<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\addons\File;
use app\admin\BaseController;
use app\admin\model\AdminMenu as MenuModel;
/**
 * 管理员菜单管理
 */
class AdminMenu extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $data = $this->request->menu;
            $keyword = input('post.keyword');
            if (! empty($keyword)) {
                $data = [];
                foreach ($this->request->menu as $key => $value) {
                    if (strstr($value['title'],$keyword) !== false || strstr($value['path'],$keyword) !== false) {
                        array_push($data, $value);
                    }
                }
            }
            foreach ($data as $key => $value) {
                $data[$key]['c_ifshow']     = $value['ifshow'] == 1 ? '显示' : '隐藏';
                $data[$key]['c_logwriting'] = $value['logwriting'] == 1 ? '开启' : '关闭';
            }
            return json(['status' => 'success', 'message' => '请求成功', 'data' => $data, 'publicMenu' => $this->request->publicMenu]);
        } else {
            return View::fetch();
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            if (is_numeric($input['id'])) {
                MenuModel::update($input);
            } else {
                $arr    = explode('_', $input['id']);
                $plugin = $arr[0];
                $pluginPath = plugin_path() . $plugin . '/';
                // 修改菜单图片
                if ($arr[1] == 0 && is_file(public_path() . $input['icon'])) {
                    File::create($pluginPath.'menu.png', file_get_contents(public_path() . $input['icon']));
                }
                // 修改菜单信息
                $menu   = include(plugin_path() . $plugin . '/menu.php');
                switch (count($arr)) {
                    case 4:
                        $menu[$arr[1]]['children'][$arr[2]]['children'][$arr[3]]['title']      = $input['title'];
                        $menu[$arr[1]]['children'][$arr[2]]['children'][$arr[3]]['sort']       = $input['sort'];
                        $menu[$arr[1]]['children'][$arr[2]]['children'][$arr[3]]['path']       = str_replace($plugin . '/', '', $input['path']);
                        $menu[$arr[1]]['children'][$arr[2]]['children'][$arr[3]]['ifshow']     = $input['ifshow'];
                        $menu[$arr[1]]['children'][$arr[2]]['children'][$arr[3]]['logwriting'] = $input['logwriting'];
                        break;
                    case 3:
                        $menu[$arr[1]]['children'][$arr[2]]['title']      = $input['title'];
                        $menu[$arr[1]]['children'][$arr[2]]['sort']       = $input['sort'];
                        $menu[$arr[1]]['children'][$arr[2]]['path']       = str_replace($plugin . '/', '', $input['path']);
                        $menu[$arr[1]]['children'][$arr[2]]['ifshow']     = $input['ifshow'];
                        $menu[$arr[1]]['children'][$arr[2]]['logwriting'] = $input['logwriting'];
                        break;
                    case 2:
                        $menu[$arr[1]]['title']      = $input['title'];
                        $menu[$arr[1]]['sort']       = $input['sort'];
                        $menu[$arr[1]]['path']       = str_replace($plugin . '/', '', $input['path']);
                        $menu[$arr[1]]['ifshow']     = $input['ifshow'];
                        $menu[$arr[1]]['logwriting'] = $input['logwriting'];
                        break;    
                }
                File::create($pluginPath.'menu.php', "<?php\nreturn ".var_export($menu,true).";");
            }
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            foreach ($input['ids'] as $key => $id){
                if (is_numeric($id)) {
                    MenuModel::recursiveDestroy((string)$id);
                } else {
                    $arr        = explode('_', $id);
                    $plugin     = $arr[0];
                    $pluginPath = plugin_path() . $plugin . '/';
                    // 修改菜单信息
                    $menu = include(plugin_path() . $plugin . '/menu.php');
                    switch (count($arr)) {
                        case 4:
                            unset($menu[$arr[1]]['children'][$arr[2]]['children'][$arr[3]]);
                            break;
                        case 3:
                            unset($menu[$arr[1]]['children'][$arr[2]]);
                            break;
                        case 2:
                            unset($menu[$arr[1]]);
                            break;    
                    }
                    File::create($pluginPath.'menu.php', "<?php\nreturn ".var_export($menu,true).";");
                }
            }
            return json(['status' => 'success', 'message' => '删除成功']);
        }
    }
    
    /**
     * 保存新建的资源
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            if (is_numeric($input['pid'])) {
                MenuModel::create($input);
            } else {
                $arr    = explode('_', $input['pid']);
                if (count($arr) > 3) {
                    return json(['status' => 'error', 'message' => '亲，插件菜单最多三级哦']);
                }
                $plugin = $arr[0];
                $menu   = include(plugin_path() . $plugin . '/menu.php');
                $new    = [
                    'title'      => $input['title'],
                    'sort'       => $input['sort'],
                    'path'       => str_replace('/' . $plugin, '', $input['path']),
                    'ifshow'     => $input['ifshow'],
                    'logwriting' => $input['logwriting'],
                    'children'   => [],
                ];
                switch (count($arr)) {
                    case 3:
                        array_push($menu[$arr[1]]['children'][$arr[2]]['children'], $new);
                        break;
                    case 2:
                        array_push($menu[$arr[1]]['children'], $new);
                        break;    
                }
                $pluginPath = plugin_path() . $plugin . '/';
                File::create($pluginPath.'menu.php', "<?php\nreturn ".var_export($menu,true).";");
            }
            return json(['status' => 'success', 'message' => '新增成功']);   
        }
    }
}
