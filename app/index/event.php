<?php
// +----------------------------------------------------------------------
// | 事件
// +----------------------------------------------------------------------

// 系统
$systemListen = [
    'AppInit'     => [],
    'HttpRun'     => [],
    'HttpEnd'     => [],
    'LogWrite'    => [],
    'RouteLoaded' => [],
];

// 前台
$pluginListen = [
    // 分类检测
    'Catalog'       => [],
    // 系统检测
    'System'        => [],
    // 登录视图
    'LoginView'     => [],
    // 登录结束
    'LoginEnd'      => [],
    // 注册视图
    'RegisterView'  => [],
    // 注册结束
    'RegisterEnd'   => [],
    // 会员侧边
    'UserSite'      => [],
    // 会员中心
    'UserIndex'     => [],
    // 会员主页
    'UserPage'      => [],
    // 会员信息查询后
    'UserAfterRead' => [],
];
foreach ($pluginListen as $handle => $listen) {
    foreach (plugin_list() as $key => $plugin) {
        $path = $plugin['name'] . '/index/listen/' . $handle;
        if (is_file(plugin_path() . $path  . '.php')) {
            array_push($pluginListen[$handle], 'plugins\\' . str_replace('/', '\\', $path));
        }
    }
}
// 事件定义文件
$list = [
    // 绑定事件
    'bind'      => [],
    // 监听事件
    'listen'    => array_merge($systemListen, $pluginListen),
    // 订阅事件
    'subscribe' => [],
];
return $list;