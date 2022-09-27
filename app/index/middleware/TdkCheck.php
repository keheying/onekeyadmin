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

namespace app\index\middleware;

use think\facade\View;
/**
 * TDK检测
 */
class TdkCheck
{
    public function handle($request, \Closure $next)
    {
        if ($request->isGet()) {
            $seo_title = empty($request->catalog['seo_title']) ? $request->system['seo_title'] : $request->catalog['seo_title'];
            $seo_keywords = empty($request->catalog['seo_keywords']) ? $request->system['seo_keywords'] : $request->catalog['seo_keywords'];
            $seo_description = empty($request->catalog['seo_description']) ? $request->system['seo_description'] : $request->catalog['seo_description'];
            View::assign([
                'seo_title'       => $seo_title,
                'seo_keywords'    => $seo_keywords,
                'seo_description' => $seo_description,
            ]);
        }
        return $next($request);
    }
}