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
// 规则
Route::pattern([
	"param"  => "[\w\-]+",
	"name"   => "[\w\-]+",
	"id"     => "\d+",
]);
// 当前语言
$lang = request()->lang;
$pathinfo = request()->pathinfo;
// 搜索页面
Route::rule("$lang/search", "page/search");
Route::rule("search", "page/search");
// 他人页面
Route::rule("$lang/userpage", "page/userpage");
Route::rule("userpage", "page/userpage");
Route::rule("$lang/visitorPage", "page/visitorPage");
Route::rule("visitorPage", "page/visitorPage");
Route::rule("$lang/fansPage", "page/fansPage");
Route::rule("fansPage", "page/fansPage");
Route::rule("$lang/messagePage", "page/messagePage");
Route::rule("messagePage", "page/messagePage");
// 登录/注册
Route::rule("$lang/login/:name", "login/:name");
Route::rule("login/:name", "login/:name");
// 会员中心
Route::rule("$lang/user/:name", "user/:name")->middleware([\app\index\middleware\Login::class]);
Route::rule("user/:name", "user/:name")->middleware([\app\index\middleware\Login::class]);
// 插件页面
foreach (plugin_list() as $key => $val) {
	// 路由(插件名/类/方法)
	$plugin = request()->route;
	if ($plugin === $val['name']) {
		$append = ['plugin' => $plugin];
		$class  = $lang === $pathinfo[0] ? 2 : 1;
		$class  = isset($pathinfo[$class]) ? $pathinfo[$class] : '';
		$middleware = $class === 'user' ? [\app\index\middleware\Login::class] : [];
		if ($class !== 'page') {
        	Route::rule("$lang/$plugin/:class/:action", "plugins/index")->middleware($middleware)->append($append);
        	Route::rule("$plugin/:class/:action", "plugins/index")->middleware($middleware)->append($append);
		}
	}
}
// 分类页面
$catalog = request()->catalog;
// seo_url优先
$name = empty($catalog["seo_url"]) ? $catalog["id"] : $catalog["seo_url"];
$type = $catalog["type"];
switch ($type) {
	case "page":
		// 页面类型 
		Route::rule("$lang/$name", "page/index");
		Route::rule("$name", "page/index");
		break;
	default:
		// 插件名
		$append["class"] = $type;
		foreach (plugin_list() as $kk => $vv) {
			foreach ($vv["route"] as $k => $v) {
				if ($type === $v["catalog"]) $append["plugin"] = $vv["name"];
			}
		}
		// 详情/列表类型
		if (count($pathinfo) === request()->singleRouteLength) {
			$append["action"] = "single";
			Route::rule("$lang/$name/:param", "plugins/index")->append($append);
			Route::rule("$name/:param", "plugins/index")->append($append);
		} else {
			$append["action"] = "catalog";
			Route::rule("$lang/$name", "plugins/index")->append($append);
			Route::rule("$name", "plugins/index")->append($append);
		}
		break;
}
// 首页(优先级问题，防止覆盖)
Route::rule("/", "page/index");
Route::rule("/$lang", "page/index");