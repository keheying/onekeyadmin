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
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule =   [
        'loginAccount'  => 'require',
        'loginPassword' => 'require',
        'captcha'       => 'require|captcha',
        'code'          => 'require',
        'group_id'      => 'require',
        'nickname'      => 'require|min:2|max:40',
        'email'         => 'require|email',
        'account'       => ['require','min' => 5,'max' => 40],
        'password'      => ['min' => 6,'max' => 40],
    ];
    protected $message  =   [
        'loginAccount.require'   => '登录账号不能为空',
        'loginPassword.require'  => '登录密码不能为空',
        'captcha.require'        => '请输入验证码',
        'captcha.captcha'        => '验证码错误',
        'group_id.require'       => '所属组别不能为空',
        'nickname.require'       => '管理员昵称不能为空',
        'nickname.min'           => '管理员昵称不能少于2个字符',
        'nickname.max'           => '管理员昵称不能超过40个字符',
        'email'                  => '管理员邮箱格式不正确',
        'account.require'        => '管理员账号不能为空',
        'account.min'            => '管理员账号不能少于5个字符',
        'account.max'            => '管理员账号不能超过40个字符',
        'password.min'           => '管理员密码不能少于6个字符',
        'password.max'           => '管理员密码不能超过40个字符',
    ];
    protected $scene = [
        'save'          => ['group_id','nickname','email','account','password'],
        'login'         => ['loginAccount','loginPassword'],
        'captchalogin'  => ['loginAccount','loginPassword','captcha'],
        'password'      => ['email','password','code'],
        'register'      => ['email','account','password','code'],
        'code'          => ['email','captcha'],
    ];
}