<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\api\BaseController;
/**
* 插件跳转
*/
class Plugins extends BaseController
{
    /**
    * 插件控制器中间件
    */
    protected $middleware = [];
    
    protected function initialize()
    {
        parent::initialize();
        $plugin = input("plugin");
        $action = input('action');
        $class  = ucwords(str_replace('_', ' ', input('class')));
        $class  = str_replace(' ','',lcfirst($class));
        $class  = ucfirst($class);
        // 当前访问类
        $this->namespace = "plugins\\$plugin\api\controller\\".$class;
        // 替换中间件
        $this->middleware = property_exists(app($this->namespace),'middleware') ? app($this->namespace)->middleware : [];
    }
    
    /**
    * 插件跳转
    */
	public function index()
    {
        $action = input('action');
        return method_exists($this->namespace, $action) ? app($this->namespace)->$action() : json(['status' => 'error', 'message' => '404 not definde']);
    }
}