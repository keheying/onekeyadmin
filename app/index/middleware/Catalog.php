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

use app\addons\Tree;
use app\index\addons\User;
use app\index\model\Catalog as CatalogModel;
/**
 * 分类检测
 */
class Catalog
{
    public function handle($request, \Closure $next)
    {
        // 是否安装
        if (! is_file(root_path().'.env')) return redirect(request()->root(true) . '/install/index/index');
        // 自动登录
        $request->userInfo = User::checkAutomaticLogin();
        // 路径信息
        $request->pathinfo = explode('/', str_replace('.html', '', $request->pathinfo()));
        // 语言标识
        $request->lang = in_array($request->pathinfo[0], array_column(config('lang.lang_allow'), 'name')) ? $request->pathinfo[0] : config('lang.default_lang');
        // 所有分类
        $catalogList = CatalogModel::withoutField('status,create_time,language')
        ->where('status', 1)
        ->where('theme', theme())
        ->where('language', $request->lang)
        ->order('sort','desc')
        ->cache('catalog_' . theme() . $request->lang)
        ->append(['url'])
        ->select()
        ->toArray();
        $request->catalogList = array_combine(array_column($catalogList, 'id'), $catalogList);
        // 首页分类
        $catalogIndexKey = array_search('index', array_column($catalogList, 'seo_url'));
        $request->catalogIndex = $catalogList[$catalogIndexKey];
        // 标识分类
        $catalogNum = [];
        foreach (array_unique(array_column($catalogList, 'num')) as $key => $val) {
            foreach ($catalogList as $k => $v) {
                if ($val === $v['num']) {
                    $catalogNum[$val][] = $v;
                }
            }
        }
        $request->catalogNum = $catalogNum;
        // 导航头部、底部
        $catalogHeader = [];
        $catalogFooter = [];
        foreach ($catalogList as $key => $val) {
            if ($request->isMobile() && $val['mobile'] === 0) continue;
                switch ($val['show']) {
                    case 1:
                        array_push($catalogHeader, $val);
                        array_push($catalogFooter, $val);
                        break;
                    case 2:
                        array_push($catalogHeader, $val);
                        break;
                    case 3:
                        array_push($catalogFooter, $val);
                        break;
                }
        }
        $header = new Tree($catalogHeader);
        $footer = new Tree($catalogHeader);
        $request->catalogHeader = $header->leaf(0); 
        $request->catalogFooter = $footer->leaf(0);
        // 当前分类信息
        $catalog = [];
        $route = isset($request->pathinfo[1]) ? $request->pathinfo[1] : '';
        $route = $request->lang === $request->pathinfo[0] ? $route : $request->pathinfo[0];
        $request->route = empty($route) ? 'index' : $route;
        $request->singleRouteLength = $request->lang === $request->pathinfo[0] ? 3 : 2;
        // 列表分类
        foreach ($catalogList as $key => $val) {
            if ($val['seo_url'] == $request->route || $val['id'] == $request->route) {
                $catalog = $val;
            }
        }
        // 自定义链接
        if (empty($catalog)) {
            foreach ($catalogList as $key => $val) {
                if ($val['links_type'] == 1 && $val['url'] == $request->domain() . '/' . $request->pathinfo()) {
                    $catalog = $val;
                }
            }
        }
        // 默认信息
        if (empty($catalog)) {
            $catalog = [
                'id'              => '',
                'pid'             => '0',
                'num'             => '',
                'level'           => 1,
                'group_id'        => [],
                'title'           => '',
                'cover'           => '',
                'content'         => '',
                'description'     => '',
                'field'           => [],
                'seo_url'         => '',
                'seo_title'       => '',
                'seo_keywords'    => '',
                'seo_description' => '',
                'sort'            => 0,
                'type'            => 'page',
                'show'            => 0,
                'status'          => 1,
                'language'        => $request->lang,
                'mobile'          => 1,
                'level1'          => '',
                'theme'           => theme(),
            ];
        }
        // 面包屑
        $catalogTree = new Tree($catalogList);
        $catalog['crumbs'] = $catalogTree->navi($catalog['id']);
        // 等级
        $i = 0;
        foreach ($catalog['crumbs'] as $key => $val) {
            $level = 'level' . ($key + 1);
            $catalog[$level] = $val['id'];
        }
        $request->catalog = $catalog;
        // 权限
        $authority = User::authorityCheck($catalog['group_id']);
        if ($authority !== true) {
            return $authority;
        }
        // 钩子
        event('Catalog', $request);
        return $next($request);
    }
}