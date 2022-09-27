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
 * 引入插件common.php文件
 */
foreach (plugin_list() as $k => $v) {
    $pluginCommonFile = plugin_path() . $v['name'] . '/admin/common.php';
    if (is_file($pluginCommonFile)) {
        include($pluginCommonFile);
    }
}