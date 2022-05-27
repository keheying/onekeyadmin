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
declare (strict_types = 1);

namespace app\admin\middleware;

use app\admin\addons\User;
/**
 * 环境检查
 */
class AppCheck
{
    public function handle($request, \Closure $next)
    {
        // 自动登录
        User::checkAutomaticLogin();
        $request->userInfo = session('admin');
        // 系统信息
        $pathinfo = str_replace('.html', '', $request->pathinfo());
        $request->path = empty($pathinfo) ? 'index/index' : $pathinfo;
        $request->class = explode('/', $request->path)[0];
        $request->authorityPath = $request->path;
        // 插件信息
        if ($request->path === 'plugins') {
            $request->pluginPath      = str_replace('.html', '', explode('?', input('path'))[0]);
            $request->pluginPathArr   = explode('/', $request->pluginPath);
            $request->pluginName      = $request->pluginPathArr[0]; // 插件名
            $request->pluginClass     = $request->pluginPathArr[1]; // 插件类
            $request->pluginAction    = $request->pluginPathArr[2]; // 插件方法
            $request->pluginRoute     = "plugins\\$request->pluginName\admin"; //插件路径
            $request->pluginNamespace = "$request->pluginRoute\controller\\".ucfirst($request->pluginClass); //命名空间
            $request->authorityPath   = $request->pluginPath;
        }
        // 钩子
        event('AppCheck', $request);
        return $next($request);
    }
}