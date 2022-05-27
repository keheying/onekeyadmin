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
use app\admin\model\UserGroup as GroupModel;
/**
 * 用户角色管理
 */
class UserGroup extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $data  = GroupModel::withSearch(['keyword'], $input)->order($input['prop'], $input['order'])->append(['c_default','c_status'])->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
        } else {
            return View::fetch();
        }
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $save = GroupModel::create(input('post.'));
            if ($save->default === 1) {
                GroupModel::where('id', '<>', $save->id)->update(['default' => 0]);
            }
            return json(['status' => 'success', 'message' => '新增成功']);
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $default = GroupModel::where('id', $input['id'])->value('default');
            if ($input['default'] === 0 && $default === 1) {
                return json(['status' => 'error', 'message' => '可选择其它分组为注册默认，不能直接关闭']);
            }
            $save = GroupModel::update($input);
            if ($save->default === 1) {
                GroupModel::where('id', '<>', $save->id)->update(['default' => 0]);
            }
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }
    
    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            GroupModel::whereIn('id', input("post.ids"))->where('default', '<>', 1)->delete();
            return json(['status' => 'success', 'message' => '删除成功']);
        }
    }
}
