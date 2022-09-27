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
namespace app\admin\controller;

use onekey\File;
use think\facade\Db;
use think\facade\View;
use app\admin\BaseController;
use app\admin\model\Curd as CurdModel;
/**
 * 一键生成代码
 */
class Curd extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input  = input('post.');
            $data   = CurdModel::withSearch(['keyword'], $input)->order('sort', 'desc')->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data, 'publicMenu' => $this->request->publicMenu]);
        } else {
            $result = api_post('tokenSystem/table');
            if ($result['status'] === 'success') {
                $table = array_column(Db::query('SHOW TABLES'),'Tables_in_'.env('database.database'));
                $data  = [];
                foreach ($table as $val){
                    if (strstr($val, 'mk_app_') !== false) {
                        if (! in_array($val,$result['data'])) {
                            array_push($data, $val);
                        }
                    }
                }
                foreach ($data as $val) {
                    $exist = CurdModel::where('name', $val)->find();
                    $props = Db::query("SHOW FULL COLUMNS FROM $val");
                    // 存在表则对比字段
                    if ($exist) {
                        $field = $exist->field;
                        foreach ($props as $key => $prop) {
                            if (! in_array($prop['Field'], array_column($field, 'prop'))) {
                                $label = empty($prop['Comment']) ? $prop['Field'] : $prop['Comment'];
                                $array = [
                                    'is'          => 'el-input',
                                    'prop'        => $prop['Field'],
                                    'label'       => $label,
                                    'colMd'       => '',
                                    'default'     => '',
                                    'placeholder' => '',
                                    'filterable'  => true,
                                    'multiple'    => true,
                                    'type'        => '',
                                    'child'       => [],
                                    'tips'        => '',
                                    'required'    => false,
                                    'pattern'     => '',
                                    'disabled'    => false,
                                    'formShow'    => true,
                                    'tableWidth'  => 0,
                                    'tableBind'   => [],
                                    'tableSort'   => true,
                                    'tableProp'   => $prop['Field'],
                                    'tableLabel'  => $label,
                                    'tableShow'   => true,
                                ];
                                array_push($field, $array);
                            }
                        }
                        $exist->field = $field;
                        $exist->save();
                    } else {
                        $field = [];
                        foreach ($props as $key => $prop) {
                            $field[$key]['is']          = 'el-input';
                            $field[$key]['key']         = $prop['Key'];
                            $field[$key]['prop']        = $prop['Field'];
                            if ($prop['Key'] === 'PRI') {
                                 $field[$key]['label']  = '主键';
                            } else {
                                $field[$key]['label']   = empty($prop['Comment']) ? $prop['Field'] : $prop['Comment'];
                            }
                            $field[$key]['colMd']       = '';
                            $field[$key]['default']     = '';
                            $field[$key]['placeholder'] = '';
                            $field[$key]['filterable']  = true;
                            $field[$key]['multiple']    = true;
                            $field[$key]['type']        = '';
                            $field[$key]['child']       = [];
                            $field[$key]['tips']        = '';
                            $field[$key]['required']    = false;
                            $field[$key]['pattern']     = '';
                            $field[$key]['disabled']    = false;
                            $field[$key]['formShow']    = $prop['Key'] === 'PRI' ? false : true;
                            $field[$key]['tableWidth']  = 0;
                            $field[$key]['tableBind']   = [];
                            $field[$key]['tableSort']   = true;
                            $field[$key]['tableProp']   = $field[$key]['prop'];
                            $field[$key]['tableLabel']  = $field[$key]['label'];
                            $field[$key]['tableShow']   = true;
                        }
                        CurdModel::create([
                            'title'                 => '未命名',
                            'name'                  => $val,
                            'field'                 => $field,
                            'sort'                  => 0,
                            'plugin'                => '',
                            'number'                => 0,
                            'form_label_width'      => 100,
                            'form_col_md'           => 24,
                            'table_tree'            => 0,
                            'table_expand'          => 0,
                            'table_export'          => 1,
                            'table_sort'            => '',
                            'table_page_size'       => 20,
                            'table_operation_width' => 0,
                            'search_catalog'        => [],
                            'search_status'         => [],
                            'search_keyword'        => 1,
                            'search_date'           => 1,
                            'preview'               => 0,
                            'create_time'           => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            return View::fetch();
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            CurdModel::update(input('post.'));
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }
    
    /**
     * 生成代码
     */
    public function code()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $pluginPath = plugin_path() . $input['name'] . '/';
            // 生成插件文件夹
            File::dirMkdir($pluginPath);
            // 生成插件基础信息
            $pluginInfo = [
                'title'  => $input['title'],
                'name'   => $input['name'],
                'status' => 1,
                'sort'   => 0,
            ];
            File::create($pluginPath.'info.php', "<?php\nreturn ".var_export($pluginInfo,true).";");
            // 生成插件菜单图片
            if (! empty($input['cover'])) {
                File::create($pluginPath.'menu.png', file_get_contents(public_path() . $input['cover']));
            }
            // 生成后台控制器、模型、视图、菜单权限、API接口、前端路由
            $menuChildren = [];
            $pluginRoute  = [];
            foreach ($input['table'] as $key => $value) {
                $name     = $value['name'];
                $table    = str_replace('mk_','', $name);
                $viewPath = str_replace('mk_app_','', $name);
                $path     = ucwords(str_replace('_', ' ', $viewPath));
                $path     = str_replace(' ','',lcfirst($path));
                $class    = ucfirst($path);
                $field    = $value['field'];
                include(app_path() . "addons/Curd.php");
                // 后台
                File::create($pluginPath . "admin/view/$viewPath/index.html", $adminView); // 可重复创建
                if (! is_file($pluginPath . "admin/controller/$class.php")) File::create($pluginPath . "admin/controller/$class.php", $adminController);
                if (! is_file($pluginPath . "admin/model/$class.php")) File::create($pluginPath . "admin/model/$class.php", $admminModel);
                // API
                if (! is_file($pluginPath . "api/controller/$class.php")) File::create($pluginPath . "api/controller/$class.php", $indexController);
                if (! is_file($pluginPath . "api/model/$class.php")) File::create($pluginPath . "api/model/$class.php", $indexModel);
                // 菜单
                array_push($menuChildren,
                [
    				'title'    => $value['title'] . '管理',
    				'path'     => $path . '/index',
    				'ifshow'   => 1,
    				'children' => [
    					[
    						'title'      => '查看',
    						'path'       => $path . '/index',
    					],
    					[
    						'title'      => '发布',
    						'path'       => $path . '/save',
    						'logwriting' => 1,
    					],
    					[
    						'title'      => '编辑',
    						'path'       => $path . '/update',
    						'logwriting' => 1,
    					],
    					[
    						'title'      => '删除',
    						'path'       => $path . '/delete',
    						'logwriting' => 1,
    					],
    				]
                ]);
                // 路由
                array_push($pluginRoute, 
                [
                    "catalog" => $viewPath,
                    "title"   => $value['title'],
                    "table"   => $table,
                ]);
                CurdModel::where('name', $value['name'])->update([
                    'number' => $value['number'] + 1,
                    'plugin' => $input['name'],
                ]);
            }
            // 生成插件路由
            File::create($pluginPath.'route.php', "<?php\nreturn ".var_export($pluginRoute,true).";");
            // 生成插件菜单
            $pluginMenu[] = 
            [
        		'title'    => $input['title'],
        		'path'     => $input['name'],
                'icon'     => 'menu.png',
        		'ifshow'   => 1,
        		'children' => $menuChildren
            ];
            File::create($pluginPath.'menu.php', "<?php\nreturn ".var_export($pluginMenu,true).";");
            return json(['status' => 'success','message' => '一键生成插件成功']);
        }
    }
    
    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            CurdModel::destroy(input('post.ids'));
            return json(['status' => 'success', 'message' => '删除成功']);
        }
    }
}
