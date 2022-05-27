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
use app\admin\model\UserLog as UserLogModel;
/**
 * 用户日志
 */
class UserLog extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $count = UserLogModel::withSearch(['id'], $input)->count();
            $data = UserLogModel::withSearch(['id'], $input)->with(['user'])->order('create_time', 'desc')->page($input['page'], $input['pageSize'])->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data, 'count' => $count]);
        } else {
            return View::fetch();
        }
    }
}
