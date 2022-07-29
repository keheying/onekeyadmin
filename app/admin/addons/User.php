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

namespace app\admin\addons;

use think\facade\Db;
use app\addons\Email;
use app\admin\model\Admin;
use app\admin\model\Config;
/**
 * 用户组件
 */
class User
{
    /**
     * 盐(自行修改)
     */
    private static $salt = '513038996@qq.com';

    /**
     * 清除登录状态
     */
    public static function clearLoginStatus()
    {
        session('admin', null);
        cookie('admin_token', null);
        cookie('admin_last_url', null);
    }

    /**
     * 获取当前ip登录错误次数
     */
    public static function getLoginErrorNum(): int
    {
        $admin_error_num  = session('admin_error_num');
        return empty($admin_error_num) ? 0 : $admin_error_num;
    }

    /**
     * 设置当前ip登录错误次数
     */
    public static function setLoginErrorNum(): void
    {
        $get_num    = self::getLoginErrorNum();
        $admin_error_num  = $get_num + 1;
        session('admin_error_num', $admin_error_num);
    }

    /**
     * 清除当前ip登录错误次数
     */
    public static function clearLoginErrorNum(): void
    {
        session('admin_error_num', null);
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
            Db::name('admin_token')->save([
                'user_id'     => $userInfo['id'], 
                'token'       => $token, 
                'create_time' => date('Y-m-d H:i:s')
            ]);
            cookie('admin_token', $token, $time);
        } else {
            cookie('admin_token', null);
        }
    }
    
    /**
     * 判断自动登录的token
     */
    public static function checkAutomaticLogin()
    {
        $userInfo = session('admin');
        if (empty($userInfo)) {
            $token = cookie('admin_token');
            if ($token) {
                $time = 14*24;
                $userId = Db::name("admin_token")->where("token", $token)->whereTime("create_time","-$time hours")->value('user_id');
                $user = Admin::with(['group'])->where('status', 1)->where('id', $userId)->find();
                if ($user) {
                    if (password_verify($user['email'] . $user['password'] . self::$salt . request()->ip(), $token)) {
                        session('admin',$user);
                        $userInfo = $user;
                    } else {
                        // 曝光删除
                        Db::name("admin_token")->where("token", $token)->delete();
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
        return $result;
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
}