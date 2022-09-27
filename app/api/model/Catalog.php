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
namespace app\api\model;

use think\Model;

class Catalog extends Model
{
    // 设置json类型字段
    protected $json = ['field'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    // 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("title",'like', '%' . $value . '%');
        }
    }
    
    public function searchStatusAttr($query, $value, $array)
    {
        if ($value !== '') {
            $query->where("status", '=', $value);
        }
    }

    public function searchTypeAttr($query, $value, $array)
    {
        if ($value !== '') {
            $query->where("type", '=', $value);
        }
    }

    public function searchShowAttr($query, $value, $array)
    {
        if ($value !== '') {
            $query->where("show", '=', $value);
        }
    }

    // 获取器
    public function getGroupIdAttr($value, $array)
    {   
        return empty($value) ? [] : explode(',', $value);
    }
    
    public function getFieldAttr($value, $array)
    {
        $field = [];
        foreach ($value as $k => $v) {
            switch ($v['type']['is']) {
                case 'el-link-select':
                    $field[$v['field']] = Url::appoint($v['type']['value']);
                    break;
                case 'el-array':
                    $arr = $v['type']['value']['table'];
                    foreach ($arr as $key1 => $val1) {
                        foreach (array_keys($val1) as $key2 => $val2) {
                            if (is_array($val1[$val2])) {
                                $arr[$key1][$val2] = Url::appoint($val1[$val2]);
                            }
                        }
                    }
                    $field[$v['field']] = $arr;
                    break;
                default:
                    $field[$v['field']] = $v['type']['value'];
                    break;
            }
        }
        return $field;
    }
}