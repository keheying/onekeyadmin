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
namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'loginAccount'  => 'require',
        'loginPassword' => 'require',
        'captcha'       => 'require|captcha',
        'code'          => 'require',
        'nickname'      => 'require|max:12',
        'describe'      => 'max:255',
        'account'       => 'require',
        'password'      => ['require', 'min' => 6, 'max' => 40],
    ];
    protected $message = [
        'loginAccount.require'   => 'login account cannot be empty',
        'loginPassword.require'  => 'login password cannot be empty',
        'account.require'        => 'account number cannot be empty',
        'captcha.require'        => 'please enter the graphic verification code',
        'captcha.captcha'        => 'the graphic verification code entered is incorrect',
        'nickname.require'       => 'nickname cannot be empty',
        'nickname.max'           => 'the nickname cannot exceed 12 characters',
        'describe.max'           => 'the introduction cannot exceed 255 characters',
        'password.require'       => 'password cannot be empty',
        'password.min'           => 'the password cannot be less than 6 characters',
        'password.max'           => 'the password cannot exceed 40 characters',
    ];
    protected $scene = [
        'login'         => ['loginAccount','loginPassword'],
        'captchalogin'  => ['loginAccount','loginPassword','captcha'],
        'password'      => ['account','password','code'],
        'register'      => ['account','password','code'],
        'bindEmail'     => ['email','code'],
        'codeEmail'     => ['email','captcha'],
        'bindMobile'    => ['mobile','code'],
        'codeMobile'    => ['mobile','captcha'],
        'set'           => ['nickname','describe'],
    ];
}