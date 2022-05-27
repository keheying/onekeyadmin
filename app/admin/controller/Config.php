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
use app\admin\BaseController;
use app\admin\model\Themes;
use app\admin\model\Config as ConfigModel;
/**
 * 配置管理
 */
class Config extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        // 基础配置
        $system = ConfigModel::getVal('system_' . $this->request->lang);
        $system = $system ? $system : [
            'company'         => '', 
            'email'           => '', 
            'telephone'       => '', 
            'phone'           => '', 
            'fax'             => '',
            'wechat'          => '',
            'qq'              => '',
            'address'         => '',
            'business_hours'  => '',
            'ico'             => '',
            'logo'            => '',
            'copy_logo'       => '',
            'wechat_qrcode'   => '',
            'copyright'       => '',
            'seo_title'       => '',
            'seo_keywords'    => '',
            'seo_description' => '',
            'icp'             => ''
        ];
        // 会员配置-钩子
        $object = (object)[];
        $object->userConfig = ConfigModel::getVal('user');
        if (! $object->userConfig) {
            $object->userConfig = [];
        }
        event('UserConfig', $object);
        // 主题配置
        $theme = Themes::where('name', theme())->field('name,title,config')->find()->toArray();
        $value = ConfigModel::getVal('theme_' . $theme['name'] . '_' . request()->lang);
        foreach ($theme['config'] as $key => $val) {
            $theme['config'][$key]['type']['value'] = isset($value[$val['field']]) ? $value[$val['field']] : $val['type']['value'];
        }
        View::assign([
            'email'  => ConfigModel::getVal('email'),
            'system' => $system,
            'user'   => $object->userConfig,
            'theme'  => $theme,
            'upload' => [
                'admin' => include(root_path().'app/admin/config/upload.php'),
                'index' => include(root_path().'app/index/config/upload.php')
            ],
        ]);
        return View::fetch();
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            switch ($input['name']) {
                case 'email':
                    $msg = ConfigModel::setVal($input['name'],$input['title'], $input['value']);
                    break;
                case 'system':
                    $msg = ConfigModel::setVal($input['name'] . '_' . $this->request->lang,$input['title'], $input['value']);
                    break;
                case 'user':
                    $msg = ConfigModel::setVal($input['name'], $input['title'], $input['value']);
                    break;
                case 'upload':
                    file_put_contents(root_path().'app/index/config/upload.php', "<?php\nreturn ".var_export($input['value']['index'],true).";");
                    file_put_contents(root_path().'app/admin/config/upload.php', "<?php\nreturn ".var_export($input['value']['admin'],true).";");
                    $msg = ['status' => 'success', 'message' => '修改成功'];
                    break;
                default:
                    $value = [];
                    foreach ($input['value'] as $key => $val) {
                        $value[$val['field']] = $val['type']['value'];
                    }
                    $name = 'theme_' . $input['name'] . '_' . $this->request->lang;
                    $msg  = ConfigModel::setVal($name, $input['title'], $value);
                    if ($msg['status'] === 'success') {
                        Themes::update(['config' => $input['value']], ['name' => $input['name']]);
                        cache('themes_' . $input['name'], null);
                    }
                    break;
            }
            return json($msg);
        }
    }

    /**
     * 链接配置
     */
    public function link()
    {
        $input = input('post.');
        $data  = [];
        if (! isset($input['table'])){
            foreach (plugin_list() as $key => $value) {
                $data = array_merge($data, $value['route']);
            }
        } else {
            $exist = Db::query("SHOW TABLES LIKE 'mk_".$input['table']."'");
            if (! empty($exist)) {
                // 多语言
                $field = Db::query("SHOW COLUMNS FROM mk_".$input['table']."");
                if (in_array('language', array_column($field, 'Field'))) {
                    $where[] = ['language', '=', $this->request->lang];
                }
                // 模糊查找
                if (! empty($input['keyword'])) {
                    $where[] = [implode('|', array_column($field, 'Field')), 'like', '%'.$input['keyword'].'%'];
                }
                // 状态正常
                if (in_array('status', array_column($field, 'Field'))) {
                    $where[] = ['status', '=', 1];
                }
                $data = Db::name($input['table'])->where($where)->field('id,title,seo_url,catalog_id')->limit(10)->select();
            }
        }
        return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
    }
}
