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

use PDO;
use think\facade\View;
use app\addons\File;
use app\admin\BaseController;
use app\admin\model\Catalog;
/**
 * 插件管理
 */
class Plugins extends BaseController
{
    /**
     * 插件列表
     */
    public function list()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            if ($input['install'] == 2) {
                // 自主研发
                $data = [];
                $pluginPath = plugin_path();
                if (is_dir($pluginPath)) {
                    $handle = opendir($pluginPath);
                    if ($handle) {
                        while (($path = readdir($handle)) !== false) {
                            if ($path != '.' && $path != '..') {
                                $nowPluginPath = $pluginPath . $path;
                                $nowPluginInfo = is_file($nowPluginPath . '/info.php') ? include($nowPluginPath . '/info.php') : [];
                                if ($nowPluginInfo) {
                                    if (empty($nowPluginInfo['version'])) {
                                        $nowPluginInfo['route'] = is_file($nowPluginPath.'/route.php') ? include($nowPluginPath.'/route.php') : [];
                                        array_push($data, $nowPluginInfo);
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ($data as $key => $value) {
                    $data[$key]['user']          = ['nickname' => '本站作者'];
                    $data[$key]['describe']      = '如果您想和TA人分享此插件可上传到官网：<a href="'.config('app.api').'/api/user/pluginsList.html" target="_blank">点击此处上传</a>';
                    $data[$key]['price']         = 0;
                    $data[$key]['install_count'] = 0;
                }
                $count = count($data);
            } else {
                $list = plugin_list();
                // 插件中心/已安装
                if ($input['install'] == 1) {
                    if (empty($list)) {
                        return json(['status' => 'success','message' => '获取成功','data' => [], 'count' => 0, 'publicMenu' => $this->request->publicMenu]);
                    }
                    $input['name'] = array_column($list, 'name');
                }
                $result = api_post('plugins/catalog', $input);
                if ($result['status'] !== 'success') {
                    return json($result);
                }
                $count = $result['count'];
                $data  = $result['data'];
                foreach ($data as $key => $value) {
                    $pluginPath = plugin_path() . $value['name'] .'/info.php';
                    $pluginInfo = is_file($pluginPath) ? include($pluginPath) : [];
                    $pluginInfoVersion = empty($pluginInfo) || empty($pluginInfo['version']) ? '' : $pluginInfo['version'];
                    $data[$key]['installLoading']   = false;
                    $data[$key]['updateLoading']    = false;
                    $data[$key]['orderLoading']     = false;
                    $data[$key]['uninstallLoading'] = false;
                    $data[$key]['install']          = empty($pluginInfoVersion) && !$value['need_pay'];
                    $data[$key]['shop']             = empty($pluginInfoVersion) && $value['need_pay'];
                    $data[$key]['update']           = ! empty($pluginInfoVersion) && $pluginInfoVersion < $value['version'];
                    $data[$key]['uninstall']        = ! empty($pluginInfoVersion);
                    $data[$key]['new_version']      = implode('.', str_split(str_replace('.', '', $value['version'])));
                    $data[$key]['now_version']      = implode('.', str_split(str_replace('.', '', $pluginInfoVersion)));
                    $data[$key]['status']           = $pluginInfoVersion ? $pluginInfo['status'] : '';
                }
            }
            return json([
                'status'     => 'success', 
                'message'    => '获取成功', 
                'data'       => $data, 
                'count'      => $count, 
                'publicMenu' => $this->request->publicMenu
            ]);
            
        } else {
            // 分类
            $result = api_post('token/pluginsClass');
            View::assign('catalog', $result['status'] === 'success' ? $result['data'] : []);
            return View::fetch();
        }
    }

    /**
     * 插件卸载
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            File::delDirAndFile(plugin_path() . input('post.name'));
            return json(['status' => 'success', 'message' => '插件卸载成功']);
        }
    }

    /**
     * 插件开启关闭
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $file  = plugin_path() . $input['name'] . '/info.php';
            $info  = include($file);
            $info['status'] = $input['status'];
            File::create($file, "<?php\nreturn ".var_export($info,true).";");
            return json(['status' => 'success', 'message' => $input['status'] === 1 ? '插件开启成功' : '插件关闭成功']);
        }
    }

    /**
     * 插件订单创建
     */
    public function createOrder()
    {
        if ($this->request->isPost()) {
            $result = api_post('token/pluginsCreateOrder', input('post.'));
            return json($result);
        }
    }

    /**
     * 插件订单状态
     */
    public function statusOrder()
    {
        if ($this->request->isPost()) {
            $result = api_post('token/pluginsOrderSingle', input('post.'));
            return json($result);
        }
    }

    /**
     * 插件订单支付方式
     */
    public function payMethod()
    {
        if ($this->request->isPost()) {
            $result = api_post('token/pluginsPayMethod', input('post.'));
            return json($result);
        }
    }

    /**
     * 插件安装
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $input  = input('post.');
            $result = api_post('token/pluginsInstall', $input);
            if ($result['status'] === 'success') {
                $zip = base64_decode($result['data']['zip']);
                $path = plugin_path() . $input['name'] . '/';
                $zipPath = $path . 'install.zip';
                // 创建文件
                File::create($zipPath, $zip);
                // 执行解压
                File::extract($zipPath, $path);
                // 执行数据
                if (is_file($path . 'install.sql')) {
                    $sql  = file_get_contents(($path . 'install.sql'));
                    $info = "mysql:dbname=".env('database.database').";host=".env('database.hostname')."";
                    $db   = new PDO($info, env('database.username'), env('database.password'));
                    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
                    $db->exec($sql);
                }
                // 前端视图移动
                $oldView = $path.'index/view/';
                $oldFile = File::getDir($oldView);
                $newView = theme_now_path();
                foreach ($oldFile as $key => $value) {
                    if (is_file($value)) {
                        $newFile = $newView . str_replace($oldView, '', $value);
                        if (! is_file($newFile)) {
                            File::create($newFile, file_get_contents($value));
                        }
                    }
                }
                // 初始化信息
                File::create($path.'info.php', "<?php\nreturn ".var_export($result['data']['info'],true).";");
                // 分类信息
                $route = plugin_path() . $input['name'] . '/' . 'route.php';
                if (is_file($route)) {
                    $catalog = include($route);
                    foreach ($catalog as $key => $val) {
                        $exits = Catalog::where('type', $val['catalog'])->where('theme', theme())->value('id');
                        if (! $exits) {
                            Catalog::create([
                                'pid'             => 0,
                                'num'             => 0,
                                'level'           => 1,
                                'group_id'        => [],
                                'title'           => $val['title'],
                                'cover'           => '',
                                'content'         => '',
                                'description'     => '',
                                'field'           => [],
                                'bind_html'       => '',
                                'seo_url'         => $val['catalog'],
                                'seo_title'       => '',
                                'seo_keywords'    => '',
                                'seo_description' => '',
                                'links_type'      => 0,
                                'links_value'     => [],
                                'sort'            => 0,
                                'type'            => $val['catalog'],
                                'blank'           => 0,
                                'show'            => 2,
                                'status'          => 1,
                                'language'        => config('lang.default_lang'),
                                'mobile'          => 1,
                                'theme'           => theme()
                            ]);
                        }
                    }
                    // 清除缓存
                    cache('catalog_' . theme() . config('lang.default_lang'), NULL);
                }
                return json(['status' => 'success', 'message' => '插件安装成功']);
            } else {
                return json($result);
            }
        }
    }

    /**
     * 插件更新安装
     */
    public function updateInstall()
    {
        if ($this->request->isPost()) {
            $input  = input('post.');
            $result = api_post('token/pluginsInstall', $input);
            if ($result['status'] === 'success') {
                $zip = base64_decode($result['data']['zip']);
                $path = plugin_path() . $input['name'] . '/';
                $zipPath = $path . 'update.zip';
                // 创建文件
                File::create($zipPath, $zip);
                // 执行解压
                File::extract($zipPath, $path);
                // 前端视图移动
                $oldView = $path.'index/view/';
                $oldFile = File::getDir($oldView);
                $newView = theme_now_path();
                foreach ($oldFile as $key => $value) {
                    if (is_file($value)) {
                        $newFile = $newView . str_replace($oldView, '', $value);
                        if (! is_file($newFile)) {
                            File::create($newFile, file_get_contents($value));
                        }
                    }
                }
                // 初始化文件
                $initFile = $path . 'update.php';
                if (is_file($initFile)) {
                    include($initFile);
                    unlink($initFile);
                }
                // 修改插件信息
                File::create($path.'info.php', "<?php\nreturn ".var_export($result['data']['info'],true).";");
                return json(['status' => 'success', 'message' => '插件安装成功']);
            } else {
                return json($result);
            }
        }
    }

    /**
     * 插件跳转
     */
    public function index()
    {
        $action = $this->request->pluginAction;
        $namespace = $this->request->pluginNamespace;
        return method_exists($namespace, $action) ? app($namespace)->$action() : abort(404);
    }
}
