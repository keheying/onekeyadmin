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

use app\index\addons\Url;
use app\index\model\Themes;
use app\index\model\Config;
/**
 * 配置检测
 */
class ConfigCheck
{
    public function handle($request, \Closure $next)
    {
        // 基础信息
        $system = Config::getVal('system');
        $label  = [];
        $theme  = Themes::where('name', theme())->cache('theme_' . theme())->field('config')->find();
        if (empty($theme)) {
            return abort(404);
        }
        foreach ($theme->config as $k => $v) { 
            $label[$v['field']] = $v['label']; 
            switch ($v['type']['is']) {
                case 'el-link-select':
                    $system[$v['field']] = Url::appoint($v['type']['value']);
                    break;
                case 'el-array':
                    foreach ($v['type']['value']['table'] as $key => $val) {
                        foreach (array_keys($val) as $keys => $vals) {
                            if (is_array($val[$vals])) {
                                $v['type']['value']['table'][$key][$vals] = Url::appoint($val[$vals]);
                            }
                        }
                    }
                    $system[$v['field']] = $v['type']['value']['table'];
                    break;
                default:
                    $system[$v['field']] = $v['type']['value'];
                    break;
            }
        }
        $request->system = $system;
        $request->label  = $label;
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
        $html =  "\n".'<script type="text/javascript">';
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
        $html .= "\n".'</script>';
        return $html;
    }
}