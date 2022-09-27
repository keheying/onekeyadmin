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
 * 配置检测
 */
class ConfigCheck
{
    public function handle($request, \Closure $next)
    {
        // 水印配置
        $request->watermark = Config::getVal('watermark');
        // 页面配置
        $response = $next($request);
        if ($request->isGet()) {
            $content = str_replace('<head>', '<head>' . $this->html($request), $response->getContent());
            $response->content($content);
            // 绑定事件
            event('HtmlCheck', $response);
        }
        // 下一步
        return $response;
    }

    /**
     * 头部渲染
     */
    public function html($request) 
    {
        $controller = empty($request->pluginPath) ? $request->class : $request->pluginName."/".$request->pluginClass;
        $html =  "\n".'<script type="text/javascript">';
        $html .= "\n\t".'var theme       = "'.theme().'";';
        $html .= "\n\t".'var controller  = "'.$controller.'";';
        $html .= "\n\t".'var watermark   = '.json_encode($request->watermark, JSON_UNESCAPED_UNICODE).';';
        $html .= "\n\t".'var userInfo    = '.json_encode($request->userInfo, JSON_UNESCAPED_UNICODE).';';
        $html .= "\n\t".'var authority   = '.json_encode($request->authorityList, JSON_UNESCAPED_UNICODE).';';
        $html .= "\n\t".'function index_url(url = "", parameter = {}) {';
        $html .= "\n\t\t".'let system = "'.index_url("controller/method").'";';
        $html .= "\n\t\t".'let home   = "'.index_url("").'";';
        $html .= "\n\t\t".'let link   = url == "" || url == "index" ? home : system.replace("controller/method",url);';
        $html .= "\n\t\t".'let param  = "";';
        $html .= "\n\t\t".'let index  = 0;';
        $html .= "\n\t\t".'for(key in parameter){';
        $html .= "\n\t\t\t".'str    = link.indexOf("?") == -1 && index == 0 ? "?" : "&";';
        $html .= "\n\t\t\t".'param += str + key + "=" + parameter[key];';
        $html .= "\n\t\t\t".'index++;';
        $html .= "\n\t\t".'}';
        $html .= "\n\t\t".'return link + param;';
        $html .= "\n\t".'}';
        $html .= "\n\t".'function admin_url(url = "", parameter = {}) {';
        $html .= "\n\t\t".'let system = "'.admin_url("controller/method").'";';
        $html .= "\n\t\t".'let home   = "'.admin_url("").'";';
        $html .= "\n\t\t".'if ((url.split("/")).length > 2) {';
        $html .= "\n\t\t".'parameter.path = url;';
        $html .= "\n\t\t".'url = "plugins";';
        $html .= "\n\t\t".'}';
        $html .= "\n\t\t".'let link   =  url == "" || url == "index" ? home : system.replace("controller/method", url);';
        $html .= "\n\t\t".'let param  = "";';
        $html .= "\n\t\t".'let index  = 0;';
        $html .= "\n\t\t".'for(key in parameter){';
        $html .= "\n\t\t\t".'str    = link.indexOf("?") == -1 && index == 0 ? "?" : "&";';
        $html .= "\n\t\t\t".'param += str + key + "=" + parameter[key];';
        $html .= "\n\t\t\t".'index++;';
        $html .= "\n\t\t".'}';
        $html .= "\n\t\t".'return link + param;';
        $html .= "\n\t".'}';
        $html .= "\n".'</script>';
        return $html;
    }
}