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
use app\admin\model\Config;
use app\admin\model\UserGroup;
use app\admin\model\User as UserModel;
use app\admin\validate\User as UserValidate;
/**
 * 用户管理
 */
class User extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input  = input('post.');
            $search = ['keyword','date','status'];
            $append = ['url'];
            $page     = empty($input['page']) ? 1 : $input['page'];
            $pageSize = empty($input['pageSize']) ? 20 : $input['pageSize'];
            if (! empty($input['prop']) && ! empty($input['order'])) {
                $order = [$input['prop'] => $input['order']];
            } else {
                $order = ['create_time' => 'desc'];
            }
            $count  = UserModel::withSearch($search, $input)->count();
            $data   = UserModel::withSearch($search, $input)->append($append)->with(['group'])->order($order)->page($page, $pageSize)->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data, 'count' => $count]);
        } else {
            $group = UserGroup::where('status', 1)->order('integral', 'asc')->select();
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
            if (! empty($input['mobile'])) {
                if (UserModel::where('mobile', $input['mobile'])->value('id')) {
                    return json(['status' => 'error', 'message' => '手机号已经存在！']);
                }
            }
            if (! empty($input['email'])) {
                if (UserModel::where('email', $input['email'])->value('id')) {
                    return json(['status' => 'error', 'message' => '邮箱号已经存在！']);
                }
            }
            $integral = UserGroup::where('id', $input['group_id'])->value('integral');
            $date = date('Y-m-d H:i:s');
            $input['pay_paasword']     = '';
            $input['now_integral']     = $integral;
            $input['history_integral'] = $integral;
            $input['login_ip']         = '';
            $input['login_count']      = 0;
            $input['birthday']         = $input['birthday'] ? $input['birthday'] : date('Y-m-d');
            $input['login_time']       = $date;
            $input['update_time']      = $date;
            $input['create_time']      = $date;
            $input['hide']             = 1;
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
            validate(UserValidate::class)->check($input);
            $where[] = ['id', '<>', $input['id']];
            if (! empty($input['mobile'])) {
                if (UserModel::where('mobile', $input['mobile'])->where($where)->value('id')) {
                    return json(['status' => 'error', 'message' => '手机号已经存在！']);
                }
            }
            if (! empty($input['email'])) {
                if (UserModel::where('email', $input['email'])->where($where)->value('id')) {
                    return json(['status' => 'error', 'message' => '邮箱号已经存在！']);
                }
            }
            $save = UserModel::find($input['id']);
            $integral = UserGroup::where('id', $input['group_id'])->value('integral');
            if ($input['group_id'] != $save->group_id) {
                $save->history_integral = $integral;
            }
            if (! empty($input['password'])) {
                $save->password = $input['password'];
            }
            $save->group_id         = $input['group_id'];
            $save->nickname         = $input['nickname'];
            $save->sex              = $input['sex'];
            $save->email            = $input['email'];
            $save->mobile           = $input['mobile'];
            $save->cover            = $input['cover'];
            $save->describe         = $input['describe'];
            $save->birthday         = $input['birthday'];
            $save->now_integral     = $input['now_integral'];
            $save->balance          = $input['balance'];
            $save->create_time      = $input['create_time'];
            $save->status           = $input['status'];
            $save->save();
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
}
