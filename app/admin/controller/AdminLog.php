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
use app\admin\model\AdminLog as AdminLogModel;
/**
 * AdminLog管理
 */
class AdminLog extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input  = input("post.");
            $search = ["keyword", "date"];
            $order  = [$input['prop'] => $input['order']];
            $count  = AdminLogModel::withSearch($search, $input)->count();
            $data   = AdminLogModel::withSearch($search, $input)->with(['admin'])->page($input["page"], $input["pageSize"])->order($order)->select();
            return json(["status" => "success", "message" => "请求成功", "data" => $data, "count" => $count]);
        } else {
            return View::fetch();
        }
    }
    
    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            AdminLogModel::destroy(input("post.ids"));
            return json(["status" => "success", "message" => "删除成功"]);
        }
    }
}