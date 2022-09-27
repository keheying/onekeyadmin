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
namespace app\index\controller;

use think\facade\View;
use app\index\BaseController;
/**
 * 系统页面
 */
class Page extends BaseController
{
    /**
     * 分类页面
     */
    public function index()
    {
        $name = str_replace('-', '_', $this->request->catalog['seo_url']);
        return View::fetch($name);
    }

    /**
     * 全站搜索
     */
    public function search()
    {
        return View::fetch('search');
    }
}
