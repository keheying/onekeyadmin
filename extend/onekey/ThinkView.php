<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace onekey;

use think\Manager;
use think\helper\Arr;

/**
 * 视图类
 * @package think
 */
class ThinkView extends Manager
{

    protected $namespace = '\\think\\view\\driver\\';

    /**
     * 模板变量
     * @var array
     */
    protected $data = [];

    /**
     * 内容过滤
     * @var mixed
     */
    protected $filter;

    /**
     * 获取模板引擎
     * @access public
     * @param string $type 模板引擎类型
     * @return $this
     */
    public function engine(string $type = null)
    {
        return $this->driver($type);
    }

    /**
     * 模板变量赋值
     * @access public
     * @param string|array $name  模板变量
     * @param mixed        $value 变量值
     * @return $this
     */
    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }
        return $this;
    }

    /**
     * 视图过滤
     * @access public
     * @param Callable $filter 过滤方法或闭包
     * @return $this
     */
    public function filter(callable $filter = null)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * 解析和获取模板内容 用于输出(修改底层)
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array  $vars     模板变量
     * @return string
     * @throws \Exception
     */
    public function fetch(string $template = '', array $vars = []): string
    {
        // 前端fetch
        if (App('http')->getName() === 'index') {
            $viewName = strtolower(preg_replace('/(?<=[a-z])([A-Z])/','_$1', $template));
            if ($template != '404' && $template != '403') {
                $action = input('action');
                if ($action === 'list' || $action === 'single') {
                    $viewName = input('action') . '/' . input('class');
                    if (request()->catalog['type'] !== 'page' && request()->catalog['bind_html'] !== '') {
                        $viewName = $action . '/' . request()->catalog['bind_html'];
                    }
                }
            }
            $template = theme_now_view() . $viewName .'.html';
            if (! is_file($template) && ! empty(input('plugin'))) {
                $pluginName    = input("plugin"); // 插件名
                $pluginRoute   = "plugins\\$pluginName\index"; //插件路径
                $template      = str_replace('\\', '/', public_path() . "$pluginRoute\\view\\$viewName.html");
            }
        }
        // 后端fetch
        if (App('http')->getName() === 'admin') {
            if (request()->path === 'plugins') {
                if (strstr($template,'403.html') === false && strstr($template,'404.html') === false) {
                    $pluginClass  = request()->pluginClass;
                    $pluginRoute  = request()->pluginRoute;
                    $pluginAction = strtolower(preg_replace('/(?<=[a-z])([A-Z])/','_$1', request()->pluginAction));
                    $pluginView   = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $pluginClass));
                    $template     = str_replace('\\', '/', public_path() . "$pluginRoute\\view\\$pluginView\\$pluginAction.html");
                }
            }
        }
        return $this->getContent(function () use ($vars, $template) {
            $this->engine()->fetch($template, array_merge($this->data, $vars));
        });
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array  $vars    模板变量
     * @return string
     */
    public function display(string $content, array $vars = []): string
    {
        return $this->getContent(function () use ($vars, $content) {
            $this->engine()->display($content, array_merge($this->data, $vars));
        });
    }

    /**
     * 获取模板引擎渲染内容
     * @param $callback
     * @return string
     * @throws \Exception
     */
    protected function getContent($callback): string
    {
        // 页面缓存
        ob_start();
        if (PHP_VERSION > 8.0) {
            ob_implicit_flush(false);
        } else {
            ob_implicit_flush(0);
        }

        // 渲染输出
        try {
            $callback();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        // 获取并清空缓存
        $content = ob_get_clean();

        if ($this->filter) {
            $content = call_user_func_array($this->filter, [$content]);
        }

        return $content;
    }

    /**
     * 模板变量赋值
     * @access public
     * @param string $name  变量名
     * @param mixed  $value 变量值
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 取得模板显示变量的值
     * @access protected
     * @param string $name 模板变量
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * 检测模板变量是否设置
     * @access public
     * @param string $name 模板变量名
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    protected function resolveConfig(string $name)
    {
        $config = $this->app->config->get('view', []);
        Arr::forget($config, 'type');
        return $config;
    }

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->app->config->get('view.type', 'php');
    }

}
