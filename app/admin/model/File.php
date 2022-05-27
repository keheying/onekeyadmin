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

class File extends Model
{
    // 搜索器
    public function searchKeywordAttr($query, $value, $array)
    {
    	if (! empty($value)) {
	        $query->where("title",'like', '%' . $value . '%');
	    }
    }

    public function searchTypeAttr($query, $value, $array)
    {
        if ($array['type'] !== '' && $array['type'] !== 'all' && $array['type'] !== 'recycle') {
            $query->where("type", $value);
        }
        $status = $array['type'] === 'recycle' ? 0 : 1;
        $query->where("status", $status);
    }
}