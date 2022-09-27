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

use onekey\Tree;
/**
 * 获取子分类
 * @param 分类id
 */
function catalog_child(int $id): array
{
    $tree = new Tree(request()->catalogList);
    $catalog = $tree->leaf($id);
    return $catalog;
}

/**
 * 获取等级分类
 * @param 等级
 * @param 类型
 */
function catalog_level(int $level, $type = ''): array
{
    $catalog = [];
    foreach (request()->catalogList as $key => $val) {
        if ($level === $val['level']) {
            if ($type === $val['type'] || empty($type)) {
                array_push($catalog, $val);
            }
        }
    }
    return $catalog;
}

/**
 * 引入插件common.php文件
 */
foreach (plugin_list() as $k => $v) {
    $pluginCommonFile = plugin_path() . $v['name'] . '/index/common.php';
    if (is_file($pluginCommonFile)) {
        include($pluginCommonFile);
    }
}