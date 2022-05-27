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
declare (strict_types = 1);
/**
 * 获取url
 * @param 链接
 */
function url($url = "", $paramArr = []): string
{
    $lang = empty(input('lang')) ? config('lang.default_lang') : input('lang');
    $admin = request()->root(true) . '/' . env('map_admin');
    if ($url === 'index/index' || empty($url)) {
        return $admin;
    } else {
        $system = "$admin/decoration/your-link";;
        $plugin = "$admin/plugins?path=plugin_path";
        $splUrl = count(explode('/', $url)) <= 2 ? str_replace("decoration/your-link", $url, $system) : str_replace("plugin_path", $url, $plugin);
        $param  =  '';
        if (! empty($paramArr)) {
            foreach ($paramArr as $key => $val) {
                $param .= '&' . $key . '=' . $val;
            }
        }
        return $splUrl . "?lang=" . $lang . $param;
    }
}
/**
 * 引入插件common.php文件
 */
foreach (plugin_list() as $k => $v) {
    $pluginCommonFile = plugin_path() . $v['name'] . '/admin/common.php';
    if (is_file($pluginCommonFile)) {
        include($pluginCommonFile);
    }
}