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

use think\facade\Db;
use think\facade\View;
use app\addons\File;
use app\admin\BaseController;
/**
 * 语言管理
 */
class Lang extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        $defaultParameter = include(root_path() . 'lang/' . config('lang.default_lang') . '.php');
        if ($this->request->isPost()) {
            $langList = config('lang.lang_list');
            foreach ($langList as $key => $val) {
                $parameter = include(root_path() . 'lang/' . $val['name'] . '.php');
                $langList[$key]['id']        = $key;
                $langList[$key]['parameter'] = [];
                $langList[$key]['c_default'] = $val['default'] === 1 ? '是' : '否';
                $langList[$key]['c_status']  = $val['status'] === 1 ? '正常' : '屏蔽';
                $langList[$key]['url']       = $val['default'] === 1 ? $this->request->domain() : $this->request->domain() . '/' . $val['name'];
                // 根据默认语言显示参数
                foreach ($defaultParameter as $k => $v) {
                    array_push($langList[$key]['parameter'], ['title' => $k, 'value' => isset($parameter[$k]) ? $parameter[$k] : ""]);
                }
            }
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $langList]);
        } else {
            $default = [];
            foreach ($defaultParameter as $k => $v) {
                array_push($default, ['title' => $k, 'value' => ""]);
            }
            View::assign('defaultParameter', $default);
            return View::fetch();
        }
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $langList = config('lang.lang_list');
            if (in_array($input['name'], array_column($langList, 'name')))  return json(['status' => 'error', 'message' => '语言缩写不能重复！']);
            // 默认语言
            if ($input['default'] === 1) {
                foreach ($langList as $key => $value) {
                    $langList[$key]['default'] = 0;
                }
            }
            // 语言参数
            $parameter = [];
            foreach ($input['parameter'] as $key => $value) {
                $parameter[$value['title']] = $value['value'];
            }
            File::create(root_path() . 'lang/' . $input['name'] . '.php', "<?php\nreturn ".var_export($parameter,true).";");
            // 修改数据
            array_push($langList, $input);
            foreach ($langList as $key => $value) {
                unset($langList[$key]['id'],$langList[$key]['parameter'],$langList[$key]['c_default'],$langList[$key]['c_status']);
            }
            File::create(config_path().'langList.php', "<?php\nreturn ".var_export($langList,true).";");
            return json(['status' => 'success', 'message' => '操作成功']);
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $langList = config('lang.lang_list');
            $input = input('post.');
            if ($input['default'] === 0 && $langList[$input['id']]['default'] === 1){
                return json(['status' => 'error', 'message' => '可选择其它语言为默认语言，不能直接关闭']);
            } 
            // 默认语言
            if ($input['default'] === 1) {
                foreach ($langList as $key => $value) {
                    $langList[$key]['default'] = 0;
                }
            }
            // 语言参数
            $parameter = [];
            foreach ($input['parameter'] as $key => $value) {
                $parameter[$value['title']] = $value['value'];
            }
            File::create(root_path() . 'lang/' . $input['name'] . '.php', "<?php\nreturn ".var_export($parameter,true).";");
            // 修改数据
            $langList[$input['id']] = $input;
            foreach ($langList as $key => $value) {
                unset($langList[$key]['id'],$langList[$key]['parameter'],$langList[$key]['c_default'],$langList[$key]['c_status']);
            }
            File::create(config_path().'langList.php', "<?php\nreturn ".var_export($langList,true).";");
            return json(['status' => 'success', 'message' => '操作成功']);
        }
    }

    /**
     * 删除
     */
    public function delete() {
        if ($this->request->isPost()) {
            $input = input('post.');
            $langList = config('lang.lang_list');
            foreach ($input['ids'] as $key => $id) {
                if ($langList[$id]['default'] !== 1) {
                    unset($langList[$id]);
                }
            }
            $langList = array_values($langList);
            // 修改数据
            File::create(config_path().'langList.php', "<?php\nreturn ".var_export($langList,true).";");
            return json(['status' => 'success', 'message' => '操作成功']);
        }
    }

    /**
     * 排序
     */
    public function drop() {
        if ($this->request->isPost()) {
            $input = input('post.');
            $langList = $input['table'];
            foreach ($langList as $key => $value) {
                unset($langList[$key]['id'],$langList[$key]['parameter'],$langList[$key]['c_default'],$langList[$key]['c_status']);
            }
            File::create(config_path().'langList.php', "<?php\nreturn ".var_export($langList,true).";");
            return json(['status' => 'success', 'message' => '操作成功']);
        }
    }
}