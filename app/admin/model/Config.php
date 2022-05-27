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
use think\exception\ValidateException;
use app\admin\validate\Config as ConfigValidate;

class Config extends Model
{
    // 搜索器
    public function searchLanguageAttr($query, $value, $array)
    {
        $language = request()->lang;
        $query->where("language", $language);
    }
    
    public function searchNameAttr($query, $value, $array)
    {
        if (! empty($value)) {
            $query->where("name", $value);
        }
    }

    /**
     * 配置设置
     */
    public static function setVal($name, $title, $value)
    {
        try {
            $value = json_encode($value,JSON_UNESCAPED_UNICODE);
            validate(ConfigValidate::class)->check(['value' => $value]);
            $find = self::where('name', $name)->find();
            if ($find) {
                $find->value = $value;
                $find->save();
            } else {
                self::create(['name'  => $name, 'title' => $title, 'value' => $value]);
            }
            cache($name, NULL);
            return ['status' => 'success', 'message' => '保存成功'];
        } catch ( ValidateException $e ) {
            return ['status' => 'error', 'message' => $e->getError()];
        }
    }

    /**
     * 配置获取
     */
    public static function getVal($name)
    {
        $config = self::where('name', $name)->cache($name)->find();
        return $config ? json_decode($config->value, true) : [];
    }
}