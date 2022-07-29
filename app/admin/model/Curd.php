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

class Curd extends Model
{
    // 设置json类型字段
    protected $json = ['field','search_status','search_catalog'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    // 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("title|name|plugin",'like', '%' . $value . '%');
        }
    }
}