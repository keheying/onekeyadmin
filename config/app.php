<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------
return [
    // 应用地址
    'app_host'                => 'app',
    // 应用的命名空间
    'app_namespace'           => '',
    // 是否启用路由
    'with_route'              => true,
    // 是否启用事件
    'with_event'              => true,
    // 开启应用快速访问
    'app_express'             => true,
    // 默认应用
    'default_app'             => 'index',
    // 默认时区
    'default_timezone'        => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'                 => [env('map_admin') => 'admin'],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'             => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'           => ['addons'],
    // 异常页面的模板文件
    'exception_tmpl'          => app()->getThinkPath() . 'tpl/think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message'           => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'          => true,
    // 错误页面
    'http_exception_template' => [],
    // 请求地址
    'api' => 'https://www.onekeyadmin.com',
    // 版本号
    'version' => '105',
    // 当前主题
    'theme' => 'template',
];
