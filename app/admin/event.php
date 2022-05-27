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
// 插件
$pluginListen = [
    // 开始检查
    'AppCheck'   => [],
    // 权限检查
    'AuthCheck'  => [],
    // 检查完毕
    'EndCheck'   => [],
    // 控制台 
    'Console'    => [],
    // 上传结束
    'UploadEnd'  => [],
    // 登录视图
    'LoginView'  => [],
    // 登录结束
    'LoginEnd'   => [],
    // 会员配置
    'UserConfig' => [],
];
foreach ($pluginListen as $handle => $listen) {
    foreach (plugin_list() as $key => $plugin) {
        $path = $plugin['name'] . '/admin/listen/' . $handle;
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