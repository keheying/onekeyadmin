<?php

namespace Swoole\WebSocket;

class CloseFrame extends Frame
{

    public $opcode = 8;

    public $code = 1000;

    public $reason = '';


}
