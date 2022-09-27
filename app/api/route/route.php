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
use think\facade\Route;
// 变量规则
Route::pattern([
	"class"  => "[\w\-]+",
	"action" => "[\w\-]+",
]);
// 插件API
foreach (plugin_list() as $key => $val) {
	if (request()->path === $val['name']) {
		Route::rule($val['name']."/:class/:action", "plugins/index")->append(['plugin' => $val['name']]);
	}
}