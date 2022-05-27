<?php
// 字段信息
$keywordField = [];
$viewField    = [];
foreach ($field as $key => $val) {
    $is   = '';
    $type = '';
    switch ($val['Type']) {
        case 'text':
        case 'longtext':
            $is = 'el-editor';
            break;
        case 'time':
            $is = 'el-time-picker';
            break;
        case 'date':
            $is = 'el-date-picker';
            $type = 'date';
            break;    
        case 'datetime':
            $is = 'el-date-picker';
            $type = 'datetime';
            break;
    }
    if ($val['Field'] == 'id') {
        $viewField[$key]['label'] = '编号';
    } else {
        $viewField[$key]['label'] = empty($val['Comment']) ? $val['Field'] : $val['Comment'];
    }
    $viewField[$key]['prop']  = $val['Field'];
    $viewField[$key]['type']  = $type;
    $viewField[$key]['is']    = $is;
    if ($val['Type'] == 'varchar' || $val['Type'] == 'char' || $val['Type'] == 'text') {
        array_push($keywordField, $val['Field']);
    }
}

// 生成控制器
$controller = '<?php
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

use think\facade\View;
use app\admin\BaseController;
use app\admin\model\\'.$class.' as '.$class.'Model;
/**
 * '.$class.'管理
 */
class '.$class.' extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input = input("post.");
            $count = '.$class.'Model::withSearch(["keyword"], $input)->count();
            $data  = '.$class.'Model::withSearch(["keyword"], $input)->order($input["prop"], $input["order"])->page($input["page"], $input["pageSize"])->select();
            return json(["status" => "success", "message" => "请求成功", "data" => $data, "count" => $count]);
        } else {
            return View::fetch();
        }
    }
    
    /**
     * 保存新建的资源
     */
    public function save()
    {
        if ($this->request->isPost()) {
            '.$class.'Model::create(input("post."));
            return json(["status" => "success", "message" => "添加成功"]);
        }
    }
    
    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            '.$class.'Model::update(input("post."));
            return json(["status" => "success", "message" => "修改成功"]);
        }
    }
    
    /**
     * 删除指定资源
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            '.$class.'Model::destroy(input("post.ids"));
            return json(["status" => "success", "message" => "删除成功"]);
        }
    }
}';

// 生成模型
$model = '<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class '.$class.' extends Model
{
    // 搜索器
    public function searchKeywordAttr($query, $value)
    {
    	if (! empty($value)) {
	        $query->where("'.implode('|', $keywordField).'","like", "%" . $value . "%");
	    }
    }
}';

// 生成视图
$viewFieldStr = "[";
foreach ($viewField as $key => $val) {
    $comma = count($viewField) > $key ? "," : "";
    $type = empty($val['type']) ? '' : ", type: '".$val['type']."'";
    $form = empty($val['is']) ? '' : ", form: {is: '".$val['is']."'$type}";
    $viewFieldStr .= "\n\t\t\t\t\t{prop: '".$val['prop']."', label: '".$val['label']."'$form}$comma";
}
$viewFieldStr .= "\n\t\t\t\t]";
$view = '{include file="common/header"}
<div id="app" v-cloak>
    <el-curd :field="field"></el-curd>
</div>
<script>
    new Vue({
        el: "#app",
        data() {
            return {
                field: '.$viewFieldStr.',
            }
        },
    })
</script>
{include file="common/footer"}
';