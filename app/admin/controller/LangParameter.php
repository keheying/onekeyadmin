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

use think\facade\View;
use app\addons\File;
use app\admin\BaseController;
/**
 * 语言参数管理
 */
class LangParameter extends BaseController
{
    /**
     * 显示资源列表
     */
    
    public function index()
    {
        if ($this->request->isPost()) {
            $list = [];
            $langList  = config('lang.lang_list');
            foreach ($langList as $key => $val) {
                $langList[$key]['parameter'] = include(root_path() . 'lang/' . $val['name'] . '.php');
            }
            $key = 0;
            $parameter = include(root_path() . 'lang/' . config('lang.default_lang') . '.php');
            foreach ($parameter as  $key1 => $val1) {
                $list[$key] = [];
                $list[$key]['name'] = $key1;
                $list[$key]['parameter'] = [];
                foreach ($langList as $key2 => $val2) {
                    $list[$key]['parameter'][] = [
                        'lang'       => $val2['name'], 
                        'lang_title' => $val2['title'], 
                        'name'       => $key1, 
                        'value'      => $val2['parameter'][$key1]
                    ];
                }
                $key++;
            }
            return json(['status' => 'success', 'message' => '获取成功', 'langList' => $langList, 'list' => $list]);
        }
    }
    
    /**
     * 保存新建的资源
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $langList  = config('lang.lang_list');
            foreach ($langList as $key => $val) {
                $parameter = include(root_path() . 'lang/' . $val['name'] . '.php');
                if (! isset($parameter[$input['name']])) {
                    $parameter[$input['name']] = '';
                }
                ksort($parameter, 0);
                File::create(root_path() . 'lang/' . $val['name'] . '.php', "<?php\nreturn ".var_export($parameter,true).";");
            }
            return json(['status' => 'success', 'message' => '写入成功']);
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $parameter = include(root_path() . 'lang/' . $input['lang'] . '.php');
            $parameter[$input['name']] = $input['value'];
            ksort($parameter, 0);
            File::create(root_path() . 'lang/' . $input['lang'] . '.php', "<?php\nreturn ".var_export($parameter,true).";");
            return json(['status' => 'success', 'message' => '写入成功']);
        }
    }

    /**
     * 删除
     */
    public function delete() {
        if ($this->request->isPost()) {
            $input = input('post.');
            $langList  = config('lang.lang_list');
            foreach ($langList as $key => $val) {
                $parameter = include(root_path() . 'lang/' . $val['name'] . '.php');
                unset($parameter[$input['name']]);
                ksort($parameter, 0);
                File::create(root_path() . 'lang/' . $val['name'] . '.php', "<?php\nreturn ".var_export($parameter,true).";");
            }
            return json(['status' => 'success', 'message' => '写入成功']);
        }
    }
}