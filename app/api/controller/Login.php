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
namespace app\api\controller;

use think\exception\ValidateException;
use app\api\BaseController;
use app\api\addons\sendCode;
use app\api\model\UserGroup;
use app\api\model\UserToken;
use app\api\model\User as UserModel;
use app\api\validate\User as UserValidate;
/**
 * 登录模块
 */
class Login extends BaseController
{
    /**
     * 登录
     */
    public function index()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('login')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            $userInfo = UserModel::with(['group'])->append(['url'])->where('mobile|email',$input['account'])->find();
            if (! $userInfo) {
                return json(['status' => 'error', 'message' => '账号不存在']);
            }
            $password = UserModel::where('id', $userInfo->id)->value('password');
            if (! $password) {
                return json(['status' => 'error', 'message' => '密码或账号错误']);
            }
            if (! password_verify($input['password'], $password)) {
                return json(['status' => 'error', 'message' => '密码或账号错误']);
            }
            if ($userInfo->status != 1) { 
                return json(['status' => 'error', 'message' => '您的账号被屏蔽']);
            }
            // 生成令牌
            $token = password_hash($userInfo->id . $this->request->ip() . $password, PASSWORD_BCRYPT, ['cost' => 12]);
            UserToken::create([
                'user_id'     => $userInfo->id,
                'token'       => $token,
                'create_time' => date('Y-m-d H:i:s')
            ]);
            // 修改信息
            $userInfo->login_count = $userInfo->login_count + 1;
            $userInfo->login_ip    = $this->request->ip();
            $userInfo->login_time  = date('Y-m-d H:i:s');
            $userInfo->save();
            return json(['status' => 'success', 'message' => '登录成功', 'token' => $token, 'userInfo' => $userInfo]);
        }
    }
    
    /**
     * 邮箱找回密码
     */
    public function passwordEmail()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('passwordEmail')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (! password_verify($input['code'].'index_password_email_code'.$input['email'].$input['salt'].$this->request->ip(), $input['rcode'])) {
                return json(["status" => "error", "message" => '验证码不正确']);
            }
            $save = UserModel::where('email', $input['email'])->find();
            if (! $save) {
                return json(['status' => 'error', 'message' => '账号不存在']);
            }
            $save->password = $input['password'];
            $save->save();
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }

    /**
     * 短信找回密码
     */
    public function passwordMobile()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('passwordMobile')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (! password_verify($input['code'].'index_password_mobile_code'.$input['mobile'].$input['salt'].$this->request->ip(), $input['rcode'])) {
                return json(["status" => "error", "message" => '验证码不正确']);
            }
            $save = UserModel::where('mobile', $input['mobile'])->find();
            if (! $save) {
                return json(['status' => 'error', 'message' => '账号不存在']);
            }
            $save->password = $input['password'];
            $save->save();
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }
    
    /**
     * 邮箱注册
     */
    public function registerEmail()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('registerEmail')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (! password_verify($input['code'].'index_register_email_code'.$input['email'].$input['salt'].$this->request->ip(), $input['rcode'])) {
                return json(["status" => "error", "message" => '邮箱验证码错误']);
            }
            if (UserModel::where('email', $input['email'])->value('id')) {
                return json(['status' => 'error', 'message' => '邮箱号已被注册']);
            }
            $group = UserGroup::where('default',1)->find();
            $group_id = 0;
            $integral = 0;
            if ($group) {
                $group_id = $group['id'];
                $integral = $group['integral'];
            }
            $date = date('Y-m-d H:i:s');
            $registerInfo = UserModel::create([
                'group_id'         => $group_id,
                'nickname'         => '未命名',
                'cover'            => '/upload/avatar.jpg',
                'sex'              => 0,
                'email'            => $input['email'],
                'mobile'           => "",
                'account'          => "",
                'password'         => $input['password'],
                'pay_paasword'     => "",
                'describe'         => "这个人很懒，什么都没有留下。",
                'birthday'         => date('Y-m-d'),
                'now_integral'     => $integral,
                'history_integral' => $integral,
                'balance'          => 0,
                'login_ip'         => "",
                'login_count'      => 0,
                'login_time'       => $date,
                'update_time'      => $date,
                'create_time'      => $date,
                'status'           => 1,
                'hide'             => 1,
            ]);
            // 绑定事件
            event('RegisterEnd', $registerInfo);
            return json(['status' => 'success', 'message' => '注册成功']);
        }
    }
    
    /**
     * 手机注册
     */
    public function registerMobile()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('registerMobile')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (! password_verify($input['code'].'index_register_mobile_code'.$input['mobile'].$input['salt'].$this->request->ip(), $input['rcode'])) {
                return json(["status" => "error", "message" => '手机验证码错误']);
            }
            if (UserModel::where('mobile', $input['mobile'])->value('id')) {
                return json(['status' => 'error', 'message' => '手机号已被注册']);
            }
            $group = UserGroup::where('default',1)->find();
            $group_id = 0;
            $integral = 0;
            if ($group) {
                $group_id = $group['id'];
                $integral = $group['integral'];
            }
            $date = date('Y-m-d H:i:s');
            $registerInfo = UserModel::create([
                'group_id'         => $group_id,
                'nickname'         => '未命名',
                'cover'            => '/upload/avatar.jpg',
                'sex'              => 0,
                'email'            => "",
                'mobile'           => $input['mobile'],
                'account'          => "",
                'password'         => $input['password'],
                'pay_paasword'     => "",
                'describe'         => "这个人很懒，什么都没有留下。",
                'birthday'         => date('Y-m-d'),
                'now_integral'     => $integral,
                'history_integral' => $integral,
                'balance'          => 0,
                'login_ip'         => '',
                'login_count'      => 0,
                'login_time'       => $date,
                'update_time'      => $date,
                'create_time'      => $date,
                'status'           => 1,
                'hide'             => 1,
            ]);
            // 绑定事件
            event('RegisterEnd', $registerInfo);
            return json(['status' => 'success', 'message' => '注册成功']);
        }
    }

    /**
     * 发送注册邮箱验证码
     */
    public function sendRegisterEmailCode()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('codeEmail')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (UserModel::where('email', $input['email'])->value('id')) {
                return json(['status' => 'error', 'message' => '邮箱号已被注册']);
            }
            $result = sendCode::email($input['email'], 'index_register_email_code','用户注册');
            return json($result);
        }
    }

    /**
     * 发送注册短信验证码
     */
    public function sendRegisterMobileCode()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('codeMobile')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (UserModel::where('mobile', $input['mobile'])->value('id')) {
                return json(['status' => 'error', 'message' => '手机号已被注册']);
            }
            $result = sendCode::sms($input['mobile'], 'index_register_mobile_code', '26BEKytK3bCe');
            return json($result);
        }
    }

    /**
     * 发送修改密码邮箱验证码
     */
    public function sendPasswordEmailCode()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('codeEmail')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (UserModel::where('email', $input['email'])->value('id')) {
                $result = sendCode::email($input['email'], 'index_password_email_code', '修改密码');
                return json($result);
            } else {
                return json(['status' => 'error', 'message' => '邮箱号未注册']);
            }
        }
    }

    /**
     * 发送修改密码短信验证码
     */
    public function sendPasswordMobileCode()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('codeMobile')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (UserModel::where('mobile', $input['mobile'])->value('id')) {
                $result = sendCode::sms($input['mobile'], 'index_password_mobile_code', '26BEKytK3bCe');
                return json($result);
            } else {
                return json(['status' => 'error', 'message' => '手机号未注册']);
            }
        }
    }
}