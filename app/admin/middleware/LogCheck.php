<?php 
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\admin\middleware;

use think\facade\Log;
use app\admin\model\Config;
use app\admin\model\AdminLog;
/**
 * 日志检测
 */
class LogCheck
{
    /**
     * 不需要检查的类
     * @var noVerification
     */
    protected $ignoreCheckClass = [
        'login'
    ];

    public function handle($request, \Closure $next)
    {
        // 写入日志
        if ($request->isPost()) {
            if (! in_array($request->class, $this->ignoreCheckClass)) {
                $operation = $request->menu[$request->authorityIndex];
                if (isset($operation['logwriting']) && $operation['logwriting'] == 1) {
                    if (env('app_demo') == 1) return json(['status' => 'info', 'message' => '演示不能修改数据哦~']);
                    $title  = $operation['title'];
                    $parentId = $operation['pid'];
                    $parentTitle = "";
                    if (! empty($parentId)) {
                        foreach ($request->menu as $key => $value) {
                            if ($parentId === $value['id']) {
                                $parentTitle = $value['title'];
                            }
                        }
                    }
                    AdminLog::create([
                        'admin_id'    => $request->userInfo->id,
                        'title'       => $title . $parentTitle,
                        'path'        => $request->authorityPath,
                        'ip'          => $request->ip(),
                        'post'        => input('post.'),
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        // 下一步
        return $next($request);
    }
}