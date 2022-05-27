<?php 
return [
	// 错误页面
    'http_exception_template' => [
    	404 => \think\facade\App::getAppPath() . '404.html',
		403 => \think\facade\App::getAppPath() . '403.html',
    ],
];