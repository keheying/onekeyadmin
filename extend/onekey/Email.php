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

namespace onekey;

use phpmailer\phpmailer;
use think\facade\Validate;
use app\admin\model\Config;

class Email
{
    /**
    * @param 收件人邮箱 
    * @param 邮件标题
    * @param 邮件内容
    */
    public static function send(string $email, string $title, string $body): array
    {
        $config = Config::getVal('email');
        $rule = [];
        $rule['smtp']      = 'require';
        $rule['email']     = 'require|email';
        $rule['password']  = 'require';
        $rule['sender']    = 'require';
        $rule['sendstyle'] = 'require';
        $validate = Validate::rule($rule);
        if (!$validate->check($config)) {
            return ['status' => 'error', 'message' => '系统未配置邮箱'];
        }
        $host       = $config['smtp'];      // 发送方的SMTP服务器地址 
        $username   = $config['email'];     // 发送方的邮箱用户名
        $password   = $config['password'];  // 发送方的邮箱客户端授权密码
        $from       = $config['email'];     // 发件人邮箱
        $fromTitle  = $config['sender'];    // 发件人名字  如：(xxxx@qq.com）
        $replyTo    = $config['email'];     // 回复人邮箱
        $replyTitle = $config['sender'];    // 回复人名字
        $smtpSecure = $config['sendstyle']; // 使用的协议方式，如ssl/tls
        $port       = $config['sendstyle'] === 'ssl' ? 465 : 25;
        $body       = preg_replace('/\\\\/','', $body);
        $mail       = new PHPMailer();  
        $mail->CharSet    = "utf8"; 
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port       = $port ;  
        $mail->Subject    = $title;
        $mail->isSMTP();
        $mail->setLanguage('zh_cn');
        $mail->setFrom($from,$fromTitle);
        $mail->addAddress($email);
        $mail->addReplyTo($replyTo,$replyTitle);
        $mail->MsgHTML($body);
        $mail->IsHTML(true);
        $message = $mail->send();
        return $message === true ? ['status' => 'success', 'message' => '获取成功'] : ['status' => 'error', 'message' => $message];
    }
}