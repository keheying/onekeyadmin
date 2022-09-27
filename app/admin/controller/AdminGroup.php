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
use app\admin\BaseController;
use app\admin\model\AdminGroup as GroupModel;
/**
 * 管理员角色组管理
 */
class AdminGroup extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input  = input('post.');
            $search = ['keyword'];
            $append = ['disabled'];
            $order  = [$input['prop'] => $input['order']];
            $data   = GroupModel::withSearch($search, $input)->order($order)->append($append)->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
        } else {
            View::assign('menu', $this->request->menu);
            return View::fetch();
        }
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $input['admin_id'] = $this->request->userInfo->id;
            GroupModel::create($input);
            return json(['status' => 'success', 'message' => '新增成功']);
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            GroupModel::update(input('post.'));
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            GroupModel::destroy(input("post.ids"));
            return json(['status' => 'success', 'message' => '删除成功']);
        }
    }
}
