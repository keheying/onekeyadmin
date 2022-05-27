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

use think\facade\View;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use app\admin\BaseController;
use app\admin\model\Admin;
use app\admin\addons\User as UserAddons;
use app\admin\validate\Admin as UserValidate;
/**
 * 管理员登录、修改密码
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
                $userInfo = Admin::with(['group'])->where('account|email',$input['loginAccount'])->find();
                if ($userInfo) {
                    $password = Admin::where('id', $userInfo['id'])->value('password');
                    if (password_verify($input['loginPassword'], $password)) {
                        if ($userInfo['status'] === 1) {
                            $userInfo->login_count  = $userInfo->login_count + 1;
                            $userInfo->login_ip     = $this->request->ip();
                            $userInfo->login_time   = date('Y-m-d H:i:s');
                            $userInfo->save();
                            // 记录管理员信息
                            session('admin',$userInfo);
                            // 清除登录错误次数
                            UserAddons::clearLoginErrorNum();
                            // 勾选两周内自动登录
                            UserAddons::openAutomaticLogin($userInfo->id, $input['checked']);
                            // 钩子
                            event('LoginEnd');
                            return json(['status' => 'success', 'message' => '登录成功', 'url'=> $this->request->adminLastUrl()]);
                        } else {
                            $error = ['status' => 'error', 'message' => '账号正在审核'];
                        }
                    } else {
                        $error = ['status' => 'error', 'message' => '账号或密码错误'];
                    }
                } else {
                    $error = ['status' => 'error', 'message' => '账号或密码错误'];
                }
                // 设置当前ip登录错误次数
                UserAddons::setLoginErrorNum();
                return json($error);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
        } else {
            // 钩子
            event('LoginView');
            $userInfo = session('admin');
            return empty($userInfo) ? View::fetch() : redirect(url());
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
                $code = UserAddons::getEmailCode('admin_password_code', $input['email']);
                if ($code === (int)$input['code']) {
                    $save = Admin::where('email',$input['email'])->find();
                    if ($save) {
                        $save->password = $input['password'];
                        $save->save();
                        UserAddons::clearLoginStatus();
                        return json(['status' => 'success', 'message' => '修改成功']);
                    } else {
                        return json(['status' => 'error', 'message' => '该邮箱号未被注册！']);
                    }
                } else {
                    return json(["status" => "error","message"=>"输入的验证码有误"]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
        } else {
            return View::fetch();
        }
    }

    /**
     * 邮箱验证码
     */
    public function passwordEmailCode()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('code')->check($input);
                $email = Admin::where('email', $input['email'])->value('id');
                if ($email) {
                    $message = UserAddons::sendEmailCode($input['email'], 'admin_password_code', '修改密码');
                    return json($message);
                } else {
                    return json(['status' => 'error', 'message' => '该邮箱号未被注册！']);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
        }
    }

    /**
     * 登录验证码
     */
    public function isNeedVerification()
    {
        if ($this->request->isPost()) {
            $error_num = UserAddons::getLoginErrorNum();
            if ($error_num >= 5) {
                return json(['status' => 'error', 'message' => '需要验证']);
            } else {
                return json(['status' => 'success', 'message' => '状态正常']);
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
