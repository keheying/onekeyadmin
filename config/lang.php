<?php
// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

// 语言列表
$langList = is_file(config_path().'langList.php') ? include(config_path().'langList.php') : [];
$info['default'] = "cn";
$info['allow']   = [];
foreach ($langList as $key => $val) {
    if ($val['default'] === 1) $info['default'] = $val['name'];
    if ($val['status'] === 1) array_push($info['allow'], $val); 
}
// 插件扩展包
$extend_list = [];
foreach ($info['allow'] as $key => $val) {
    $array   = [];
    $array[] = root_path() . 'lang/' . $val['name'] . ".php";
    foreach (plugin_list() as $key => $plugin) {
        $file = plugin_path() . $plugin['name'] . '/lang/' .  $val['name'] . ".php";
        if (is_file($file)) {
            $array[] = $file;
        }
    }
    $extend_list[$val['name']] = $array;
}
return [
    // 语言列表
    'lang_list'    => $langList,
    // 允许语言
    'lang_allow'   => $info['allow'],
    // 默认语言
    'default_lang' => $info['default'],
    // 扩展语言包
    'extend_list'  => $extend_list,
];