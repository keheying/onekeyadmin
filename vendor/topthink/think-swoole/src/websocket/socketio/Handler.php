<?php

namespace think\swoole\websocket\socketio;

use Exception;
use Swoole\Server;
use Swoole\Timer;
use Swoole\Websocket\Frame;
use think\Config;
use think\Event;
use think\Request;
use think\swoole\Websocket;
use think\swoole\websocket\Room;

class Handler extends Websocket
{
    /** @var Config */
    protected $config;

    protected $eio;

    protected $pingTimeoutTimer  = null;
    protected $pingIntervalTimer = null;

    protected $pingInterval;
    protected $pingTimeout;

    public function __construct(Server $server, Room $room, Event $event, Config $config)
    {
        $this->config       = $config;
        $this->pingInterval = $this->config->get('swoole.websocket.ping_interval', 25000);
        $this->pingTimeout  = $this->config->get('swoole.websocket.ping_timeout', 60000);
        parent::__construct($server, $room, $event);
    }

    /**
     * "onOpen" listener.
     *
     * @param int $fd
     * @param Request $request
     */
    public function onOpen($fd, Request $request)
    {
        $this->eio = $request->param('EIO');

        $payload = json_encode(
            [
                'sid'          => base64_encode(uniqid()),
                'upgrades'     => [],
                'pingInterval' => $this->pingInterval,
                'pingTimeout'  => $this->pingTimeout,
            ]
        );

        $this->push(EnginePacket::open($payload));

        $this->event->trigger("swoole.websocket.Open", $request);

        if ($this->eio < 4) {
            // 修改底层(不建议注释，会导致tcp连接一直存在，占用大量内存)
            $this->resetPingTimeout($this->pingInterval + $this->pingTimeout);
            $this->onConnect();
        } else {
            $this->schedulePing();
        }
    }

    /**
     * "onMessage" listener.
     *
     * @param Frame $frame
     */
    public function onMessage(Frame $frame)
    {
        // 修改底层(监听消息回调)
        $frameData = json_decode($frame->data);
        $this->event->trigger('swoole.websocket.'.ucfirst($frameData->type), $frameData->data);
    }

    /**
     * "onClose" listener.
     *
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($fd, $reactorId)
    {
        Timer::clear($this->pingTimeoutTimer);
        Timer::clear($this->pingIntervalTimer);
        $this->event->trigger("swoole.websocket.Close", $reactorId);
    }

    protected function onConnect($data = null)
    {
        try {
            $this->event->trigger('swoole.websocket.Connect', $data);
            $packet = Packet::create(Packet::CONNECT);
            if ($this->eio >= 4) {
                $packet->data = ['sid' => base64_encode(uniqid())];
            }
        } catch (Exception $exception) {
            $packet = Packet::create(Packet::CONNECT_ERROR, [
                'data' => ['message' => $exception->getMessage()],
            ]);
        }

        $this->push($packet);
    }

    protected function resetPingTimeout($timeout)
    {
        Timer::clear($this->pingTimeoutTimer);
        $this->pingTimeoutTimer = Timer::after($timeout, function () {
            $this->close();
        });
    }

    protected function schedulePing()
    {
        Timer::clear($this->pingIntervalTimer);
        $this->pingIntervalTimer = Timer::after($this->pingInterval, function () {
            $this->push(EnginePacket::ping());
            $this->resetPingTimeout($this->pingTimeout);
        });
    }

    protected function encode($packet)
    {
        return Parser::encode($packet);
    }

    protected function decode($payload)
    {
        return Parser::decode($payload);
    }

    public function push($data)
    {
        if ($data instanceof Packet) {
            $data = EnginePacket::message($this->encode($data));
        }
        if ($data instanceof EnginePacket) {
            $data = $data->toString();
        }
        return parent::push($data);
    }

    public function emit(string $event, $data = null): bool
    {
        $packet = Packet::create(Packet::EVENT, [
            'data' => [$event, $data],
        ]);
        return $this->push($packet);
    }
}
