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
namespace app\index\controller;

use think\facade\View;
use app\index\BaseController;
/**
* 插件跳转
*/
class Plugins extends BaseController
{
	public function index()
    {
        $plugin = input("plugin");
        $action = input('action');
        $class  = ucwords(str_replace('_', ' ', input('class')));
        $class  = str_replace(' ','',lcfirst($class));
        $class = ucfirst($class);
        $namespace = "plugins\\$plugin\index\controller\\".$class;
        return method_exists($namespace, $action) ? app($namespace)->$action() : abort(404);
    }
}