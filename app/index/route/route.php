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
	"param"  => "[\w\-]+",
	"name"   => "[\w\-]+",
	"class"  => "[\w\-]+",
	"action" => "[\w\-]+",
]);
// 主页页面
Route::rule("/", "page/index");
// 主页页面
Route::rule("search", "page/search");
// 插件页面
foreach (plugin_list() as $key => $val) {
	if (request()->path === $val['name']) {
		Route::rule($val['name']."/:class/:action", "plugins/index")->append(['plugin' => $val['name']]);
	}
}
// 分类页面
$catalog = request()->catalog;
if (! empty($catalog)) {
	switch ($catalog["type"]) {
		case "page":
			// 页面类型
			Route::rule($catalog['route'], "page/index");
			break;
		default:
			// 插件名
			$append["class"] = $catalog["type"];
			foreach (plugin_list() as $kk => $vv) {
				foreach ($vv["route"] as $k => $v) {
					if ($catalog["type"] === $v["catalog"]) $append["plugin"] = $vv["name"];
				}
			}
			// 详情
			if (request()->singleRoute) {
				$append["action"] = "single";
				Route::rule($catalog['route']."/:param", "plugins/index")->append($append);
			}
			// 列表
			if (request()->catalogRoute || request()->PageRoute) {
				$append["action"] = "list";
				Route::rule($catalog['route'], "plugins/index")->append($append);
			}
			break;
	}
}
