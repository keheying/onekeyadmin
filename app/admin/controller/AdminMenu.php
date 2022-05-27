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
                $data[$key]['c_ifshow'] = $value['ifshow'] == 1 ? '显示' : '隐藏';
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
            MenuModel::update(input('post.'));
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            MenuModel::recursiveDestroy(input('post.ids'));
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
            if ($input['curd'] != 1) {
                $parent = MenuModel::create($input);
            } else {
                $viewPath = str_replace('mk_','', $input['path']);
                $path  = ucwords(str_replace('_', ' ', $viewPath));
                $path  = str_replace(' ','',lcfirst($path));
                $class = ucfirst($path);
                $exist = Db::query("SHOW TABLES LIKE '".$input['path']."'");
                if (empty($exist)) return json(['status' => 'error', 'message' => '数据表不存在！']);
                $field = Db::query("SHOW FULL COLUMNS FROM ".$input['path']."");
                $curd  = include(app_path() . "addons/Curd.php");
                File::create(app_path() . "view/$viewPath/index.html", $view);
                File::create(app_path() . "controller/$class.php", $controller);
                File::create(app_path() . "model/$class.php", $model);
                $curd = [
                    ['title' => '编辑', 'path' => $path . '/update'],
                    ['title' => '删除', 'path' => $path . '/delete'],
                    ['title' => '新增', 'path' => $path . '/save'],
                    ['title' => '查看', 'path' => $path . '/index'],
                ];
                Db::startTrans();
                try {
                    $parent = MenuModel::create([
                        'pid'        => $input['pid'],
                        'title'      => $input['title'],
                        'icon'       => $input['icon'],
                        'path'       => $path.'/index',
                        'sort'       => $input['sort'],
                        'ifshow'     => $input['ifshow'],
                        'logwriting' => $input['logwriting'],
                    ]);
                    foreach ($curd as $key => $val) {
                        MenuModel::create([
                            'pid'        => $parent->id,
                            'title'      => $val['title'],
                            'icon'       => '',
                            'path'       => $val['path'],
                            'sort'       => $key,
                            'ifshow'     => 0,
                            'logwriting' => $input['logwriting'],
                        ]);
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    return json(['status' => 'error', 'message' => '创建数据失败！']);
                }
            }
            return json(['status' => 'success', 'message' => '新增成功']);
        }
    }
}
