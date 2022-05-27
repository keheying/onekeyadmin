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

namespace app\index\addons;
/**
 * 链接组件
 */
class Url
{
    /**
     * 获取url
     * @param 链接
     * @param 语言
     */
    public static function getUrl($url, $lang = '', $paramArr = [], $theme = true): string
    {
        $domain = request()->domain();
        $langx  = $lang === '' ? request()->lang : $lang;
        $langx  = config('lang.default_lang') == $langx ? '/' : '/' . $langx . '/';
        $suffix = empty(config('view.view_suffix')) ? '' : '.' . config('view.view_suffix');
        $param  = '';
        if ($theme && ! empty(input('theme'))) {
            $param .= '?theme=' . theme();
        }
        if (! empty($paramArr)) {
            foreach ($paramArr as $key => $val) {
                $str = empty($param) ? '?' : '&';
                $param .= $str . $key . '=' . $val;
            }
        }
        return empty($url) || $url == 'index' ? $domain . $langx . $param : $domain . $langx . $url . $suffix . $param; 
    }

    /**
     * 获取link的url
     * @param 链接数组
     */
    public static function getLinkUrl($link, $theme = true): string
    {
        $url = "";
        if (! empty($link)) {
            switch ($link['type']) {
                case '1':
                    if (strstr($link['value'][1]['url'], 'http') === false) {
                        $url = self::getUrl($link['value'][1]['url'], '', [], $theme);
                    } else {
                        $url = $link['value'][1]['url'];
                    }
                    break;
                case '2':
                    $url = self::getSingleUrl($link['value'][2]['details'], $theme);
                    break;
                case '3':
                    $id = $link['value'][3]['catalog']['id'];
                    if (! empty(request()->catalogList)) {
                        $catalog = [];
                        foreach (request()->catalogList as $key => $value) {
                            if ($value['id'] == $id) $catalog = $value;
                        }
                    } else {
                        // 只有获取分类时，才会重新加载
                        $catalog = \app\index\model\Catalog::where('id', $id)->find();
                        $catalog = $catalog ? $catalog->toArray() : [];
                    }
                    if (! empty($catalog)) {
                        if ($catalog['links_type'] === 1) {
                            $url = self::getLinkUrl(json_decode($catalog['links_value'],true), $theme);
                        } else {
                            $anchor = empty($link['value'][3]['anchor']) ? '' : $link['value'][3]['anchor'];
                            $url = self::getCatalogUrl($catalog, $theme).$anchor;
                        }
                    }
                    break;
            }
        }
        return $url;
    }

    /**
     * 获取分类url(包含系统页)例：news、product
     * @param 分类详情
     * @param 语言
     */
    public static function getCatalogUrl(array $catalog, $lang = '', $theme = true): string
    {
        if (! empty($catalog)) {
            $param = empty($catalog['seo_url']) ? $catalog['id'] : $catalog['seo_url'];
            return self::getUrl($param, $lang, [], $theme);
        } else {
            return '';
        }
    }

    /**
     * 获取单页url例：news/1
     * @param 单页详情
     * @param 分类标识
     * @param 语言
     */
    public static function getSingleUrl(array $details, $lang = '', $theme = true): string
    {
        $id = explode(',', $details['catalog_id'])[0];
        if (! empty(request()->catalogList)) {
            $catalog = [];
            foreach (request()->catalogList as $key => $value) {
                if ($value['id'] == $id) $catalog = $value;
            }
        } else {
            // 只有获取分类时，才会重新加载
            $catalog = \app\index\model\Catalog::where('id', $id)->find();
        }
        if (! empty($catalog)) {
            $name  = empty($catalog['seo_url']) ? $catalog['id'] : $catalog['seo_url'];
            $param = empty($details['seo_url']) ? $details['id'] : $details['seo_url'];
            return self::getUrl($name .'/'. $param, $lang, [], $theme);
        } else {
            return '';
        }
    }

    /**
     * 获取分页url例：product/page/1
     * @param 分页
     */
    public static function getCatalogPageUrl(int $page): string
    {
        $input = input();
        $param = empty($input['keyword']) ? [] : ['keyword' => $input['keyword']];
        return self::getUrl(request()->route .'/page/'. $page, "", $param);
    }
}