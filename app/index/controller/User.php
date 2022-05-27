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
use think\exception\ValidateException;
use app\index\BaseController;
use app\index\model\UserLog;
use app\index\model\User as UserModel;
use app\index\addons\User as UserAddons;
use app\index\validate\User as UserValidate;
/**
 * 个人中心模块
 */
class User  extends BaseController
{
    protected function initialize()
    {
        parent::initialize();
        $object = (object)[]; 
        // 侧边
        $object->site = [
            ['title' => lang('personal center'), 'cover' => '/themes/template/static/images/user.png', 'url' => url('user/index')],
            ['title' => lang('account settings'), 'cover' => '/themes/template/static/images/set.png', 'url' => url('user/set')],
        ];
        // 钩子
        event('UserSite', $object);
        View::assign('userSite', $object->site);
    }

    /**
     * 个人中心
     */
    public function index()
    {
        $object = (object)[];
        // 主页
        $object->index = [
            [
                'count' => $this->request->userInfo->balance, 
                'title' => lang('my bill'), 
                'cover' => '/themes/template/static/images/balance.png', 
                'url' => url('user/balance')
            ],
            [
                'count' => $this->request->userInfo->now_integral, 
                'title' => lang('points details'), 
                'cover' => '/themes/template/static/images/integral.png', 
                'url' => url('user/integral')
            ],
        ];
        // 钩子
        event('UserIndex', $object);
        View::assign('userIndex', $object->index);
        return View::fetch("user/index");
    }

    /**
     * 账户设置
     */
    public function set()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $this->request->userInfo->nickname = $input['nickname'];
            $this->request->userInfo->describe = $input['describe'];
            $this->request->userInfo->hide     = (int)$input['hide'];
            $this->request->userInfo->cover    = str_replace(request()->domain(), '', $input['cover']);
            $this->request->userInfo->save();
            session('user', $this->request->userInfo);
            // 钩子
            event('UserSet');
            return json(['status' => 'success','message' => lang('successful operation')]);
        } else {
            return View::fetch("user/set");
        }
    }

    /**
     * 钱包中心
     */
    public function balance()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $where[] = ['type', '=', 'balance'];
            $where[] = ['user_id', '=', $this->request->userInfo->id];
            $count = UserLog::where($where)->withSearch(['keyword','date'], $input)->count();
            $data  = UserLog::where($where)->withSearch(['keyword','date'], $input)->order('create_time', 'desc')->page($input['page'], 10)->select();
            return json(['status' => 'success','message' => lang('successful operation'), 'data' => $data, 'count' => $count]);
        } else {
            return View::fetch("user/balance");
        }
    }

    /**
     * 积分中心
     */
    public function integral()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $where[] = ['type', '=', 'integral'];
            $where[] = ['user_id', '=', $this->request->userInfo->id];
            $count = UserLog::where($where)->withSearch(['keyword','date'], $input)->count();
            $data  = UserLog::where($where)->withSearch(['keyword','date'], $input)->order('create_time', 'desc')->page($input['page'], 10)->select();
            return json(['status' => 'success','message' => lang('successful operation'), 'data' => $data, 'count' => $count]);
        } else {
            return View::fetch("user/integral");
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
            return json(['status' => 'success', 'message' => lang('successful operation'), 'data' => $url]);
        } catch (ValidateException $e) {
            return json(['status' => 'error', 'message' => lang($e->getError())]);
        }
    }

    /**
     * 关注
     */
    public function follow() 
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            if ($this->request->userInfo->id != $input['id']) {
                $follow = UserLog::where('user_id', $this->request->userInfo->id)->where('to_id',$input['id'])->where('type','fans')->find();
                if ($follow) {
                    UserLog::where('user_id', $this->request->userInfo->id)->where('to_id',$input['id'])->where('type','fans')->delete();
                } else {
                    UserLog::create([
                        'user_id'     => $this->request->userInfo->id,
                        'explain'     => 'fans',
                        'number'      => 0,
                        'inc'         => 1,
                        'to_id'       => $input['id'],
                        'type'        => 'fans',
                        'create_time' => date('Y-m-d H:i:s'),
                    ]);
                }
                return json(['status' => 'success','message' => lang('successful operation')]);
            } else {
                return json(['status' => 'error','message' => lang('can t take care of oneself')]);
            }
        }
    }

    /**
     * 留言
     */
    public function message() 
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            UserLog::create([
                'user_id'     => $this->request->userInfo->id,
                'explain'     => $input['content'],
                'number'      => 0,
                'inc'         => 1,
                'to_id'       => $input['id'],
                'type'        => 'message',
                'create_time' => date('Y-m-d H:i:s'),
            ]);
            return json(['status' => 'success','message' => lang('successful operation')]);
        }
    }

    /**
     * 移除留言
     */
    public function messageDelete() 
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            UserLog::where('to_id', $this->request->userInfo->id)->whereIn('id',$input['id'])->where('type', 'message')->delete();
            return json(['status' => 'success', 'message' => lang('successful operation')]);
        }
    }

    /**
     * 移除粉丝
     */
    public function fansDelete() 
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            UserLog::where('to_id', $this->request->userInfo->id)->whereIn('id',$input['id'])->where('type', 'fans')->delete();
            return json(['status' => 'success', 'message' => lang('successful operation')]);
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
                $code = UserAddons::getEmailCode('index_bind_email_code', $input['email']);
                if ($code == $input['code']) {
                    $email = UserModel::where('email', $input['email'])->value('id');
                    if (! $email) {
                        $this->request->userInfo->email = $input['email'];
                        $this->request->userInfo->update_time = date('Y-m-d H:i:s');
                        $this->request->userInfo->save();
                        session('user', $this->request->userInfo);
                        return json(['status' => 'success', 'message' => lang('successful operation')]);
                    } else {
                        return json(['status' => 'error', 'message' => lang('this email number is already registered')]);
                    }
                } else {
                    return json(["status" => "error", "message" => lang('the verification code entered is incorrect')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
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
                $code = UserAddons::getSmsCode('index_bind_mobile_code', $input['mobile']);
                if ($code == $input['code']) {
                    $mobile = UserModel::where('mobile', $input['mobile'])->value('id');
                    if (! $mobile) {
                        $this->request->userInfo->mobile = $input['mobile'];
                        $this->request->userInfo->update_time = date('Y-m-d H:i:s');
                        $this->request->userInfo->save();
                        session('user', $this->request->userInfo);
                        return json(['status' => 'success', 'message' => lang('successful operation')]);
                    } else {
                        return json(['status' => 'error', 'message' => lang('this mobile number is already registered')]);
                    }
                } else {
                    return json(["status" => "error", "message" => lang('the verification code entered is incorrect')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
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
                $email = UserModel::where('email', $input['email'])->value('id');
                if (! $email) {
                    $message = UserAddons::sendEmailCode($input['email'], 'index_bind_email_code', lang('bind email'));
                    return json($message);
                } else {
                    return json(['status' => 'error', 'message' => lang('this email number is already registered')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
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
                $mobile = UserModel::where('mobile', $input['mobile'])->value('id');
                if (! $mobile) {
                    $message = UserAddons::sendSmsCode($input['mobile'], 'index_bind_mobile_code', '26BEKytK3bCe');
                    return json($message);
                } else {
                    return json(['status' => 'error', 'message' => lang('this mobile number is already registered')]);
                }
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => lang($e->getError())]);
            }
        }
    }
}
