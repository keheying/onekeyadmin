<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights resulterved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\facade\View;
use app\addons\File;
use app\admin\BaseController;
use app\admin\model\Catalog;
use app\admin\model\Themes as ThemesModel;
/**
 * 主题管理
 */
class Themes extends BaseController
{
    /**
     * 主题列表
     */
    public function index()
    {
        $list = ThemesModel::select()->toArray();
        if ($this->request->isPost()) {
            $input = input('post.');
            if ($input['install'] == 1) {
                $input['name'] = array_column($list, 'name');
            }
            $result = api_post('themes/catalog', $input);
            if ($result['status'] === 'success') {
                $count = $result['count'];
                $data  = $result['data'];
                foreach ($data as $key => $value) {
                    $themeInfo = ThemesModel::where('name', $value['name'])->find();
                    $data[$key]['installLoading']   = false;
                    $data[$key]['orderLoading']     = false;
                    $data[$key]['uninstallLoading'] = false;
                    $data[$key]['install']          = empty($themeInfo) && ! $value['need_pay'];
                    $data[$key]['shop']             = empty($themeInfo) && $value['need_pay'];
                    $data[$key]['uninstall']        = !empty($themeInfo);
                    $data[$key]['status']           = $themeInfo ? $themeInfo['status'] : '';
                    $data[$key]['use']              = theme() == $value['name'];
                }
                return json(['status' => 'success', 'message' => '获取成功', 'data' => $data, 'count' => $count, 'list' => $list]);
            } else {
                return json($result);
            }
        } else {
            // 分类
            $result = api_post('token/themesClass');
            View::assign([
                'install' => $list,
                'catalog' => $result['status'] === 'success' ? $result['data'] : [],
            ]);
            return View::fetch();
        }
    }

    /**
     * 主题卸载
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            if ($input['name'] !== theme()) {
                File::delDirAndFile(theme_path() . $input['name']);
                ThemesModel::where('name', $input['name'])->delete();
                Catalog::where('theme', $input['name'])->delete();
                return json(['status' => 'success', 'message' => '主题卸载成功']);
            } else {
                return json(['status' => 'error', 'message' => '主题使用中，无法卸载！']);
            }
        }
    }

    /**
     * 主题切换
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $pat[] = 'theme';
            $rep[] = input('post.theme');
            File::editConfig($pat, $rep, config_path().'app.php');
            return json(['status' => 'success', 'message' => '主题切换成功']);
        }
    }

    /**
     * 主题订单创建
     */
    public function createOrder()
    {
        if ($this->request->isPost()) {
            $result = api_post('token/themesCreateOrder', input('post.'));
            return json($result);
        }
    }

    /**
     * 主题订单状态
     */
    public function statusOrder()
    {
        if ($this->request->isPost()) {
            $result = api_post('token/themesOrderSingle', input('post.'));
            return json($result);
        }
    }

    /**
     * 插件订单支付方式
     */
    public function payMethod()
    {
        if ($this->request->isPost()) {
            $result = api_post('token/themesPayMethod', input('post.'));
            return json($result);
        }
    }

    /**
     * 安装主题
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $input  = input('post.');
            $result = api_post('token/themesInstall', $input);
            if ($result['status'] === 'success') {
                $zip     = base64_decode($result['data']['zip']);
                $path    = theme_path() . $input['name'] . '/';
                $zipPath = $path . 'install.zip';
                // 创建文件
                File::create($zipPath, $zip);
                // 文件解压
                File::extract($zipPath, $path);
                if (is_dir($path . 'upload')) {
                    // 资源解压
                    File::dirCopy($path . 'upload', public_path() . "upload");
                    // 删除资源
                    File::delDirAndFile($path . 'upload');
                }
                // 创建主题
                ThemesModel::create($result['data']['info']);
                // 创建分类
                $model = new Catalog;
                $model->saveAll($result['data']['catalog']);
                return json(['status' => 'success', 'message' => '安装成功']);
            } else {
                return json($result);
            }
        }
    }
}
