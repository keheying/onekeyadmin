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
/**
 * 插件列表
 * @param 状态
 */
function plugin_list($status = 1): array
{
    $pluginList = [];
    $pluginPath = plugin_path();
    if (is_dir($pluginPath)) {
        $handle = opendir($pluginPath);
        if ($handle) {
            while (($path = readdir($handle)) !== false) {
                if ($path != '.' && $path != '..') {
                    $nowPluginPath = $pluginPath . $path;
                    $nowPluginInfo = is_file($nowPluginPath . '/info.php') ? include($nowPluginPath . '/info.php') : [];
                    if ($nowPluginInfo) {
                        if ($nowPluginInfo['status'] == $status) {
                            $nowPluginInfo['route'] = is_file($nowPluginPath.'/route.php') ? include($nowPluginPath.'/route.php') : [];
                            array_push($pluginList, $nowPluginInfo);
                        }
                    }
                }
            }
        }
    }
    return array_sort($pluginList, 'sort');
}

/**
 * 字符串裁剪
 * @param 字符串
 * @param 起始位置
 * @param 结束位置
 */
function mk_substr($string = "", $start = 0, $length = 255): string
{
    $string = str_replace("&nbsp;",'',$string);
    return mb_substr(trim(strip_tags(htmlspecialchars_decode($string,ENT_QUOTES))), $start, $length, 'UTF-8');
}

/**
 * 二维数组根据某个字段排序
 * @param 要排序的数组
 * @param 要排序的键字段
 * @param 排序类型SORT_ASC/SORT_DESC 
 */
function array_sort(array $array, string $keys, $sort = "desc"): array
{
    $order     = $sort === 'asc' ? SORT_ASC : SORT_DESC;
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $order, $array);
    return $array;
}

/**
 * 生成不重复的字符串
 * @param 需要的长度
 */
function rand_id(int $length): string 
{
    $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
    $str = '';
    $arr_len = count($arr);
    for ($i = 0; $i < $length; $i++){
        $rand = mt_rand(0, $arr_len-1);
        $str.=$arr[$rand];
    }
    return $str;
}

/**
 * 时间秒转换为 00:00:00 格式
 * @param 秒
 */
function secto_time(int $times): string
{  
    $result = '00:00:00';  
    if ($times>0) {  
            $hour = floor($times/3600); 
            if($hour<10){
                $hour = "0".$hour;
            } 
            $minute = floor(($times-3600 * $hour)/60); 
            if($minute<10){
                $minute = "0".$minute;
            } 
            $second = floor((($times-3600 * $hour) - 60 * $minute) % 60); 
             if($second<10){
                $second = "0".$second;
            } 
            $result = $hour.':'.$minute.':'.$second;  
    }  
    return $result;  
}

/**
 * 插件位置
 */
function plugin_path(): string 
{
    return public_path() . 'plugins/';
}

/**
 * 当前主题
 */
function theme(): string
{
    if (empty(input('theme'))) {
        return empty(env('app_theme')) ? config('app.theme') : env('app_theme');
    } else {
        return input('theme');
    }
}

/**
 * 主题路径
 */
function theme_path(): string
{
    return public_path() . 'themes/';
}

/**
 * 当前主题路径
 */
function theme_now_path(): string
{
    return theme_path() . theme() . '/';
}

/**
 * 当前主题视图
 */
function theme_now_view(): string
{
    $path = theme_now_path();
    // 手机端
    if (is_dir($path . 'wap/') && request()->isMobile() == 1) {
        $path = $path . 'wap/';
    }
    return $path;
}

/**
 * api发起POST请求
 * @param 请求api方法
 * @param 请求api数据
 */
function api_post(string $func, $data = []): array
{
    $data['token'] = env('app_token');
    $url    = config('app.api').'/api/' . $func;
    $output = curl($url, $data);
    $result = json_decode($output, true);
    return is_array($result) ? $result : ['status' => 'error', 'message' => '连接错误'];
}

/**
 * CURL请求函数:支持POST及基本header头信息定义
 * @param 请求远程链接
 * @param 请求远程数据
 * @param 头信息数组
 * @param 来源url
 */
function curl(string $api_url, $post_data = [], $header = [], $referer_url = '')
{
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $api_url);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt( $ch, CURLOPT_TIMEOUT, 60);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt( $ch, CURLOPT_AUTOREFERER, 1);
    $header[] = "CLIENT-IP:".request()->ip();
    $header[] = "X-FORWARDED-FOR:".request()->ip();
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt( $ch, CURLOPT_ENCODING, "");
    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Baiduspider/2.0; +" . request()->domain() . ")" );
    curl_setopt( $ch, CURLOPT_REFERER, request()->domain());
    if($post_data && is_array($post_data)) {
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec( $ch );
    if (curl_errno($ch)) {
        return ['status' => 'error', 'message' => curl_error($ch)];
    } else {
        curl_close($ch);    
        return $data;
    }
}