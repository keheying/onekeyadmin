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

use think\facade\Filesystem;
use think\exception\ValidateException;
use app\api\BaseController;
use app\api\addons\sendCode;
use app\api\model\User as UserModel;
use app\api\validate\User as UserValidate;
/**
 * 个人中心模块
 */
class User  extends BaseController
{
    /**
     * 用户鉴权
     */
    protected $middleware = [\app\api\middleware\AuthCheck::class];
    
    /**
     * 修改个人资料
     */
    public function set()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $this->request->userInfo->sex      = $input['sex'];
            $this->request->userInfo->birthday = $input['birthday'];
            $this->request->userInfo->nickname = $input['nickname'];
            $this->request->userInfo->describe = $input['describe'];
            $this->request->userInfo->hide     = $input['hide'];
            $this->request->userInfo->cover    = str_replace(request()->domain(), '', $input['cover']);
            $this->request->userInfo->save();
            return json(['status' => 'success','message' => '设置成功']);
        }
    }
    
    /**
     * 用户资料(别人的)
     */
    public function info()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('info')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            $field    = 'id,group_id,nickname,sex,email,mobile,cover,describe,birthday,history_integral,hide';
            $userInfo = UserModel::with(['group'])->where('id', $input['id'])->field($field)->find();
            if ($userInfo) {
                return json(['status' => 'success', 'message' => '获取成功', 'data' => $userInfo]);
            } else {
                return json(['status' => 'error', 'message' => '该用户暂未注册']);
            }
        }
    }

    /**
     * 上传图片
     */
    public function upload()
    {
        try {
            $file = $this->request->file('file');
            validate([ 'file' => [ 'fileExt'=>config('upload.ext')['image'], 'fileSize' =>config('upload.size')['image'] ]])->check(['file' => $file]);
            $url = Filesystem::putFile('user', $file);
            $url = '/upload/' . str_replace('\\', '/', $url);
            return json(['status' => 'success', 'message' => '上传成功', 'data' => $url]);
        } catch (ValidateException $e) {
            return json(['status' => 'error', 'message' => $e->getError()]);
        }
    }
    
    /**
     * 绑定邮箱
     */
    public function bindEmail()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('bindEmail')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (! password_verify($input['code'].'index_bind_email_code'.$input['email'].$input['salt'].request()->ip(), $input['rcode'])) {
                return json(["status" => "error", "message" => '验证码不正确']);
            }
            $email = UserModel::where('email', $input['email'])->value('id');
            if ($email) {
                return json(['status' => 'error', 'message' => '此邮箱号已被注册']);
            }
            $this->request->userInfo->email       = $input['email'];
            $this->request->userInfo->update_time = date('Y-m-d H:i:s');
            $this->request->userInfo->save();
            return json(['status' => 'success', 'message' => '绑定成功']);
        }
    }

    /**
     * 绑定手机
     */
    public function bindMobile()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('bindMobile')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (! password_verify($input['code'].'index_bind_mobile_code'.$input['mobile'].$input['salt'].request()->ip(), $input['rcode'])) {
                return json(["status" => "error", "message" => '验证码不正确']);
            }
            $mobile = UserModel::where('mobile', $input['mobile'])->value('id');
            if ($mobile) {
                return json(['status' => 'error', 'message' => '此手机号已被注册']);
            }
            $this->request->userInfo->mobile      = $input['mobile'];
            $this->request->userInfo->update_time = date('Y-m-d H:i:s');
            $this->request->userInfo->save();
            return json(['status' => 'success', 'message' => '绑定成功']);
        }
    }

    /**
     * 发送绑定邮箱验证码
     */
    public function sendBindEmailCode()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('codeEmail')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (UserModel::where('email', $input['email'])->value('id')) {
                return json(['status' => 'error', 'message' => '此邮箱号已被注册']);
            }
            $result = sendCode::email($input['email'], 'index_bind_email_code', '绑定邮箱');
            return json($result);
        }
    }

    /**
     * 发送绑定手机验证码
     */
    public function sendBindMobileCode()
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                validate(UserValidate::class)->scene('codeMobile')->check($input);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
            if (UserModel::where('mobile', $input['mobile'])->value('id')) {
                return json(['status' => 'error', 'message' => '此手机号已被注册']);
            }
            $result = sendCode::sms($input['mobile'], 'index_bind_mobile_code', '26BEKytK3bCe');
            return json($result);
        }
    }
}
