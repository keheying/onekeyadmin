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
/**
 * 控制台
 */
class Console extends BaseController
{
    public function index()
    {
    	// 钩子
    	$object = (object)[];
    	$object->html = '';
    	event('Console', $object);
    	View::assign([
    		'html'   => $object->html,
    		'domain' => $this->request->domain()
    	]);
        return View::fetch();
    }
}
