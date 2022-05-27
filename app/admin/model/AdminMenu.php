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

class AdminMenu extends Model
{
    // 递归删除
    public static function recursiveDestroy($ids) {
        self::destroy($ids);
        $ids = self::whereIn('pid', $ids)->column('id');
        if ($ids) self::recursiveDestroy($ids);
    }
}