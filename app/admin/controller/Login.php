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

use onekey\Email;
use think\facade\View;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use app\admin\BaseController;
use app\admin\model\AdminToken;
use app\admin\model\Admin as AdminModel;
use app\admin\validate\Admin as AdminValidate;
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
                validate(AdminValidate::class)->scene($scene)->check($input);
                $userInfo = AdminModel::with(['group'])->where('account|email',$input['loginAccount'])->find();
                if ($userInfo) {
                    $password = AdminModel::where('id', $userInfo['id'])->value('password');
                    if (password_verify($input['loginPassword'], $password)) {
                        if ($userInfo['status'] === 1) {
                            $userInfo->login_count  = $userInfo->login_count + 1;
                            $userInfo->login_ip     = $this->request->ip();
                            $userInfo->login_time   = date('Y-m-d H:i:s');
                            $userInfo->save();
                            // 记录管理员信息
                            session('admin',$userInfo);
                            // 清除登录错误次数
                            session('admin_error_num', null);
                            return json(['status' => 'success', 'message' => '登录成功']);
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
                $error_num  = session('admin_error_num');
                $error_num  = empty($error_num) ? 1 : $error_num + 1;
                session('admin_error_num', $error_num);
                return json($error);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
        } else {
            session('admin', null);
            return View::fetch();
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
                validate(AdminValidate::class)->scene('password')->check($input);
                $value = session('admin_password_code');
                if (! empty($value)) {
                    $value = unserialize($value);
                    $code  = $value['email'] === $input['email'] ? $value['code'] : null;
                } else {
                    $code  = null;
                }
                if ($code === (int)$input['code']) {
                    $save = AdminModel::where('email',$input['email'])->find();
                    if ($save) {
                        $save->password = $input['password'];
                        $save->save();
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
                validate(AdminValidate::class)->scene('code')->check($input);
                if (AdminModel::where('email', $input['email'])->value('id')) {
                    $emil      = $input['email'];
                    $name      = 'admin_password_code';
                    $operation = '修改密码';
                    $value     = session($name);
                    if (! empty($value)) {
                        $interval = 60;
                        $lasttime = unserialize($value)['time'];
                        if (time() - $lasttime < $interval) {
                            return ['status'=>'error','message'=>'获取过于频繁，请等待一分钟后再试'];
                        }
                    }
                    $title  = 'OneKeyAdmin';
                    $code   = rand(1000,9999);
                    $body   = $operation."验证<br/>您好".$email."!<br/> ".$title."，请将验证码填写到".$operation."。<br/>验证码：".$code."";
                    $result = Email::send($email, $title, $body);
                    if ($result['status'] === 'success') {
                        $arr['code']  = $code;
                        $arr['email'] = $email;
                        $arr['time']  = time();
                        $session      = serialize($arr);
                        session($name, $session);
                    }
                    return json($result);
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
            $error_num  = session('admin_error_num');
            $error_num  = empty($error_num) ? 0 : $error_num;
            if ($error_num < 5) {
                return json(['status' => 'success', 'message' => '状态正常']);
            } else {
                return json(['status' => 'error', 'message' => '需要验证']);
            }
        }
    }

    /**
     * 系统验证码
     */
    public function verify()
    {
        return Captcha::create();
    }
}
