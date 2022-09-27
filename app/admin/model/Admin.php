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

class Admin extends Model
{
    // 关联模型
    public function group()
    {
        return $this->hasOne(AdminGroup::class, 'id', 'group_id')->bind([
            'group_title' => 'title', 
            'group_role'  => 'role'
        ]);
    }

    // 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
    	if (! empty($value)) {
	        $query->where("nickname|email|account",'like', '%' . $value . '%');
	    }
    }

    public function searchStatusAttr($query, $value, $array)
    {
        if ($value !== '') {
            $query->where("status", '=', $value);
        }
    }

    // 获取器
    public function getPasswordAttr($value, $array)
    {
        return '';
    }
    
    public function getCStatusAttr($value, $array)
    {
        return $array['status'] === 1 ? '正常' : '屏蔽';
    }
    
    public function getDisabledAttr($value, $array)
    {
        if ($array['group_id'] === 1) {
            return true;
        } else {
            return $array['admin_id'] !== request()->userInfo->id && request()->userInfo->group_role !== '*';
        }
    }
    
    // 修改器
    public function setPasswordAttr($value, $array)
    {
        if (! empty($value)) {
            $password = password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]); 
            $this->set('password', $password);
        }
    }
}