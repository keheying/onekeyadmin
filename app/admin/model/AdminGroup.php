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

class AdminGroup extends Model
{
    // 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("title",'like', '%' . $value . '%');
        }
    }

    // 获取器
    public function getDisabledAttr($value, $array)
    {
        if ($array['role'] == '*') {
            return true;
        } else {
            return $array['admin_id'] !== request()->userInfo->id && request()->userInfo->group_role !== '*';  
        }
    }

    public function getRoleAttr($value, $array)
    {
        return $value === '*' ? $value : explode(',', $value);
    }

    public function getCStatusAttr($value, $array)
    {
        return $array['status'] === 1 ? '正常' : '屏蔽';
    }

    // 修改器
    public function setRoleAttr($value, $array)
    {
        return $value === '*' ? $value : implode(',', $value);
    }
}