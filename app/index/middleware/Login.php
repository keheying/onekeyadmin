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

use app\index\model\User;
/**
 * 登录检查
 */
class Login
{
    public function handle($request, \Closure $next)
    {
        if (empty($request->userInfo)) {
            return $request->isPost() ? json(['status'=>'login', 'message'=> lang('login status expired')]) : redirect(url('login/index'));
        }
        return $next($request);
    }
}