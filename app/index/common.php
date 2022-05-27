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

use think\facade\View;
use app\addons\Tree;
use app\index\addons\Url;
/**
 * 获取url
 * @param 链接
 */
function url($url = "", $parameter = []): string
{
    return Url::getUrl($url, '', $parameter);
}

/**
 * 获取上下页
 * @param 分类id
 */
function get_prev_next_page(Object $model, Object $single): Object
{
    $npWhere[] = ['status', '=', 1];
    $npWhere[] = ['language', '=', request()->lang];
    $npWhere[] = ['catalog_id', 'find in set', request()->catalog['id']];
    $field = 'id,catalog_id,title,seo_url';
    $single->prev = $model::where('sort','<',$single->sort)->field($field)->where($npWhere)->order('sort','desc')->find();
    $single->next = $model::where('sort','>',$single->sort)->field($field)->where($npWhere)->order('sort','asc')->find();
    if (empty($single->prev)) {
        $single->prev = $model::where('create_time','<',$single->create_time)->field($field)->where($npWhere)->order('create_time','desc')->find();
    }
    if (empty($single->prev)) {
        $single->prev = $model::where('id','<',$single->id)->field($field)->where($npWhere)->order('id','desc')->find();
    }
    if (empty($single->next)) {
        $single->next = $model::where('create_time','>',$single->create_time)->field($field)->where($npWhere)->order('create_time','asc')->find();
    }
    if (empty($single->next)) {
        $single->next = $model::where('id','>',$single->id)->field($field)->where($npWhere)->order('id','asc')->find();
    }
    return $single;
}

/**
 * 获取子分类
 * @param 分类id
 */
function get_catalog_child(int $id): array
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
function get_catalog_level(int $level, $type = ''): array
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
 * 设置TDK
 * @param 详情
 */
function set_tdk(array $single) 
{
    $tdk = [];
    $catalog = request()->catalog;
    $tdk['seo_title'] = empty($single['seo_title']) ? $catalog['seo_title'] : $single['seo_title'];
    $tdk['seo_keywords'] = empty($single['seo_keywords']) ? $catalog['seo_keywords'] : $single['seo_keywords'];
    $tdk['seo_description'] = empty($single['seo_description']) ? $catalog['seo_description'] : $single['seo_description'];
    View::assign($tdk);
}

/**
 * 设置自定义数组
 * @param 数组
 */
function mk_array($arr)
{
    foreach ($arr as $key => $val) {
        foreach (array_keys($val) as $k => $v) {
            if (is_array($val[$v])) {
                $arr[$key][$v] = Url::getLinkUrl($val[$v]);
            }
        }
    }
    return $arr;
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