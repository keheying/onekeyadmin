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
namespace app\admin\controller;

use onekey\File;
use think\facade\View;
use app\admin\BaseController;
use app\admin\model\UserGroup;
use app\admin\model\Catalog as CatalogModel;
/**
 * 分类管理
 */
class Catalog extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input  = input('post.');
            $search = ['keyword','status'];
            $append = ['c_show','c_type','delete_disabled','url'];
            $order  = [$input['prop'] => $input['order']];
            $data   = CatalogModel::withSearch($search, $input)->where('theme', theme())->append($append)->order($order)->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
        } else {
            $type[] = ['title' => '页面', 'catalog' => "page"];
            foreach (plugin_list() as $key => $value) {
                $type = array_merge($type, $value['route']);
            }
            $group = UserGroup::where('status', 1)->order('integral', 'asc')->select();
            View::assign([ 'type' => $type, 'group' => $group ]);
            return View::fetch();
        }
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            // 检测路由
            if (! empty($input['seo_url']) && $input['links_type'] === 0) {
                $input['seo_url']  = strtolower(str_replace(' ', '-', trim($input['seo_url'])));
                $where[] = ['seo_url', '=', $input['seo_url']];
                $where[] = ['theme', '=', theme()];
                $where[] = ['links_type', '=', 0];
                $isExist = CatalogModel::where($where)->value('id');
                if ($isExist) {
                    return json(['status' => 'error', 'message' => '自定义地址已经存在，不可重复']);
                }
            }
            // 创建模板
            if ($input['type'] === 'page' && $input['links_type'] === 0) {
                if (empty($input['seo_url'])) {
                    return json(['status' => 'error', 'message' => '页面类型必须请输入路由！']);
                }
                $file = theme_now_path() . str_replace('-', '_', $input['seo_url']) . '.html';
                if (! is_file($file)) {
                    File::create($file, '{$catalog.content|raw}');
                }
            }
            // 绑定模板
            if ($input['type'] !== 'page' && $input['links_type'] === 0 && $input['bind_html'] !== '') {
                $singleFile = theme_now_path() .'single/'. str_replace('-', '_', $input['bind_html']) . '.html';
                $catalogFile = theme_now_path() .'list/'. str_replace('-', '_', $input['bind_html']) . '.html';
                if (! is_file($singleFile)) File::create($singleFile);
                if (! is_file($catalogFile)) File::create($catalogFile);
            }
            $input['level']    = $input['pid'] === 0 ? 1 : CatalogModel::where('id', $input['pid'])->value('level') + 1;
            $input['theme']    = theme();
            CatalogModel::create($input);
            // 清除缓存
            cache('catalog_' . theme(), NULL);
            return json(['status' => 'success', 'message' => '新增成功']);
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            // 检测路由
            if (! empty($input['seo_url']) && $input['links_type'] === 0) {
                $input['seo_url'] = strtolower(str_replace(' ', '-', trim($input['seo_url'])));
                $where[] = ['seo_url', '=', $input['seo_url']];
                $where[] = ['id', '<>', $input['id']];
                $where[] = ['theme', '=', theme()];
                $where[] = ['links_type', '=', 0];
                $isExist = CatalogModel::where($where)->value('id');
                if ($isExist) {
                    return json(['status' => 'error', 'message' => '自定义地址已经存在，不可重复']);
                }
            }
            $old = CatalogModel::where('id', $input['id'])->value('seo_url');
            if ($old === 'index') {
                if ($input['seo_url'] !== 'index') {
                    return json(['status' => 'error', 'message' => '首页路由是固定的，不能修改！']);
                }
            }
            // 修改模板
            if ($input['type'] === 'page' && $input['links_type'] === 0) {
                if (empty($input['seo_url'])) {
                    return json(['status' => 'error', 'message' => '页面类型必须请输入路由！']);
                }
                $file = theme_now_path() . str_replace('-', '_', $input['seo_url']) . '.html';
                $oldfile = theme_now_path() . str_replace('-', '_', $old) . '.html';
                is_file($oldfile) ? rename($oldfile, $file) : File::create($file, '{$catalog.content|raw}');
            }
            // 绑定模板
            if ($input['type'] !== 'page' && $input['links_type'] === 0 && $input['bind_html'] !== "") {
                $singleFile = theme_now_path() .'single/'. str_replace('-', '_', $input['bind_html']) . '.html';
                $catalogFile  = theme_now_path() .'list/'. str_replace('-', '_', $input['bind_html']) . '.html';
                if (! is_file($singleFile)) File::create($singleFile);
                if  (! is_file($catalogFile)) File::create($catalogFile);
            }
            $input['level']    = $input['pid'] === 0 ? 1 : CatalogModel::where('id', $input['pid'])->value('level') + 1;
            CatalogModel::update($input);
            // 清除缓存
            cache('catalog_' . theme(), NULL);
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            CatalogModel::recursiveDestroy(input('post.ids'));
            // 清除缓存
            cache('catalog_' . theme(), NULL);
            return json(['status' => 'success', 'message' => '删除成功']);
        }
    }

    /**
     * 自定义查询
     */
    public function query() {
        if ($this->request->isPost()) {
            $input = input('post.');
            $where[] = ['status', '=', 1];
            $where[] = ['theme', '=', theme()];
            if (! empty($input['type'])) {
                $where[] = ['type','=', $input['type']];
            }
            $data  = CatalogModel::where($where)->field('id,pid,title,seo_url,links_type,links_value')->order(['sort'=>'desc'])->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
        }
    }
}