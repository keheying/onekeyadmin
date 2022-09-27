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

namespace app\api\controller;

use app\api\BaseController;
use app\api\model\Catalog as CatalogModel;
/**
 * 分类信息
 */
class Catalog extends BaseController
{
	/**
	 * 列表
	 */
	public function index() 
	{
		if ($this->request->isPost()) {
			$input  = input('post.');
			$search = ['keyword','status','type','show'];
	        $append = ['url'];
	        $data   = CatalogModel::withSearch($search, $input)->append($append)->order('sort','desc')->select();
	        return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
	    }
	}

	/**
	 * 详情
	 */
	public function single() 
	{
		if ($this->request->isPost()) {
			$input = input('post.');
	        $data = CatalogModel::find($input['id']);
	        if (! $data) {
                return json(['status'=>'error', 'message'=> '分类信息不存在']);
            }
	        // 权限
            if (! empty($data->group_id)) {
                if (empty($this->request->userInfo)) return json(['status' => 'error','message' => '访问权限不足，登录后再试']);
                if (! in_array($this->request->userInfo->group_id, $data->group_id)) return json(['status' => 'error','message' => '访问权限不足']);
            }
	        return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
	    }
	}
}