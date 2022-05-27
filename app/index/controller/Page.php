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
namespace app\index\controller;

use think\facade\Db;
use think\facade\View;
use app\index\addons\Url;
use app\index\BaseController;
use app\index\model\UserLog;
use app\index\model\User as UserModel;
/**
 * 系统页面
 */
class Page extends BaseController
{
    /**
     * 页面
     */
    public function index()
    {
        $name = str_replace('-', '_', $this->request->catalog['seo_url']);
        return View::fetch($name);
    }

    /**
     * 搜索
     */
    public function search()
    {
        $input    = input();
        $modular  = isset($input['modular']) ? $input['modular'] : '';
        $keyword  = isset($input['keyword']) ? $input['keyword'] : '';
        $page     = isset($input['page']) ? $input['page'] : 1;
        $field    = 'id,cover,title,seo_url,catalog_id,seo_description,create_time,status,language';
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
        if ($this->request->isPost()) {
            $data = [];
            $count = 0;
            $union = "";
            $where[] = ['status', '=', 1];
            $where[] = ['language', '=', $this->request->lang];
            $where[] = ['title|seo_description', 'like', '%'.$keyword.'%'];
            foreach ($modulars as $key => $route) {
                if (! empty($keyword)) {
                    if (empty($modular) || $modular === $route['catalog']) {
                        // 总数
                        $count = $count + Db::name($route['table'])->where($where)->count();
                        // 拼接
                        if (empty($union)) {
                            $union = Db::name($route['table'])->field($field.',"'.$route['catalog'].'" as catalog')->where($where);
                        } else {
                            $union = $union->unionAll('SELECT '.$field.',"'.$route['catalog'].'" AS catalog FROM mk_'.$route['table'].' WHERE status=1 AND language="'.$this->request->lang.'" AND title LIKE "%'.$keyword.'%" OR seo_description LIKE "%'.$keyword.'%"');
                        }
                    }
                }
                
            }
            // 查询
            if (! empty($union)) {
                $data = $union->page($page, 10)->select()->toArray();
                foreach ($data as $index => $item) {
                    $data[$index]['title'] = str_ireplace($keyword, '<b style="color:#016aac">'.$keyword.'</b>', $item['title']);
                    $data[$index]['type'] = $item['catalog'];
                    $data[$index]['url'] = Url::getSingleUrl($item);
                }
            }
            return json(['status' => 'success', 'message' => lang('successful operation'), 'data' => $data, 'count' => $count]);
        } else {
            View::assign([
                'modular'  => $modular,
                'keyword'  => $keyword,
                'modulars' => $modulars,
            ]);
            return View::fetch('search');
        }
    }

    /**
     * 他人主页
     */
    public function userpage()
    {
        $input = input();
        if (empty($input['id'])){
            return abort(404);
        }
        // 用户信息
        $pageUserInfo = UserModel::with(['group'])->where('id', $input['id'])->find();
        if (! $pageUserInfo) {
            return abort(404);
        }
        if ($this->request->userInfo->id != $input['id']) {
            // 记录访客
            $visitor = UserLog::where('user_id', $this->request->userInfo->id)->where('to_id',$input['id'])->where('type','visitor')->find();
            if ($visitor) {
                $visitor->create_time = date('Y-m-d H:i:s');
                $visitor->number = $visitor->number + 1;
                $visitor->save();
            } else {
                UserLog::create([
                    'user_id'     => $this->request->userInfo->id,
                    'explain'     => 'visitor',
                    'number'      => 0,
                    'inc'         => 1,
                    'to_id'       => $input['id'],
                    'type'        => 'visitor',
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        event('UserPage', $pageUserInfo);
        View::assign('pageUserInfo', $pageUserInfo);
        return View::fetch("userpage");
    }

    /**
     * 访客记录
     */
    public function visitorPage() 
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $where[] = ['type', '=', 'visitor'];
            $where[] = ['to_id', '=', $input['id']];
            $count = UserLog::where($where)->count();
            $data  = UserLog::where($where)->with(['user'])->order('create_time','desc')->page($input['page'],10)->select();
            return json(['status' => 'success','message' => lang('successful operation'), 'data' => $data, 'count' => $count]);
        }
    }

    /**
     * 粉丝/关注列表
     */
    public function fansPage() 
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $idFd = $input['type'] === 'fans' ? 'to_id' : 'user_id';
            $with = $input['type'] === 'fans' ? 'user' : 'to';
            $where[] = ['type', '=', 'fans'];
            $where[] = [$idFd, '=', $input['id']];
            $count = UserLog::where($where)->count();
            $data  = UserLog::where($where)->with($with)->order('create_time','desc')->page($input['page'],10)->select();
            return json(['status' => 'success','message' => lang('successful operation'), 'data' => $data, 'count' => $count]);
        }
    }

    /**
     * 留言列表
     */
    public function messagePage() 
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $where[] = ['type', '=', 'message'];
            $where[] = ['to_id', '=', $input['id']];
            $count = UserLog::where($where)->count();
            $data  = UserLog::where($where)->with(['user'])->order('create_time','desc')->page($input['page'],10)->select();
            return json(['status' => 'success','message' => lang('successful operation'), 'data' => $data, 'count' => $count]);
        }
    }
}
