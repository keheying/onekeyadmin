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
declare (strict_types = 1);

namespace app\admin\addons;

use think\Image as thinkImage;
/**
 * 图片组件
 */
class Image
{
    /**
     * 生成缩略图
     * @param 图片连接
     * @param 生成宽度
     * @param 生成高度
     */
    public static function thumb(string $url, $width = 40, $height = 40)
    {
        $file = str_replace('\/', '/', public_path() . $url);
        $image = thinkImage::open($file);
        $fileName = pathinfo($url, PATHINFO_FILENAME);
        $thumbName = str_replace($fileName, $fileName.$width.'x'.$height, $file);
        $image->thumb($width, $height)->save($thumbName);
    }
}