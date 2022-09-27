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

class UserGroup extends Model
{
    // 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("title",'like', '%' . $value . '%');
        }
    }

    // 修改器
    public function getCDefaultAttr($value, $array)
    {
        return $array['default'] === 1 ? '是' : '否';
    }
}