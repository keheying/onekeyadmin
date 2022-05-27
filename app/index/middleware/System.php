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
 * 系统变量检测
 */
class System
{
    public function handle($request, \Closure $next)
    {
        // 基础信息
        $system = Config::getVal('system_' . $request->lang);
        $system = $system ? $system : [
            'company'         => '', 
            'email'           => '', 
            'telephone'       => '', 
            'phone'           => '', 
            'fax'             => '',
            'wechat'          => '',
            'qq'              => '',
            'address'         => '',
            'business_hours'  => '',
            'ico'             => '',
            'logo'            => '',
            'copy_logo'       => '',
            'wechat_qrcode'   => '',
            'copyright'       => '',
            'seo_title'       => '',
            'seo_keywords'    => '',
            'seo_description' => '',
            'icp'             => ''
        ];
        // 主题信息
        $themeName   = theme();
        $themeConfig = Config::getVal("theme_" . $themeName . "_" . $request->lang);
        $themeConfigDefault = Themes::where('name', $themeName)->cache('themes_' . $themeName)->field('config')->find();
        $themeConfigDefault = $themeConfigDefault['config'];
        foreach ($themeConfigDefault as $k => $v) { 
            switch ($v['type']['is']) {
                case 'el-link-select':
                    $system[$v['field']] = isset($themeConfig[$v['field']]) ? Url::getLinkUrl($themeConfig[$v['field']]) : '';
                    break;
                case 'el-array':
                    $system[$v['field']] = isset($themeConfig[$v['field']]) ? mk_array($themeConfig[$v['field']]['table']) : '';
                    break;
                default:
                    $system[$v['field']] = isset($themeConfig[$v['field']]) ? $themeConfig[$v['field']] : $v['type']['value'];
                    break;
            }
        }
        $request->system = $system;
        // 会员配置
        $userConfig = [];
        foreach (Config::getVal("user") as $k => $v) {
            $userConfig[$v['field']] = $v['type']['value'];
        }
        $request->userConfig = $userConfig;
        // TDK信息
        $catalog = $request->catalog;
        $catalog['seo_title'] = empty($catalog['seo_title']) ? $system['seo_title'] : $catalog['seo_title'];
        $catalog['seo_keywords'] = empty($catalog['seo_keywords']) ? $system['seo_keywords'] : $catalog['seo_keywords'];
        $catalog['seo_description'] = empty($catalog['seo_description']) ? $system['seo_description'] : $catalog['seo_description'];
        $request->catalog = $catalog; 
        set_tdk($catalog);
        // 语言列表
        $langAllow = config('lang.lang_allow');
        foreach ($langAllow as $key => $val) {
            $langAllow[$key]['url'] = config('lang.default_lang') == $val['name'] ? '/' : '/'.$val['name'];
        }
        $request->langAllow = $langAllow; 
        $response = $next($request);
        if ($request->isGet()) {
            // 绑定内容
            $content = $response->getContent();
            preg_match("/<head>(.*)<\/head>/si",$content,$match);
            if (! empty($match)) {
                $bind = $this->header($request);
                $content = str_replace($match[1], $match[1].$bind, $content);
                $response->content($content);
            }
            // 记录登录跳转
            if ($request->route !== 'login') {
                cookie('index_last_url', url(str_replace('.html', '', $request->pathinfo())));
            }
            // 钩子
            event('System', $response);
        }
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
        // 搜索分类
        $searchCatalog = [];
        foreach (plugin_list() as $key => $value) {
            $searchCatalog = array_merge($searchCatalog, $value['route']);
        }
        $bind = '<script type="text/javascript">';
        $bind .= "\n\t".'function url(url, parameter = {}) {';
        $bind .= "\n\t\t".'let system = "'.url("decoration/your-link").'";';
        $bind .= "\n\t\t".'let link = system.replace("decoration/your-link", url);';
        $bind .= "\n\t\t".'for(key in parameter){';
        $bind .= "\n\t\t".'let srt = link.indexOf("?") == -1 ? "?" : "&"';
        $bind .= "\n\t\t".'link += srt + key + "=" + parameter[key]';
        $bind .= "\n\t\t".'}';
        $bind .= "\n\t\t".'return link';
        $bind .= "\n\t".'}';
        $bind .= "\n\t".'function lang(name) {';
        $bind .= "\n\t\t".'let langParameter = '.json_encode($langParameter,JSON_UNESCAPED_UNICODE).';';
        $bind .= "\n\t\t".'return typeof langParameter[name] === "undefined" || langParameter[name] === "" ? name : langParameter[name];';
        $bind .= "\n\t".'}';
        $bind .= "\n".'</script>';
        return $bind;
    }
}