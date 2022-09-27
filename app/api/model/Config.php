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
namespace app\api\model;

use think\Model;

class Config extends Model
{
    // 设置json类型字段
    protected $json = ['value'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 配置获取
     */
    public static function getVal($name)
    {
        $config = self::where('name', $name)->cache($name)->find();
        return $config ? $config->value : [];
    }
}