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
use think\facade\Filesystem;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use app\index\BaseController;
use app\index\model\UserGroup;
use app\index\model\User as UserModel;
use app\index\addons\User as UserAddons;
use app\index\validate\User as UserValidate;
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
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                $scene = $this->isNeedVerification() === 1 ? 'captchalogin' : 'login';
                validate(UserValidate::class)->scene($scene)->check($input);
                $userInfo = UserModel::with(['group'])->where('mobile|email',$input['loginAccount'])->find();
                if ($userInfo) {
                    $password = UserModel::where('id', $userInfo['id'])->value('password');
                    if (password_verify($input['loginPassword'], $password)) {
                        if ($userInfo['status'] === 1) {
                            $userInfo->login_count = $userInfo->login_count + 1;
                            $userInfo->login_ip    = $this->request->ip();
                            $userInfo->login_time  = date('Y-m-d H:i:s');
                            $userInfo->save();
                            // 记录用户信息
                            session('user',$userInfo);
                            // 清除登录错误次数
                            UserAddons::clearLoginErrorNum();
                            // 勾选两周内自动登录
                            UserAddons::openAutomaticLogin($userInfo, $input['checked']);
                            // 钩子
                            event('LoginEnd',$userInfo);
                            return json(['status' => 'success', 'message' => lang('login successful'), 'url'=> $this->request->indexLastUrl()]);
                        } else {
                            $error = ['status' => 'error', 'message' => lang('account under review')];
                        }
                    } else {
                        $error = ['status' => 'error', 'message' => lang('incorrect username or password')];
                    }
                } else {
                    $error = ['status' => 'error', 'message' => lang('incorrect username or password')];
                }
                // 设置当前ip登录错误次数
                UserAddons::setLoginErrorNum();
                return json($error);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
        } else {
            // 钩子
            event('LoginView');
            return empty($this->request->userInfo) ? View::fetch("login/index") : redirect($this->request->indexLastUrl());
        }
    }
    
    /**
     * 修改密码
     */
    public function password()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('password')->check($input);
                // 邮箱/手机
                if (empty($input['mobile'])) {
                    $code = UserAddons::getEmailCode('index_password_email_code', $input['email']);
                    $save = UserModel::where('email',$input['email'])->find();
                } else {
                    $code = UserAddons::getSmsCode('index_password_mobile_code', $input['mobile']);
                    $save = UserModel::where('mobile',$input['mobile'])->find();
                }
                // 验证码错误
                if ($code != $input['code']) {
                    return json(["status" => "error", "message" => lang('the verification code entered is incorrect')]);
                }
                if ($save) {
                    $save->password = $input['password'];
                    $save->save();
                    UserAddons::clearLoginStatus();
                    return json(['status' => 'success', 'message' => lang('successful operation')]);
                } else {
                    return json(['status' => 'error', 'message' => lang('this account is not registered')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
        } else {
            return View::fetch("login/password");
        }
    }

    /**
     * 注册
     */
    public function register()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('register')->check($input);
                // 邮箱/手机
                if (empty($input['mobile'])) {
                    if ($this->request->userConfig['register_email']) {
                        $code = UserAddons::getEmailCode('index_register_email_code', $input['email']);
                        $save = UserModel::where('email', $input['email'])->value('id');
                    } else {
                        return json(["status" => "error", "message" => lang('close mailbox registration')]);
                    }
                } else {
                    if ($this->request->userConfig['register_mobile']) {
                        $code = UserAddons::getSmsCode('index_register_mobile_code', $input['mobile']);
                        $save = UserModel::where('mobile', $input['mobile'])->value('id');
                    } else {
                        return json(["status" => "error", "message" => lang('close mobile phone number registration')]);
                    }
                }
                // 验证码错误
                if ($code != $input['code']) {
                    return json(["status" => "error", "message" => lang('the verification code entered is incorrect')]);
                }
                if ($save) {
                    return json(['status' => 'error', 'message' => lang('the account is already registered')]);
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
                    'mobile'           => $input['mobile'],
                    'account'          => "",
                    'password'         => $input['password'],
                    'pay_paasword'     => "",
                    'cover'            => "",
                    'describe'         => lang("no personal signature"),
                    'birthday'         => date('Y-m-d'),
                    'now_integral'     => $integral,
                    'history_integral' => $integral,
                    'balance'          => 0,
                    'login_ip'         => $this->request->ip(),
                    'login_count'      => 0,
                    'login_time'       => $date,
                    'update_time'      => $date,
                    'create_time'      => $date,
                    'status'           => 1,
                    'hide'             => 1,
                ]);
                // 清除登录状态
                UserAddons::clearLoginStatus();
                // 钩子
                event("RegisterEnd", $registerInfo);
                return json(['status' => 'success', 'message' => lang('the registration is successful, please log in')]);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
        } else {
            // 钩子
            event('RegisterView');
            return View::fetch("login/register");
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
                if (! $this->request->userConfig['register_email']) {
                    return json(["status" => "error", "message" => lang('close mailbox registration')]);
                }
                $email = UserModel::where('email', $input['email'])->value('id');
                if (! $email) {
                    $message = UserAddons::sendEmailCode($input['email'], 'index_register_email_code', lang('user registration'));
                    return json($message);
                } else {
                    return json(['status' => 'error', 'message' => lang('the account is already registered')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
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
                if (! $this->request->userConfig['register_mobile']) {
                    return json(["status" => "error", "message" => lang('close mobile phone number registration')]);
                }
                $mobile = UserModel::where('mobile', $input['mobile'])->value('id');
                if (! $mobile) {
                    $message = UserAddons::sendSmsCode($input['mobile'], 'index_register_mobile_code', '26BEKytK3bCe');
                    return json($message);
                } else {
                    return json(['status' => 'error', 'message' => lang('the account is already registered')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
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
                $email = UserModel::where('email', $input['email'])->value('id');
                if ($email) {
                    $message = UserAddons::sendEmailCode($input['email'], 'index_password_email_code', lang('change passwor'));
                    return json($message);
                } else {
                    return json(['status' => 'error', 'message' => lang('this account is not registered')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
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
                $mobile = UserModel::where('mobile', $input['mobile'])->value('id');
                if ($mobile) {
                    $message = UserAddons::sendSmsCode($input['mobile'], 'index_password_mobile_code', '26BEKytK3bCe');
                    return json($message);
                } else {
                    return json(['status' => 'error', 'message' => lang('this account is not registered')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
        }
    }

    /**
     * 是否开启登录验证码
     * @return int
     */
    public function isNeedVerification()
    {
        if ($this->request->isPost()) {
            $error_num = UserAddons::getLoginErrorNum();
            if ($error_num > 5) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        UserAddons::clearLoginStatus();
        return redirect(url("login/index"));
    }

    /**
     * 系统验证码
     */
    public function verify()
    {
        return Captcha::create();
    }
}