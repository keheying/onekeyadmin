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

class User extends Model
{
    // 设置json类型字段
    protected $json = ['field'];

    protected $jsonAssoc = true;
    
    // 关联模型
    public function group()
    {
        return $this->hasOne(UserGroup::class, 'id', 'group_id')->bind([
            'group_title' => 'title'
        ]);;
    }

    // 搜索器
    public function setPasswordAttr($value, $array)
    {
        if (! empty($value)) {
            $password = password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]); 
            $this->set('password', $password);
        }
    }

    public function searchKeywordAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("nickname|email|mobile|account",'like', '%' . $value . '%');
        }
    }

    public function searchDateAttr($query, $value, $array)
    {
        if (! empty($value)) { 
            $query->whereBetweenTime('create_time', $value[0], $value[1]);
        }
    }

    public function searchStatusAttr($query, $value, $array)
    {
        if ($value !== '') {
            $query->where("status", '=', $value);
        }
    }

    // 获取器
    public function getUrlAttr($value, $array)
    {
        return request()->domain() . '/userpage?id=' . $array['id'];
    }

    public function getPasswordAttr($value)
    {
        return "";
    }

    public function getCStatusAttr($value, $array)
    {
        return $array['status'] === 1 ? '正常' : '屏蔽';
    }

    // 修改器
    public function setFieldAttr($value, $array)
    {
        $field = [];
        foreach ($value as $key => $val) {
            $field[$val['field']] = $val['value'];
        }
        return $field;
    }
}