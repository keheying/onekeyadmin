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

use think\facade\Cache;
use app\admin\model\Admin;
use app\admin\model\AdminMenu;
use app\admin\model\AdminToken;
/**
 * 权限检测
 */
class AuthCheck
{
    /**
     * 不需要检查的类
     * @var noVerification
     */
    protected $ignoreCheckClass = [
        'login'
    ];
    
    public function handle($request, \Closure $next)
    {
        if (! in_array($request->class, $this->ignoreCheckClass)) {
            // 登录检查
            if (empty($request->userInfo)) {
                return $request->isPost() ? json(['status'=>'login', 'message'=>'登录状态过期失效！']) : redirect(admin_url('login/index'));
            }
            // 菜单列表
            $request->systemMenu = $this->systemMenu($request);
            $request->pluginMenu = $this->pluginMenu($request);
            $request->menu       = array_merge(array_sort($request->systemMenu, 'sort'), array_sort($request->pluginMenu, 'sort'));
            $publicMenu = [];
            foreach ($request->menu as $key => $value) {
                if ($value['ifshow'] === 1) {
                    array_push($publicMenu, $value);
                }
            }
            $request->publicMenu = $publicMenu;
            // 权限检查
            $request->authorityList  = array_column($request->menu, 'path');
            $request->authorityIndex = array_search($request->authorityPath, $request->authorityList);
            if ($request->path !== 'index/index') {
                if ($request->authorityIndex === false) {
                    return $request->isPost() ? json(['status' => 'error', 'message' => '当前权限不足~']) : abort(403);
                }
            }
        }
        // 下一步
        return $next($request);
    }

    /**
     * 系统菜单
     */
    public function systemMenu($request)
    {
        $where = [];
        $group_role = is_array($request->userInfo->group_role) ? implode(',', $request->userInfo->group_role) : $request->userInfo->group_role;
        if ($group_role !== "*") {
            $where[] = ['id', 'in', $group_role];
        }
        $menu = Cache::get('role-'.$group_role);
        if (empty($menu)) {
            $menu = AdminMenu::where($where)->select();
            $menu = $menu ? $menu->toArray() : [];
            Cache::tag('adminRole')->set('role-'.$group_role, $menu);
        }
        foreach ($menu as $key => $value) {
            $menu[$key]['unread'] = 0;
        }
        return $menu;
    }

    /**
     * 插件菜单
     */
    public function pluginMenu($request)
    {
        $pluginMenu = [];
        foreach (plugin_list() as $index => $plugin) {
            $fileMenu = plugin_path() . $plugin['name'] . '/menu.php';
            $fileInfo = plugin_path() . $plugin['name'] . '/info.php';
            if (is_file($fileMenu)) {
                $menu = include($fileMenu);
                $menu = is_array($menu) ? $menu : [];
                $pluginMenu = $this->pluginMenuRecursion($menu, 0, $plugin['name'], $request->userInfo['group_role'],$request);
            }
        }
        return $pluginMenu;
    }

    /**
     * 插件菜单(递归)
     */
    public function pluginMenuRecursion($array, $pid, $pluginName, $role, $request)
    {
        static $pluginMenu = [];
        $count = count($array);
        foreach ($array as $key => $menu) {
            $sub['id'] = $pid === 0 ? $pluginName . '_' . $key : $pid . '_' . $key;
            if (isset($menu['bind'])) {
                $pid  = 0;
                foreach ($request->systemMenu as $key => $val) {
                    if ($val['title'] === $menu['bind']) $pid = $val['id']; 
                }
            }
            $sub['pid']          = $pid;
            $sub['title']        = $menu['title'];
            $sub['path']         = $pluginName . '/' . $menu['path'];
            $sub['icon']         = isset($menu['icon']) ? "/plugins/$pluginName/" . $menu['icon'] : '';
            $sub['sort']         = isset($menu['sort']) ? $menu['sort'] : $count - $key;
            $sub['ifshow']       = isset($menu['ifshow']) ? $menu['ifshow'] : 0;
            $sub['unread']       = isset($menu['unread']) ? $menu['unread'] : 0;
            $sub['logwriting']   = isset($menu['logwriting']) ? $menu['logwriting'] : 0;
            if ($role === "*" || in_array($sub['id'], $role)) {
                $pluginMenu[] = $sub;
            }
            if (isset($menu['children'])) {
               $this->pluginMenuRecursion($menu['children'], $sub['id'], $pluginName, $role, $request);
            }
        }
        return $pluginMenu;
    }
}