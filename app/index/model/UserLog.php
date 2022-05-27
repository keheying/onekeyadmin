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

class UserLog extends Model
{
    // 关联模型
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function to()
    {
        return $this->hasOne(User::class, 'id', 'to_id');
    }

	// 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
    	if (! empty($value)) {
	        $query->where("explain",'like', '%' . $value . '%');
	    }
    }

    public function searchDateAttr($query, $value, $array)
    {
    	if (! empty($value)) { 
    		$query->whereBetweenTime('create_time', $value[0], $value[1]);
	    }
    }
}