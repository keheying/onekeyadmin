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

class AdminLog extends Model
{
    // 关联模型
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id')->bind([
            'admin_nickname' => 'nickname',
        ]);
    }

    // 搜索器
    public function searchKeywordAttr($query, $value)
    {
    	if (! empty($value)) {
	        $query->where("title|path|ip|post","like", "%" . $value . "%");
	    }
    }

    public function searchDateAttr($query, $value)
    {
        if (! empty($value)) {
            $query->where("create_time", 'between', $value);
        }
    }

    public function searchIdAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("admin_id", $value);
        }
    }

    // 获取器
    public function getLanguageAttr($value, $array)
    {
        return lang($value);
    }

    // 修改器
    public function setPostAttr($array, $value)
    {
    	return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}