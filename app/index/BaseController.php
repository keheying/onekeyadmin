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
            'label'         => $this->request->label,
            'system'        => $this->request->system,
            'crumbs'        => $this->request->crumbs,
            'pathinfo'      => $this->request->pathinfo,
            'catalog'       => $this->request->catalog,
            'catalogList'   => $this->request->catalogList,
            'catalogHeader' => $this->request->catalogHeader,
            'catalogFooter' => $this->request->catalogFooter,
        ]);
    }
}
