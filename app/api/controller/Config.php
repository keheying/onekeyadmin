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

namespace app\api\controller;

use think\captcha\facade\Captcha;
use app\api\BaseController;
use app\api\model\Themes;
use app\api\model\Config as ConfigModel;
/**
 * 配置信息
 */
class Config extends BaseController
{
	/**
     * 验证码
     */
    public function verify()
    {
        return Captcha::create();
    }
    
	/**
	 * 基础信息
	 */
	public function index() 
	{
		if ($this->request->isPost()) {
			// 基础信息
	        $data = ConfigModel::getVal('system');
	        // 主题信息
	        $theme = Themes::where('name', theme())->cache('theme_' . theme())->field('config')->find();
	        foreach ($theme->config as $k => $v) { 
	            $data[$v['field']] = $v['type']['value'];
	        }
	        return json(['status' => 'success', 'message' => '获取成功', 'data' => $data]);
	    }
	}
}