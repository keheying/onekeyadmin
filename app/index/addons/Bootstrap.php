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
namespace app\index\addons;

use think\Paginator;
use app\index\addons\Url;
/**
 * Bootstrap 分页驱动
 */
class Bootstrap extends Paginator
{

    /**
     * 上一页按钮
     */
    protected function getPreviousButton(): string
    {
        $text = lang("prev page");
        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = Url::getCatalogPageUrl($this->currentPage() - 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 下一页按钮
     */
    protected function getNextButton(): string
    {
        $text = lang("next page");
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = Url::getCatalogPageUrl($this->currentPage() + 1);
        return $this->getPageLinkWrapper($url, $text);
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
                    '<div class="el-page">%s %s</div>',
                    $this->getPreviousButton(),
                    $this->getNextButton()
                );
            } else {
                return sprintf(
                    '<div class="el-page">%s %s %s</div>',
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
        return '<a href="' . htmlentities($url) . '">' . $page . '</a>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param 文字
     */
    protected function getDisabledTextWrapper(string $text): string
    {
        return '<span class="disabled">' . $text . '</span>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param 文字
     */
    protected function getActivePageWrapper(string $text): string
    {
        return '<span class="active">' . $text . '</span>';
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
        $html = '';

        foreach ($urls as $page => $val) {
            $url  = Url::getCatalogPageUrl($page);
            $html .= $this->getPageLinkWrapper($url, $page);
        }

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
