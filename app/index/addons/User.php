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
declare (strict_types = 1);

namespace app\index\addons;

use think\facade\Db;
use think\facade\View;
use app\index\model\User as UserModel;
/**
 * 用户组件
 */
class User
{
    // 盐(自行修改)
    private static $salt = '513038996@qq.com';

    /**
     * 清除登录状态
     */
    public static function clearLoginStatus()
    {
        session('user', null);
        cookie('index_token', null);
        cookie('index_last_url', null);
    }
    
    /**
     * 获取当前ip登录错误次数
     */
    public static function getLoginErrorNum(): int
    {
        $index_error_num  = session('index_error_num');
        return empty($index_error_num) ? 0 : $index_error_num;
    }

    /**
     * 设置当前ip登录错误次数
     */
    public static function setLoginErrorNum(): void
    {
        $get_num    = self::getLoginErrorNum();
        $index_error_num  = $get_num + 1;
        session('index_error_num', $index_error_num);
    }

    /**
     * 清除当前ip登录错误次数
     */
    public static function clearLoginErrorNum(): void
    {
        session('index_error_num', null);
    }

    /**
     * 是否开启自动登录验证
     * @param 用户id
     * @param 是否自动登录
     */
    public static function openAutomaticLogin(Object $userInfo, $open = true): void
    {
        if ($open && $open != 'false') {
            $key = $userInfo['email'] . $userInfo['password'] . self::$salt . request()->ip();
            $token = password_hash($key, PASSWORD_BCRYPT, ['cost' => 12]);
            $time = 14*24*3600;
            Db::name('user_token')->save([
                'user_id'     => $userInfo['id'], 
                'token'       => $token, 
                'create_time' => date('Y-m-d H:i:s')
            ]);
            cookie('index_token', $token, $time);
        } else {
            cookie('index_token', null);
        }
    }
    
    /**
     * 根据token自动登录
     */
    public static function checkAutomaticLogin()
    {
        $userInfo = session('user');
        if (empty($userInfo)) {
            $token = cookie('index_token');
            if ($token) {
                $time = 14*24;
                $userId = Db::name("user_token")->where("token", $token)->whereTime("create_time","-$time hours")->value('user_id');
                $user = UserModel::with(['group'])->where('status', 1)->where('id', $userId)->find();
                if ($user) {
                    if (password_verify($user['email'] . $user['password'] . self::$salt . request()->ip(), $token)) {
                        session('user',$user);
                        $userInfo = $user;
                    } else {
                        // 曝光删除
                        Db::name("user_token")->where("token", $token)->delete();
                    }
                }
            }
        }
        return $userInfo;
    }

    /**
     * 发送邮箱验证码
     * @param 邮箱号
     * @param 标识
     * @param 操作名
     */
    public static function sendEmailCode(string $email, string $name, string $operation): array
    {
        $value = session($name);
        if (! empty($value)) {
            $interval = 60;
            $lasttime = unserialize($value)['time'];
            if (time() - $lasttime < $interval) {
                return ['status'=>'error','message' => lang('fetching too frequently, please wait a minute and try again')];
            }
        }
        $title = request()->system['company'];
        $code  = rand(1000,9999);
        $body  = $operation . lang('verification') . "<br/>" . lang('hello') . "" . $email . "!<br/>" . $title . "，" . lang('please fill in the verification code to') . "" . $operation . "" . lang('page') . "。<br/>" . lang('verification code') . "：" . $code . "";
        $result = \app\addons\Email::send($email, $title, $body);
        if ($result['status'] === 'success') {
            $arr['code']  = $code;
            $arr['email'] = $email;
            $arr['time']  = time();
            $session      = serialize($arr);
            session($name, $session, 60*5); // 有效期5分钟
        }
        return $result;
    }

    /**
     * 发送短信验证码
     * @param 手机号
     * @param 标识
     * @param 操作名
     */
    public static function sendSmsCode(string $mobile, string $name, string $operation): array
    {
        $value = session($name);
        if (! empty($value)) {
            $interval = 60;
            $lasttime = unserialize($value)['time'];
            if (time() - $lasttime < $interval) {
                return ['status'=>'error','message' => lang('fetching too frequently, please wait a minute and try again')];
            }
        }
        $code   = rand(1000,9999);
        // operation模板ID
        $aliSms = \plugins\alisms\addons\AliSms::send($operation, $mobile, ['code' => $code]);
        if ($aliSms['status'] === 'success') {
            $arr['code']   = $code;
            $arr['mobile'] = $mobile;
            $arr['time']   = time();
            $session       = serialize($arr);
            session($name, $session, 60*5);// 有效期5分钟
        }
        return $result;
    }

    /**
     * 获取短信验证码
     * @param 标识
     * @param 手机号
     */
    public static function getSmsCode(string $name, string $mobile): ?int
    {
        $value = session($name);
        if (! empty($value)) {
            $value = unserialize($value);
            return $value['mobile'] === $mobile ? $value['code'] : null;
        } else {
            return null;
        }
    }

    /**
     * 获取邮箱验证码
     * @param 标识
     * @param 邮箱号
     */
    public static function getEmailCode(string $name, string $email): ?int
    {
        $value = session($name);
        if (! empty($value)) {
            $value = unserialize($value);
            return $value['email'] === $email ? $value['code'] : null;
        } else {
            return null;
        }
    }

    /**
     * 权限验证（按需调用）
     * @param 权限
     */
    public static function authorityCheck(array $group = [])
    {
        if (request()->route === 'login') return true;
        if (empty($group)) return true;
        $message = lang('insufficient permissions');
        if (empty(request()->userInfo)) {
            return request()->isPost() ? json(['status'=>'login', 'message'=> $message]) : redirect(url('login/index'));
        } else {
            if (! in_array(request()->userInfo['group_id'], $group)) {
                return request()->isPost() ? json(["status"=>"error", "message"=> $message]) : abort(403);
            } else {
                return true;
            }
        }
    }
}