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
namespace app\index\model;

use think\Model;
use app\index\addons\Url;

class Catalog extends Model
{
    // 设置json类型字段
    protected $json = ['field'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
    
    // 获取器
    public function getGroupIdAttr($value, $array)
    {   
        return empty($value) ? [] : explode(',', $value);
    }
    
    public function getUrlAttr($value, $array)
    {
        return $array['links_type'] === 1 ? Url::getLinkUrl(json_decode($array['links_value'] ,true)) : Url::getCatalogUrl($array);
    }
    
    public function getFieldAttr($value, $array)
    {
        $field = [];
        foreach ($value as $k => $v) {
            switch ($v['type']['is']) {
                case 'el-link-select':
                    $field[$v['field']] = Url::getLinkUrl($v['type']['value']);
                    break;
                case 'el-array':
                    $field[$v['field']] = mk_array($v['type']['value']['table']);
                    break;
                default:
                    $field[$v['field']] = $v['type']['value'];
                    break;
            }
        }
        return $field;
    }
}