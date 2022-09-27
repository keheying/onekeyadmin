<?php
// +----------------------------------------------------------------------
// | 事件
// +----------------------------------------------------------------------
$listen = [
    'AppInit'     => [],
    'HttpRun'     => [],
    'HttpEnd'     => [],
    'LogWrite'    => [],
    'RouteLoaded' => [],
    // 中间件环境检测
    'AppCheck'    => [],
    // 用户注册结束
    'RegisterEnd' => [],
];
foreach ($listen as $handle => $value) {
    foreach (plugin_list() as $key => $plugin) {
        $path = $plugin['name'] . '/api/listen/' . $handle;
        if (is_file(plugin_path() . $path  . '.php')) {
            array_push($listen[$handle], 'plugins\\' . str_replace('/', '\\', $path));
        }
    }
}
// 事件定义文件
$list = [
    // 绑定事件
    'bind'      => [],
    // 监听事件
    'listen'    => $listen,
    // 订阅事件
    'subscribe' => [],
];
return $list;