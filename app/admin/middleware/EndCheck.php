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
 * 检查结束
 */
class EndCheck
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
        // 当前语言
        $request->lang = empty(input('lang')) ? config('lang.default_lang') : input('lang');
        // 写入日志
        if ($request->isPost()) {
            if (! in_array($request->class, $this->ignoreCheckClass)) {
                $operation = $request->menu[$request->authorityIndex];
                if (isset($operation['logwriting']) && $operation['logwriting'] == 1) {
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
                        'language'    => $request->lang,
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        // 水印设置
        $request->watermark = Config::getVal('watermark');
        // 绑定内容
        $response = $next($request);
        $content  = $response->getContent();
        if ($request->isGet()) {
            preg_match("/<head>(.*)<\/head>/si",$content,$match);
            if (! empty($match)) $content = str_replace($match[1], $this->header($request).$match[1], $content);
        }
        $response->content($content);
        // 钩子
        event('EndCheck', $response);
        return $response;
    }

    /**
     * 头部渲染
     */
    public function header($request) 
    {
        $config = config('lang');
        $langParameter = [];
        if ($config['extend_list']) {
            if (isset($config['extend_list'][$request->lang])) {
                foreach ($config['extend_list'][$request->lang] as $key => $val) {
                    $parameter = is_file($val) ? include($val) : [];
                    $langParameter = array_merge($langParameter, $parameter);
                }
            }
        }
        $admin = request()->root(true) .'/'. env('map_admin');
        $controller = empty($request->pluginPath) ? $request->class : $request->pluginName.'/'.$request->pluginClass;
        $bind = "\n".'<script type="text/javascript">';
        $bind .= "\n\t".'var domain      = "'.$request->domain().'";';
        $bind .= "\n\t".'var theme       = "'.theme().'";';
        $bind .= "\n\t".'var language    = "'.$request->lang.'";';
        $bind .= "\n\t".'var controller  = "'.$controller.'";';
        $bind .= "\n\t".'var watermark   = '.json_encode($request->watermark, JSON_UNESCAPED_UNICODE).';';
        $bind .= "\n\t".'var userInfo    = '.json_encode($request->userInfo, JSON_UNESCAPED_UNICODE).';';
        $bind .= "\n\t".'var authority   = '.json_encode($request->authorityList, JSON_UNESCAPED_UNICODE).';';
        $bind .= "\n\t".'var langAllow   = '.json_encode(config('lang.lang_allow'), JSON_UNESCAPED_UNICODE).';';
        $bind .= "\n\t".'var langDefault = '.json_encode(config('lang.default_lang'), JSON_UNESCAPED_UNICODE).';';
        $bind .= "\n\t".'function url(url, parameter = {}) {';
        $bind .= "\n\t\t".'let system = "'.$admin.'/decoration/your-link";';
        $bind .= "\n\t\t".'let plugin = "'.$admin.'/plugins?path=plugin_path";';
        $bind .= "\n\t\t".'let splUrl = url.split("/").length <= 2 ? system.replace("decoration/your-link",url) : plugin.replace("plugin_path",url);';
        $bind .= "\n\t\t".'let param  = ""';
        $bind .= "\n\t\t".'for(key in parameter){';
        $bind .= "\n\t\t".'param += "&" + key + "=" + parameter[key]';
        $bind .= "\n\t\t".'}';
        $bind .= "\n\t\t".'return splUrl + "?lang=" + "'.$request->lang.'" + param;';
        $bind .= "\n\t".'}';
        $bind .= "\n\t".'function lang(name) {';
        $bind .= "\n\t\t".'let langParameter = '.json_encode($langParameter,JSON_UNESCAPED_UNICODE).';';
        $bind .= "\n\t\t".'return typeof langParameter[name] === "undefined" || langParameter[name] === "" ? name : langParameter[name];';
        $bind .= "\n\t".'}';
        $bind .= "\n".'</script>';
        return $bind;
    }
}