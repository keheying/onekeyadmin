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
namespace app\api\controller;

use think\facade\Db;
use app\index\addons\Url;
use app\api\BaseController;
/**
 * 常用模块
 */
class Search extends BaseController
{
    /**
     * 全站搜索
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input    = input('post.');
            $modular  = isset($input['catalog']) ? $input['catalog'] : '';
            $keyword  = isset($input['keyword']) ? $input['keyword'] : '';
            $page     = isset($input['page']) ? $input['page'] : 1;
            $field    = 'id,catalog_id,cover,title,seo_url,seo_description,description,create_time,status';
            $modulars = [];
            foreach (plugin_list() as $key => $value) {
                foreach ($value['route'] as $k => $route) {
                    // 表存在
                    if (Db::query("SHOW TABLES LIKE 'mk_".$route['table']."'")) {
                        $fieldAll = Db::query("SHOW COLUMNS FROM mk_".$route['table']."");
                        $fieldAll = array_column($fieldAll, 'Field');
                        $fieldArr = explode(',', $field);
                        $fieldExist = true;
                        foreach ($fieldArr as $key => $value) {
                            if (! in_array($value, $fieldAll)) $fieldExist = false;
                        }
                        // 可查询
                        if ($fieldExist) array_push($modulars, $route);
                    }
                }
            }
            $data = [];
            $count = 0;
            $union = "";
            $where[] = ['status', '=', 1];
            $where[] = ['title|description', 'like', '%'.$keyword.'%'];
            foreach ($modulars as $key => $route) {
                if (! empty($keyword)) {
                    if (empty($modular) || $modular === $route['catalog']) {
                        // 总数
                        $count = $count + Db::name($route['table'])->where($where)->count();
                        // 拼接
                        if (empty($union)) {
                            $union = Db::name($route['table'])->field($field.',"'.$route['catalog'].'" as catalog')->where($where);
                        } else {
                            $union = $union->unionAll('SELECT '.$field.',"'.$route['catalog'].'" AS catalog FROM mk_'.$route['table'].' WHERE status=1 AND title LIKE "%'.$keyword.'%" OR seo_description LIKE "%'.$keyword.'%"');
                        }
                    }
                }
                
            }
            // 查询
            if (! empty($union)) {
                $data = $union->page($page, 10)->select()->toArray();
                foreach ($data as $index => $item) {
                    $data[$index]['description'] = str_ireplace($keyword, '<b style="color:#016aac">'.$keyword.'</b>', $item['description']);
                    $data[$index]['title']       = str_ireplace($keyword, '<b style="color:#016aac">'.$keyword.'</b>', $item['title']);
                    $data[$index]['type']        = $item['catalog'];
                    $data[$index]['url']         = Url::single($item);
                }
            }
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data, 'count' => $count]);
        }
    }
}
