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
use app\index\model\User;

class User extends Model
{
    // 设置json类型字段
    protected $json = ['field'];

    protected $jsonAssoc = true;
    
    public static function onAfterRead($user)
    {
        $userId = empty(request()->userInfo) ? 0 : request()->userInfo->id;
        $user->message_count = UserLog::where('to_id', $user->id)->where('type', 'message')->count();
        $user->visitor_count = UserLog::where('to_id', $user->id)->where('type', 'visitor')->count();
        $user->follow_count  = UserLog::where('user_id', $user->id)->where('type', 'fans')->count();
        $user->fans_count    = UserLog::where('to_id', $user->id)->where('type', 'fans')->count();
        $user->is_follow     = $user->id == $userId ? null : UserLog::where('user_id', $userId)->where('to_id', $user->id)->where('type', 'fans')->count();
        $user->url           = url('userpage', ['id' =>  $user->id]);
        // 钩子
        event('UserAfterRead', $user);
    }

    // 关联模型
    public function group()
    {
        return $this->hasOne(UserGroup::class, 'id', 'group_id')->bind([
            'group_title' => 'title'
        ]);
    }
    
    // 获取器
    public function getPasswordAttr($value, $array)
    {
        return '';
    }

    public function getPayPaaswordAttr($value, $array)
    {
        return '';
    }

    public function getEmailAttr($value, $array)
    {
        return ! empty($value) ? substr_replace($value,'****',3,4): '';
    }

    public function getMobileAttr($value, $array)
    {
        return ! empty($value) ? substr_replace($value,'****',3,4) : '';
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