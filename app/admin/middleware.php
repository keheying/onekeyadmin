<?php
// +----------------------------------------------------------------------
// | 全局中间件定义文件
// +----------------------------------------------------------------------

return [
	// Session初始化
    \think\middleware\SessionInit::class,
    // 环境检测
    app\admin\middleware\AppCheck::class,
    // 权限检测
    app\admin\middleware\AuthCheck::class,
    // 配置检测
    app\admin\middleware\ConfigCheck::class,
    // 日志检测
    app\admin\middleware\LogCheck::class,
];
