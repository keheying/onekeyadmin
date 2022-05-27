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
use app\admin\model\User;

class UserLog extends Model
{
    // 关联模型
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function searchIdAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("user_id", $value);
        }
    }

    // 修改器
    public function getExplainAttr($value, $array)
    {
        $inc = $array['inc'] === 1 ? '+' : '-';
        $find = User::where('id', $array['to_id'])->find();
        $user = '';
        if ($find) {
            $user = empty($find['mobile']) ? $find['email'] : $find['mobile'];
        }
        $content = "";
        switch ($array['type']) {
            case 'balance':
                $content = '余额' . lang($array['explain']) . $inc . $array['number'];
                break;
            case 'integral':
                $content = '积分' . lang($array['explain']) . $inc . $array['number'];
                break;
            case 'visitor':
                $content = '访问了' . $user;
                break;
            case 'fans':
                $content = '关注了' . $user;
                break;
            case 'message':
                $content = '给' . $user . '留言：' . $array['explain'];
                break;
        }
        return $content;
    }
}