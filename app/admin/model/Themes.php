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

class Themes extends Model
{
    // 设置json类型字段
    protected $json = ['config'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
    
    // 搜索器
    public function searchNameAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("name", $value);
        }
    }

    // 获取器
    public function getTypeAttr($value, $array)
    {
        switch ($value) {
            case 1:
                return '响应式';
                break;
            case 2:
                return '手机';
                break;
            case 3:
                return '电脑';
                break;
        }
    }
}