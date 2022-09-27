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
namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'id'            => 'require',
        'email'         => 'require|email',
        'mobile'        => 'require',
        'account'       => 'require',
        'captcha'       => 'require|captcha',
        'code'          => 'require',
        'nickname'      => 'require|max:12',
        'describe'      => 'max:255',
        'password'      => ['require', 'min' => 6, 'max' => 40],
    ];
    protected $message = [
        'id.require'             => '参数错误',
        'email.require'          => '请填写邮箱号码',
        'email.email'            => '邮箱号码格式不正确',
        'mobile.require'         => '请填写手机号',
        'account.require'        => '请填写账号',
        'captcha.require'        => '请填写验证码',
        'captcha.captcha'        => '验证码不正确',
        'nickname.require'       => '昵称不能为空',
        'nickname.max'           => '昵称不能超过40个',
        'describe.max'           => '签名不能超过255个',
        'password.require'       => '请填写密码',
        'password.min'           => '密码不能小于6个',
        'password.max'           => '密码不能大于6个',
    ];
    protected $scene = [
        'login'          => ['account','password'],
        'passwordEmail'  => ['email','password','code'],
        'passwordMobile' => ['mobile','password','code'],
        'registerEmail'  => ['email','password','code'],
        'registerMobile' => ['mobile','password','code'],
        'bindEmail'      => ['email','code'],
        'bindMobile'     => ['mobile','code'],
        'codeEmail'      => ['email'],
        'codeMobile'     => ['mobile'],
        'info'           => ['id'],
        'set'            => ['nickname','describe'],
    ];
}