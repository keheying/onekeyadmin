<?php
// 字段信息
$tableField   = \think\facade\Db::query("SHOW FULL COLUMNS FROM ".$name."");
$keywordField = [];
$dateField    = '';
foreach ($tableField as $key => $val) {
    if ($val['Type'] == 'datetime' || $val['Type'] == 'date') {
        $dateField = $val['Field'];
    }
    if (strstr($val['Type'], 'varchar') !== false ||strstr($val['Type'], 'char') !== false || $val['Type'] == 'text') {
        array_push($keywordField, $val['Field']);
    }
}

// 生成前台控制器
$indexController = '<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace plugins\\'.$input['name'].'\index\controller;

use think\facade\View;
use app\index\BaseController;
use plugins\\'.$input['name'].'\index\model\\'.$class.' as '.$class.'Model;
/**
 * '.$class.'管理
 */
class '.$class.' extends BaseController
{
    /**
     * 显示资源列表
     * 访问：'.$_SERVER['SERVER_NAME'].'/'.$input['name'].'/'.lcfirst($class).'/index.html
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input = input("post.");
            $page  = empty($input["page"]) ? 1 : $input["page"];
            $count = '.$class.'Model::count();
            $data  = '.$class.'Model::page($page, 10)->select();
            return json(["status" => "success", "message" => "请求成功", "data" => $data, "count" => $count]);
        }
    }
    
    /**
     * 显示资源详情
     */
    public function single()
    {
        if ($this->request->isPost()) {
            '.$class.'Model::find(input("post.id"));
            return json(["status" => "success", "message" => "请求成功", "data" => $data]);
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
    
    /**
     * 自定义更多方法...
     */
}';

// 生成后台控制器
$adminController = '<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace plugins\\'.$input['name'].'\admin\controller;

use think\facade\View;
use app\admin\BaseController;
use plugins\\'.$input['name'].'\admin\model\\'.$class.' as '.$class.'Model;
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

// 生成前台模型
$indexModel = '<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace plugins\\'.$input['name'].'\index\model;

use think\Model;

class '.$class.' extends Model
{
    protected $name = "'.$table.'";
}';

// 生成后台模型
$admminModel = '<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace plugins\\'.$input['name'].'\admin\model;

use think\Model;

class '.$class.' extends Model
{
    protected $name = "'.$table.'";
    
    // 搜索器
    public function searchKeywordAttr($query, $value)
    {
    	if (! empty($value)) {
	        $query->where("'.implode('|', $keywordField).'","like", "%" . $value . "%");
	    }
    }
    
    public function searchDateAttr($query, $value, $array)
    {
        if (! empty($value)) { 
            $query->whereBetweenTime("'.$dateField.'", $value[0], $value[1]);
        }
    }
}';
// 生成field参数
$viewFieldStr = "[";
foreach ($field as $key => $val) {
    if ($val['prop'] === 'id') {
        $viewFieldStr .= "\n\t\t\t\t\t{";
        $viewFieldStr .= "\n\t\t\t\t\t\tprop: 'id',";
        $viewFieldStr .= "\n\t\t\t\t\t\tlabel: '编号',"; 
        $viewFieldStr .= "\n\t\t\t\t\t\tform: false,";
        $viewFieldStr .= "\n\t\t\t\t\t\ttable: false";
        $viewFieldStr .= "\n\t\t\t\t\t},";
    } else {
        $viewFieldStr .= "\n\t\t\t\t\t{";
        $viewFieldStr .= "\n\t\t\t\t\t\tprop: '".$val['prop']."',";
        $viewFieldStr .= "\n\t\t\t\t\t\tlabel: '".$val['label']."',"; 
        // 表格
        if (! $val['tableShow']) {
        $viewFieldStr .= "\n\t\t\t\t\t\ttable: false,";
        } else {
        $viewFieldStr .= "\n\t\t\t\t\t\ttable: {";
        if ($val['is'] == 'el-file-list-select') {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tis: 'image',";
        }
        if ($val['tableSort']) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tsort: true,";
        }
        if ($val['tableWidth'] > 0) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\twidth: '".$val['tableWidth']."px',";
        }
        if (! empty($val['tableBind'])) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tbind: ".json_encode($val['tableBind'],JSON_UNESCAPED_UNICODE).",";
        }
        if ($val['tableProp'] != '' && $val['tableProp'] != $val['prop']) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tprop: '".$val['tableProp']."',";
        }
        if ($val['tableLabel'] != '' && $val['tableLabel'] != $val['label']) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tlabel: '".$val['tableLabel']."',";
        }
        $viewFieldStr .= "\n\t\t\t\t\t\t},";
        }
        // 表单
        if (! $val['formShow']) {
        $viewFieldStr .= "\n\t\t\t\t\t\tform: false";
        } else {
        $viewFieldStr .= "\n\t\t\t\t\t\tform: {";
        $viewFieldStr .= "\n\t\t\t\t\t\t\tis: '".$val['is']."',";
        if ($val['placeholder'] !== '' && $val['is'] == 'el-input' || $val['is'] == 'el-editor' || $val['is'] == 'el-select') {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tplaceholder: '".$val['placeholder']."',";
        }
        if ($val['tips'] !== '') {
        $viewFieldStr .= "\n\t\t\t\t\t\t\ttips: '".$val['tips']."',";    
        }
        if ($val['default'] !== '' && $val['is'] != 'el-link-select' && $val['is'] != 'el-parameter') {
        if (is_array($val['default'])) {
            $default = json_encode($val['default'],JSON_UNESCAPED_UNICODE);
        } else {
            $default = '\''.$val['default'].'\'';
        }
        $viewFieldStr .= "\n\t\t\t\t\t\t\tdefault: ".$default.",";    
        }
        if ($val['pattern'] !== '' || $val['required']) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\trules: [";
        if ($val['required']) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\t\t{required: true, message: '请输入'},";
        }
        if ($val['pattern'] !== '' && $val['is'] == 'el-input') {
        $viewFieldStr .= "\n\t\t\t\t\t\t\t\t{pattern: ".$val['pattern'].",message: '格式错误'},";    
        }
        $viewFieldStr .= "\n\t\t\t\t\t\t\t],";
        }
        if ($val['disabled']) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tdisabled: true,";
        }
        if ($val['colMd'] !== '' && $val['colMd'] != $value['form_col_md']) {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tcolMd: ".$val['colMd'].",";
        }
        if ($val['is'] == 'el-radio-group' || $val['is'] == 'el-checkbox-group' || $val['is'] == 'el-select') {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tchild: {";
        $viewFieldStr .= "\n\t\t\t\t\t\t\t\tvalue: ".json_encode($val['child'],JSON_UNESCAPED_UNICODE).",";
        $viewFieldStr .= "\n\t\t\t\t\t\t\t\tprops: {label: 'title', value: 'value'}";
        $viewFieldStr .= "},";
        }
        if ($val['type'] !== '' && $val['is'] == 'el-input' || $val['is'] == 'el-file-select' || $val['is'] == 'el-file-list-select' || $val['is'] == 'el-date-picker') {
        $viewFieldStr .= "\n\t\t\t\t\t\t\ttype: '".$val['type']."',";
        }
        if ($val['is'] == 'el-file-select') {
        $viewFieldStr .= "\n\t\t\t\t\t\t\tfilterable: ".$val['filterable'].",";
        $viewFieldStr .= "\n\t\t\t\t\t\t\tmultiple: ".$val['multiple'].",";
        }
        $viewFieldStr .= "\n\t\t\t\t\t\t}";
        }
        $viewFieldStr .= "\n\t\t\t\t\t},";
    }
}
$viewFieldStr .= "\n\t\t\t\t]";
// 生成后台视图
$adminView  = '{include file="common/header"}';
$adminView .= "\n".'<div id="app" v-cloak>';
$adminView .= "\n\t".'<el-curd';
$adminView .= "\n\t\t".':field="field"';
if ($value['form_label_width'] != 100) {
$adminView .= "\n\t\t".':form-label-width="'.$value['form_label_width'].'px"';
}
if ($value['form_col_md'] != 24) {
$adminView .= "\n\t\t".':form-col-md="'.$value['form_col_md'].'"';
}
if ($value['table_export'] == 0) {
$adminView .= "\n\t\t".':table-export="false"';
}
if ($value['table_sort'] !== '') {
$adminView .= "\n\t\t".':table-sort="{prop: \''.$value['table_sort'].'\', order: \'desc\'}"';
}
if ($value['table_sort'] != 20) {
$adminView .= "\n\t\t".':table-page-size="'.$value['table_page_size'].'"';
$adminView .= "\n\t\t".':table-page-sizes="['.$value['table_page_size'].', 50, 100, 200, 500]"';
}
if ($value['table_operation_width'] != 0) {
$adminView .= "\n\t\t".':table-operation-width="'.$value['table_operation_width'].'"';
}
if (! empty($value['search_catalog'])) {
$search_catalog = [];
foreach ($value['search_catalog'] as $key => $val){
    $search_catalog[$key]['id'] = $val['value'];
    $search_catalog[$key]['title'] = $val['title'];
}
$adminView .= "\n\t\t:search-catalog='".json_encode($search_catalog,JSON_UNESCAPED_UNICODE)."'";
}
if (! empty($value['search_status'])) {
$search_status = [];
foreach ($value['search_status'] as $key => $val){
    $search_status[$key]['value'] = $val['value'];
    $search_status[$key]['label'] = $val['title'];
}
$adminView .= "\n\t\t:search-status='".json_encode($search_status,JSON_UNESCAPED_UNICODE)."'";
}
if ($value['search_keyword'] == 0) {
$adminView .= "\n\t\t".':search-keyword="false"';
}
if ($value['search_date'] == 0) {
$adminView .= "\n\t\t".':search-date="false"';
}
if ($value['table_tree'] == 1) {
$adminView .= "\n\t\ttable-tree";
}
if ($value['table_expand'] == 1) {
$adminView .= "\n\t\ttable-expand";
}
if ($value['preview'] == 1) {
$adminView .= "\n\t\tpreview";
}
$adminView .= ">";
$adminView .= "\n\t".'</el-curd>';
$adminView .= "\n".'</div>';
$adminView .= "\n".'<script>';
$adminView .= "\n\t".'new Vue({';
$adminView .= "\n\t\t".'el: "#app",';
$adminView .= "\n\t\t".'data() {';
$adminView .= "\n\t\t\t".'return {';
$adminView .= "\n\t\t\t\t".'field: '.$viewFieldStr.',';
$adminView .= "\n\t\t\t".'}';
$adminView .= "\n\t\t".'},';
$adminView .= "\n\t".'})';
$adminView .= "\n".'</script>';
$adminView .= "\n".'{include file="common/footer"}';