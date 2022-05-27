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
use think\exception\ValidateException;
use app\admin\BaseController;
use app\admin\model\AdminLog;
use app\admin\model\AdminGroup;
use app\admin\model\Admin as UserModel;
use app\admin\validate\Admin as UserValidate;
/**
 * 管理员
 */
class Admin extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $count = UserModel::withSearch(['keyword', 'status'], $input)->count();
            $data = UserModel::withSearch(['keyword', 'status'], $input)
            ->append(['c_status','disabled'])
            ->with(['group'])
            ->order($input['prop'], $input['order'])
            ->page($input['page'], $input['pageSize'])
            ->select();
            return json(['status' => 'success','message' => '获取成功', 'data' => $data, 'count' => $count]);
        } else {
            $group = AdminGroup::where('status', 1)->order('id', 'asc')->select();
            View::assign('group', $group);
            return View::fetch();
        }
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        try {
            $input = input('post.');
            validate(UserValidate::class)->scene('save')->check($input);
            if (UserModel::where('account', $input['account'])->value('id')) {
                return json(['status' => 'error', 'message' => '账号已经存在！']);
            }
            if (UserModel::where('email', $input['email'])->value('id')) {
                return json(['status' => 'error', 'message' => '邮箱号已经存在！']);
            }
            $date = date('Y-m-d H:i:s');
            $input['admin_id']    = $this->request->userInfo->id;
            $input['login_ip']    = "";
            $input['login_count'] = 0;
            $input['login_time']  = $date;
            $input['create_time'] = $date;
            UserModel::create($input);
            return json(['status' => 'success', 'message' => '新增成功']);
        } catch ( ValidateException $e ) {
            return json(['status' => 'error', 'message' => $e->getError()]);
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        try {
            $input = input('post.');
            validate(UserValidate::class)->scene('save')->check($input);
            $where[] = ['id', '<>', $input['id']];
            if (UserModel::where('account', $input['account'])->where($where)->value('id')) {
                return json(['status' => 'error', 'message' => '账号已经存在！']);
            }
            if (UserModel::where('email', $input['email'])->where($where)->value('id')) {
                return json(['status' => 'error', 'message' => '邮箱号已经存在！']);
            }
            if (empty($input['password'])) unset($input['password']);
            UserModel::update($input);
            return json(['status' => 'success', 'message' => '修改成功']);
        } catch ( ValidateException $e ) {
            return json(['status' => 'error', 'message' => $e->getError()]);
        }
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            UserModel::destroy(input('post.ids'));
            return json(['status' => 'success', 'message' => '删除成功']);
        }
    }

    /**
     * 个人中心
     */
    public function personal() 
    {
        if ($this->request->isPost()) {
            try {
                $input = input('post.');
                $userId = $this->request->userInfo->id;
                validate(UserValidate::class)->scene('save')->check($input);
                if (UserModel::where('email',$input['email'])->where('id', '<>', $userId)->value('id')) return json(['status' => 'error', 'message' => '邮箱号已经存在！']);
                $save = UserModel::with(['group'])->find($userId);
                $save->nickname = $input['nickname'];
                $save->email    = $input['email'];
                $save->cover    = $input['cover'];
                if (! empty($input['password'])) {
                    $save->password = $input['password'];
                }
                $save->save();
                session('admin',$save);
                return json(['status' => 'success', 'message' => '修改成功']);
            } catch ( ValidateException $e ) {
                return json(['status' => 'error', 'message' => $e->getError()]);
            }
        } else {
            return View::fetch();
        }
    }
}
