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

namespace app\api\middleware;

use app\api\model\User;
use app\api\model\UserToken;
/**
 * 用户鉴权（按需引入中间件）
 */
class AuthCheck
{
    public function handle($request, \Closure $next)
    {
        $time  = 14*24; // 后台控制token过期时间
        $input = input('post.');
        if (empty($input['token'])) {
            return json(['status'=>'login', 'message'=> 'TOKEN为空！']);
        }
        $id = UserToken::where("token", $input['token'])->whereTime("create_time","-$time hours")->value('user_id');
        if (! $id) {
            return json(['status'=>'login', 'message'=> 'TOKEN过期！']);
        }
        $password = User::where('id', $id)->value('password');
        if (! password_verify($id . $request->ip() . $password, $input['token'])) {
            return json(['status'=>'login', 'message'=> 'TOKEN验证错误！']);
        }
        $request->userInfo = User::with(['group'])->where('id', $id)->where('status', 1)->find();
        if (! $request->userInfo) {
            return json(['status'=>'login', 'message'=> '此账号被屏蔽！']);
        }
        // 下一步
        return $next($request);
    }
}