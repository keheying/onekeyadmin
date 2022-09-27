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
 * 个人中心模块
 */
class User  extends BaseController
{
    /**
     * 账户管理
     */
    public function index()
    {
        return View::fetch("user/index");
    }
    
    /**
     * 个人资料
     */
    public function set()
    {
        return View::fetch("user/set");
    }
    
    /**
     * 用户资料(别人的)
     */
    public function info()
    {
        return View::fetch("user/info");
    }
}
