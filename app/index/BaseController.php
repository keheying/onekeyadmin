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

namespace app\index;

use think\App;
use think\facade\View;
/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        View::assign([
            'theme'         => theme(),
            'language'      => $this->request->lang,
            'searchCatalog' => $this->request->searchCatalog,
            'system'        => $this->request->system,
            'catalog'       => $this->request->catalog,
            'catalogNum'    => $this->request->catalogNum,
            'catalogList'   => $this->request->catalogList,
            'catalogIndex'  => $this->request->catalogIndex,
            'catalogHeader' => $this->request->catalogHeader,
            'catalogFooter' => $this->request->catalogFooter,
            'userInfo'      => $this->request->userInfo,
            'langAllow'     => $this->request->langAllow,
            'langDefault'   => config('lang.default_lang'),
            'isMobile'      => $this->request->isMobile(),
            'header'        => theme_now_view() . 'common/header.html',
            'footer'        => theme_now_view() . 'common/footer.html',
        ]);
    }
}
