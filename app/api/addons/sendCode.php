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

namespace app\api\addons;

use onekey\Email;
use plugins\alisms\addons\AliSms;
/**
 * 发送验证码
 */
class sendCode
{
    /**
     * 发送邮箱验证码
     * @param 邮箱号
     * @param 标识
     * @param 操作名
     */
    public static function email(string $email, string $name, string $operation): array
    {
        $title = '验证码';
        $code  = rand(1000,9999);
        $body  = $operation . "验证码<br/>您好" . $email . "!<br/>" . $title . "，请将验证码" . $operation . "页面。<br/>验证码：" . $code . "";
        $result = Email::send($email, $title, $body);
        if ($result['status'] === 'success') {
            $salt  = rand_id(8);
            $code  = password_hash($code.$name.$email.$salt.request()->ip(), PASSWORD_BCRYPT, ['cost' => 12]);
            return ['status' => 'success','message' => '获取成功', 'code' => $code, 'salt' => $salt];
        }
        return $result;
    }

    /**
     * 发送短信验证码
     * @param 手机号
     * @param 标识
     * @param 操作名
     */
    public static function sms(string $mobile, string $name, string $operation): array
    {
        $code = rand(1000,9999);
        // operation模板ID
        $aliSms = AliSms::send($operation, $mobile, ['code' => $code]);
        if ($aliSms['status'] === 'success') {
            $salt  = rand_id(8);
            $code  = password_hash($code.$name.$mobile.$salt.request()->ip(), PASSWORD_BCRYPT, ['cost' => 12]);
            return ['status' => 'success','message' => '获取成功', 'code' => $code, 'salt' => $salt];
        }
        return $aliSms;
    }
}