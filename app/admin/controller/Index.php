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

use PDO;
use think\facade\Db;
use think\facade\View;
use think\facade\Cache;
use app\addons\File;
use app\admin\BaseController;
/**
 * 外壳
 */
class Index extends BaseController
{
    /**
     * 壳
     */
    public function index()
    {
        $result = api_post('token/systemNotification');
        View::assign([
            'version'      => implode('.', str_split(str_replace('.', '', config("app.version")))),
            'menu'         => $this->request->publicMenu,
            'notification' => $result['status'] === 'success' ? $result['data'] : [],
        ]);
        return View::fetch();
    }

    /**
     * 清除缓存
     */
    public function cacheClear()
    {
        Cache::clear();
        return json(['status' => 'success', 'message' => '清除成功']);
    }

    /**
     * 检查更新
     */
    public function checkUpdate()
    {
        if ($this->request->isPost()) {
            $data['version'] = config('app.version');
            return json(api_post('token/systemCheck', $data));
        }
    }

    /**
     * 更新系统
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $version = input('post.version');
            $result  = api_post('token/systemUpdate', ['version' => $version]);
            if ($result['status'] === 'success') {
                $zip   = $result['data']['zip'];
                $isnew = $result['data']['isnew'];
                $path  = root_path().'update.zip';
                // 创建文件
                File::create($path, base64_decode($zip));
                // 执行解压
                File::extract($path, root_path());
                // 修改版本
                $pat[] = 'version';
                $rep[] = $version;
                File::editConfig($pat, $rep, config_path() . 'app.php');
                // 初始化文件
                $initFile = root_path() . 'update.php';
                if (is_file($initFile)) {
                    $bool = include($initFile);
                    if ($bool) unlink($initFile);
                }
                return json(['status' => 'success', 'message' => '更新成功', 'isnew' => $isnew]);
            } else {
                return json($result);
            }
        }
    }
}
