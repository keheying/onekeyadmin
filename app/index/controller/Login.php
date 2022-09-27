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
 * 用户登录、注册、修改密码
 */
class Login  extends BaseController
{
    /**
     * 登录
     */
    public function index()
    {
        return View::fetch("user/login");
    }
    
    /**
     * 修改密码
     */
    public function password()
    {
        return View::fetch("user/password");
    }
    
    /**
     * 注册界面
     */
    public function register() {
        return View::fetch("user/register");
    }
}