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
namespace app\admin\controller;

use think\Image as thinkImage;
use think\facade\View;
use think\facade\Filesystem;
use think\exception\ValidateException;
use app\admin\addons\Image;
use app\admin\BaseController;
use app\admin\model\Config;
use app\addons\File as FileAddons;
use app\admin\model\File as FileModel;
/**
 * 文件
 */
class File extends BaseController
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $count = FileModel::withSearch(['keyword','type'], $input)->count();
            $data  = FileModel::withSearch(['keyword','type'], $input)->order($input['prop'], $input['order'])->page($input['page'], 20)->select();
            return json(['status' => 'success', 'message' => '获取成功', 'data' => $data, 'count' => $count]);
        } else {
            return View::fetch();
        }
    }

    /**
     * 保存更新的资源
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            FileModel::where('id', $input['id'])->update($input);
            return json(['status' => 'success', 'message' => '修改成功']);
        }
    }

    /**
     * 下载文件
     */
    public function download()
    {
        $input = input();
        return download(public_path() . $input['url'], $input['title']);
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        $file = $this->request->file('file');
        $limitExt = config('upload.ext');
        $limitSize = config('upload.size');
        $type = FileAddons::getType($limitExt, $file->getOriginalName());
        if (empty($type)) return json(['status' => 'error', 'message' => '此类型的文件不支持上传！']);
        try {
            validate([ 'file' => ['fileSize' => $limitSize[$type], 'fileExt' => $limitExt[$type]] ])->check(['file' => $file]);
            $url = Filesystem::putFile($type, $file);
            $save = FileModel::create([
                'title'       => $file->getOriginalName(),
                'size'        => $file->getSize(),
                'url'         => '/upload/' . str_replace('\\', '/', $url),
                'type'        => $type,
                'create_time' => date('Y-m-d H:i:s'),
                'status'      => 1,
                'theme'       => theme(),
            ]);
            if ($type === "image") {
                // 封面图片
                Image::thumb($save['url'],100,100);
                // 水印图片
                $config = $this->request->watermark;
                if (!empty($config)) {
                    if ($config['open'] === 1) {
                        $file = str_replace('\/', '/', public_path() . $save->url);
                        $image = thinkImage::open($file);
                        $scale = (int)$config['scale'] / 100;
                        $position = (int)$config['position'];
                        $opacity = (int)$config['opacity'];
                        $height = $image->height();
                        $width = $image->width(); 
                        if ($config['type'] === 'image') {
                            $water = public_path() . 'upload/watermark.png';
                            if (is_file($water)) {
                                if ($config['sizeType'] === 'scale') {
                                    // 按照比例
                                    $thumb = thinkImage::open($water);
                                    $waterName = pathinfo($save->url, PATHINFO_FILENAME);
                                    $waterThumb = str_replace($waterName, 'watermark_thumb', $water);
                                    $thumb->thumb($width*$scale, $height*$scale)->save($waterThumb);
                                    $image->water($waterThumb, $position, $opacity)->save($file);
                                    if (is_file($waterThumb)) {
                                        unlink($waterThumb);
                                    }
                                } else {
                                    // 按实际大小
                                    $image->water($water, $position, $opacity)->save($file);
                                }
                            }
                        } else {
                            $opacity    = 127 - (127 * $opacity / 100);
                            $dechex     = dechex($opacity);
                            $fontColor  = $config['fontColor'].$dechex;
                            $fontSize   = $config['sizeType'] === 'scale' ? $scale * ($width/2) : $config['fontSize'];
                            $fontFamily = public_path() . $config['fontFamily'];
                            $image->text($config['fontText'], $fontFamily, $fontSize, $fontColor, $position, 0, $config['fontAngle'])->save($file);
                        }
                    }
                }
            }
            return json(['status' => 'success', 'message' => '上传成功', 'data' => $save]);
        } catch (ValidateException $e) {
            return json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 放入回收站
     */
    public function recovery()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            FileModel::whereIn('id', $input['ids'])->update(['status' =>  0]);
            return json(['status' => 'success', 'message' => '回收成功']);
        }
    }

    /**
     * 彻底删除
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $input = input('post.');
            $list  = FileModel::whereIn('id', $input['ids'])->select();
            foreach ($list as $key => $val) {
                $file = str_replace('\/', '/', public_path() . $val->url);
                $name = pathinfo($val->url, PATHINFO_FILENAME);
                $thumb = str_replace($name, $name . '100x100', $file);
                if ($val->type === 'image') {
                    if(is_file($thumb)) unlink($thumb);
                }
                if(is_file($file)) unlink($file);
            }
            FileModel::whereIn('id', $input['ids'])->delete();
            return json(['status' => 'success', 'message' => '删除成功']);
        }
    }

    /**
     * 清空回收站
     */
    public function emptyTrash()
    {
        if ($this->request->isPost()) {
            $list = FileModel::where(['status' =>  0])->select();
            foreach ($list as $key => $val) {
                $file = str_replace('\/', '/', public_path() . $val->url);
                $name = pathinfo($val->url, PATHINFO_FILENAME);
                $thumb = str_replace($name, $name . '100x100', $file);
                if ($val->type === 'image') {
                    if(is_file($thumb)) unlink($thumb);
                }
                if(is_file($file)) unlink($file);
            }
            FileModel::where(['status' =>  0])->delete();
            return json(['status' => 'success', 'message' => '清空成功']);
        }
    }

    /**
     * 还原文件
     */
    public function reduction()
    {
        if ($this->request->isPost()) {
            FileModel::whereIn('id', input('ids'))->update(['status' =>  1]);
            return json(['status' => 'success', 'message' => '还原成功']);
        }
    }

    /**
     * 上传指定目录文件
     */
    public function uploadAppoint()
    {
        $input = input('post.');
        if (! isset($input['name'])) return json(['status' => 'error', 'message' => '请指定文件名称！']);
        try {
            $file = $this->request->file('file');
            $path = 'upload';
            $size = isset($input['size']) ? $input['size'] : 1*1024*1024;
            $ext  = isset($input['ext']) ? $input['ext'] : '';
            $name = $input['name'].'.'.$ext;
            validate([ 'file' => ['fileSize' => $size, 'fileExt' => $ext] ])->check(['file' => $file]);
            Filesystem::putFileAs('/', $file, $name);
            return json(['status' => 'success', 'message' => '上传成功', 'url' => '/'.$path.'/'.$name]);
        } catch (ValidateException $e) {
            return json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 水印设置
     */
    public function watermark()
    {
        if ($this->request->isPost()) {
            $message = Config::setVal('watermark', '图片水印', input('post.value'));
            return json($message);
        }
    }
}
