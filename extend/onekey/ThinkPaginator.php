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
namespace onekey;

use think\Paginator;
use app\index\addons\Url;
/**
 * 分页驱动
 */
class ThinkPaginator extends Paginator
{

    /**
     * 上一页按钮
     */
    protected function getPreviousButton(): string
    {
        $class = 'btn-prev';
        $text = '<i class="el-icon el-icon-arrow-left"></i>';
        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text, $class);
        }
        $page = $this->currentPage() - 1;
        $url  = index_url(request()->catalog['route'] .'/page/'. $page, $_GET);
        return $this->getPnButton($url, $text, $class);
    }

    /**
     * 下一页按钮
     */
    protected function getNextButton(): string
    {
        $class = 'btn-next';
        $text  = '<i class="el-icon el-icon-arrow-right"></i>';
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text, $class);
        }
        $page = $this->currentPage() + 1;
        $url  = index_url(request()->catalog['route'] .'/page/'. $page, $_GET);
        return $this->getPnButton($url, $text, $class);
    }
    
    /**
     * 上下页按钮
     */
    protected function getPnButton($url, $text, $class): string
    {
        return '<a href="'.$url.'"><button type="button" class="'.$class.'">'.$text.'</button></a>';
    }

    /**
     * 页码按钮
     */
    protected function getLinks(): string
    {
        if ($this->simple) {
            return '';
        }
        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null,
        ];
        $side   = 3;
        $window = $side * 2;
        if ($this->lastPage < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last']  = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last']  = $this->getUrlRange($this->lastPage - ($window + 2), $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        }
        $html = '';
        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }
        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }
        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }
        return $html;
    }

    /**
     * 渲染分页html
     */
    public function render()
    {
        if ($this->hasPages()) {
            if ($this->simple) {
                return sprintf(
                    '<div class="el-pagination is-background"><span class="el-pagination__total">共 '.$this->total.' 条</span>%s %s</div>',
                    $this->getPreviousButton(),
                    $this->getNextButton()
                );
            } else {
                return sprintf(
                    '<div class="el-pagination is-background"><span class="el-pagination__total">共 '.$this->total.' 条</span>%s %s %s</div>',
                    $this->getPreviousButton(),
                    $this->getLinks(),
                    $this->getNextButton()
                );
            }
        }
    }

    /**
     * 生成一个可点击的按钮
     *
     * @param 连接
     * @param 分页
     */
    protected function getAvailablePageWrapper(string $url, string $page): string
    {
        return '<a href="' . htmlentities($url) . '"><li class="number">' . $page . '</li></a>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param 文字
     */
    protected function getDisabledTextWrapper(string $text, $class = ""): string
    {
        return '<button type="button" disabled="disabled" class="$class">' . $text . '</button>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param 文字
     */
    protected function getActivePageWrapper(string $text): string
    {
        return '<li class="number active">' . $text . '</li>';
    }

    /**
     * 生成省略号按钮
     */
    protected function getDots(): string
    {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 批量生成页码按钮.
     *
     * @param 连接
     */
    protected function getUrlLinks(array $urls): string
    {
        $html = '<ul class="el-pager">';
        foreach ($urls as $page => $val) {
            $url = index_url(request()->catalog['route'] .'/page/'. $page, $_GET);
            $html .= $this->getPageLinkWrapper($url, $page);
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * 生成普通页码按钮
     *
     * @param 链接
     * @param 分页
     */
    protected function getPageLinkWrapper(string $url, string $page): string
    {
        if ($this->currentPage() == $page) {
            return $this->getActivePageWrapper($page);
        }
        return $this->getAvailablePageWrapper($url, $page);
    }
}
