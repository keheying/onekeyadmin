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

namespace onekey;
/**
 * Tree 树型类(无限分类)
 */
class Tree {
    private $result;

    private $tmp;

    private $arr;

    private $already = array();
    
    /**
     * 构造函数
     *
     * @param 树型数据表结果集
     * @param 树型数据表字段，array(分类id,父id)
     * @param 顶级分类的父id
     */
    public function __construct(array $result, $fields = ['id', 'pid'], $root = 0) 
    {
        $this->result = $result;
        $this->fields = $fields;
        $this->root = $root;
        $this->handler();
    }

    /**
     * 树型数据表结果集处理
     */
    private function handler() 
    {
        foreach ($this->result as $node) {
            if (! isset($node['children'])) {
                $node['children'] = [];
            }
            $tmp[$node[$this->fields[1]]][] = $node;
        }
        if (empty($tmp)) {
            return [];
        }
        krsort($tmp);
        for ($i = count($tmp); $i > 0; $i--) {
            foreach ($tmp as $k => $v) {
                if (!in_array($k, $this->already)) {
                    if (!$this->tmp) {
                        $this->tmp = array($k, $v);
                        $this->already[] = $k;
                        continue;
                    } else {
                        foreach ($v as $key => $value) {
                            if ($value[$this->fields[0]] == $this->tmp[0]) {
                                $tmp[$k][$key]['children'] = $this->tmp[1];
                                $this->tmp = array($k, $tmp[$k]);
                            }
                        }
                    }
                }
            }
            $this->tmp = null;
        }
        $this->tmp = $tmp;
    }

    /**
     * 反向递归
     * @param 分类数组
     * @param 分类id
     */
    private function recur_n($arr, $id) 
    {
        foreach ($arr as $v) {
            if ($v[$this->fields[0]] == $id) {
                $this->arr[] = $v;
                if ($v[$this->fields[1]] != $this->root) $this->recur_n($arr, $v[$this->fields[1]]);
            }
        }
    }

    /**
     * 菜单 多维数组
     *
     * @param 分类id
     */
    public function leaf($id = null) 
    {
        $id = ($id == null) ? $this->root : $id;
        return isset($this->tmp[$id]) ? $this->tmp[$id] : [];
    }

    /**
     * 面包屑 一维数组
     *
     * @param 分类id
     */
    public function navi($id) 
    {
        $this->arr = [];
        $this->recur_n($this->result, $id);
        krsort($this->arr);
        $this->arr = array_values($this->arr);
        return $this->arr;
    }
}
?>