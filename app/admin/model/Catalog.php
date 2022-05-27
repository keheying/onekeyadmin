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
namespace app\admin\model;

use think\Model;
use app\index\addons\Url;

class Catalog extends Model
{
    // 设置json类型字段
    protected $json = ['links_value', 'field'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    // 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("title",'like', '%' . $value . '%');
        }
    }

    public function searchLanguageAttr($query, $value, $array)
    {
        $query->where("language", request()->lang);
    }
    
    public function searchStatusAttr($query, $value, $array)
    {
        if ($value !== '') {
            $query->where("status", '=', $value);
        }
    }

    // 获取器
    public function getDeleteDisabledAttr($value, $array)
    {
        return $array['seo_url'] == 'index';
    }

    public function getGroupIdAttr($value, $array)
    {
        return $value ? array_map('intval', explode(',', $value)) : [];
    }

    public function getUrlAttr($value, $array)
    {
        return $array['links_type'] === 1 ? Url::getLinkUrl($array['links_value'], false) : Url::getCatalogUrl($array, $array['language'], false);
    }

    public function getCTypeAttr($value, $array) 
    {
        if ($array['seo_url'] === 'index') {
            return '<span style="color:#06c">首页</span>';
        } else {
            $type[] = ['title' => '页面', 'catalog' => "page"];
            foreach (plugin_list() as $key => $value) {
                $type = array_merge($type, $value['route']);
            }
            foreach ($type as $key => $value) {
                if ($value['catalog'] === $array['type']) {
                    return $value['title'];
                }
            }
        }
    }

    public function getCShowAttr($value, $array) 
    {
        switch ($array['show']) {
            case 0:
                return '不显示';
                break;
            case 1:
                return '都显示';
                break;
            case 2:
                return '头部显示';
                break;
            case 3:
                return '底部显示';
                break;
        }
    }

    public function getCStatusAttr($value, $array)
    {
        return $array['status'] === 1 ? '正常' : '屏蔽';
    }

    // 修改器
    public function setGroupIdAttr($value, $array)
    {
        return implode(',', $value);
    }

    public function setSeoUrlAttr($value, $array)
    {
        return !empty($value) ? strtolower($value) : '';
    }

    // 自定义
    public static function recursiveDestroy($ids) {
        self::whereIn('id', $ids)->where('seo_url', '<>', 'index')->delete();
        $ids = self::whereIn('pid', $ids)->where('seo_url', '<>', 'index')->column('id');
        if ($ids) self::recursiveDestroy($ids); 
    }
}