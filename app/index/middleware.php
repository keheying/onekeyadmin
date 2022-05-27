<?php
// +----------------------------------------------------------------------
// | 全局中间件定义文件
// +----------------------------------------------------------------------

return [
    // Session初始化
    \think\middleware\SessionInit::class,
    // 分类检测
    app\index\middleware\Catalog::class,
    // 系统检测
    app\index\middleware\System::class,
    // 多语言加载
    \think\middleware\LoadLangPack::class,
];
