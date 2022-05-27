<?php
// +----------------------------------------------------------------------
// | 全局中间件定义文件
// +----------------------------------------------------------------------

return [
    // Session初始化
    \think\middleware\SessionInit::class,
    // 环境检查
    app\admin\middleware\AppCheck::class,
    // 权限检查
    app\admin\middleware\AuthCheck::class,
    // 检查完成
    app\admin\middleware\EndCheck::class,
    // 多语言加载
    \think\middleware\LoadLangPack::class,
];
