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

use ZipArchive;

/**
 * 文件组件
 */
class File
{
    /**
     * 创建文件
     * @param 文件名
     */
    public static function create(string $file, $txt = ""): bool
    {
        $path = dirname($file);
        if (! is_dir($path)) mkdir($path, 0777, true);
        $fopen = fopen($file, "w") or die('无法打开文件');
        if (! empty($txt)) fwrite($fopen, $txt);
        fclose($fopen);
        return true;
    }

    /**
     * 创建文件夹
     *
     * @param 文件夹路径
     * @param 访问权限
     * @param 是否递归创建
     */
    public static function dirMkdir($path = '', $mode = 0777, $recursive = true): bool
    {
        clearstatcache();
        if (!is_dir($path)) {
            mkdir($path, $mode, $recursive);
            return chmod($path, $mode);
        }
        return true;
    }

    /**
     * 文件夹文件拷贝
     *
     * @param 来源文件夹
     * @param 目的地文件夹
     */
    public static function dirCopy($src = '', $dst = ''): bool
    {
        if (empty($src) || empty($dst)) return false;
        $dir = opendir($src);
        self::dirMkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::dirCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
        return true;
    }

    /**
     * 获取目录下所有文件
     * @param 目录
     */
    public static function getDir(string $path, &$files = []): array
    {
        if(is_dir($path)){
            $opendir = opendir($path);
            while ($file = readdir($opendir)){
                if($file != '.' && $file != '..'){
                    self::getDir($path.'/'.$file, $files);
                }
            }
            closedir($opendir);
        }
        if(!is_dir($path)){
            $files[] = $path;
        }
        return $files;
    }

    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param 待删除目录路径
     * @param 是否删除目录
     */
    public static function delDirAndFile(string $path, $delDir = true): bool
    {
        if (is_dir($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (false !== ( $item = readdir($handle) )) {
                    if ($item != "." && $item != "..") {
                        is_dir("$path/$item") ? self::delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
                    }
                }
                closedir($handle);
                if ($delDir) return rmdir($path);
            }else {
                return file_exists($path) ? unlink($path) : false;
            }
        } else {
            return true;
        }
    }

    /**
     * 提取文件
     * @param 压缩包
     * @param 路径
     * @param 跳过那些目录
     */
    public static function extract(string $zip, string $to, array $jump = []): bool
    {
        // 执行解压
        if (is_file($zip)) {
            $zipArchive = new ZipArchive;
            if ($zipArchive->open($zip) === true) {
                for ($i=0; $i<$zipArchive->numFiles; $i++) {
                    $entryInfo = $zipArchive->statIndex($i);
                    foreach ($jump as $k => $v) {
                        if(strpos($entryInfo["name"], $v) === 0){
                            $zipArchive->deleteIndex($i);
                        }
                    }
                }
                $zipArchive->close();
                if ($zipArchive->open($zip) === true) {
                    $zipArchive->extractTo($to);
                    $zipArchive->close();
                    unlink($zip);
                } else {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 创建压缩文件
     * @param 指定压缩目录
     * @param 压缩包文件
     * @param 允许的目录
     */
    public static function createZip(string $package, string $zipFile, $exclude = []): bool
    {
        $pathinfo = pathinfo($zipFile);
        $dirName  = $pathinfo['dirname'];
        if (is_file($zipFile))unlink($zipFile);
        if (! is_dir($dirName)) mkdir($dirName, 0777, true);
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            self::addFileToZip($package, $zip, $exclude);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 压缩文件
     * @param 当前文件夹。
     */
    public static function addFileToZip(string $path, object $zip, array $exclude, $basePath = "")
    {
        $basePath = empty($basePath) ? $path : $basePath;
        $handler  = opendir($path);
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {
                if (is_dir($path . "/" . $filename)) {
                    self::addFileToZip($path . "/" . $filename, $zip, $exclude, $basePath);
                } else {
                    $allow = false;
                    if (empty($exclude)) {
                        // 空代表全部
                        $allow = true;
                    } else {
                        // 文件比对
                        $nowPath = str_replace('\/', '/', $path . "/" . $filename);
                        $nowPath = str_replace('\\', '/', $nowPath);
                        $nowPath = str_replace('//', '/', $nowPath);
                        foreach ($exclude as $k => $v) {
                            $current = str_replace('\\', '/', $v);
                            if (strstr($nowPath, $current) !== false) {
                                $allow = true;
                            }
                        }
                    }
                    if ($allow) {
                        $localname = str_replace($basePath, '', $path . "/" . $filename);
                        $zip->addFile($path . "/" . $filename, $localname);
                    }
                }
            }
        }
        if (is_file($path)) closedir($path);
    }
    
    /**
     * 文件存储类型
     * @param 文件后缀配置
     * @param 文件名称
     */
    public static function getType(array $ext, string $name): string
    {
        $type   = '';
        $suffix = pathinfo($name)['extension'];
        foreach ($ext as $key => $val) {
            if (strstr($val, $suffix)) $type = $key;
        }
        return $type;
    }

    /**
     * 文件大小,以GB、MB、KB、B输出
     * @param 文件大小
     */
    public static function formatBytes(int $size): string 
    {
        $units = [' B', ' KB', ' MB', ' GB', ' TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2) . $units[$i];
    }

    /**
     * 修改系统config文件
     * @param 配置前缀 $pat[0] = 参数前缀;
     * @param 数据变量 $rep[0] = 要替换的内容;
     * @param 数据变量 $file 文件名;
     */
    public static function editConfig(array $pat, array $rep, string $file): bool
    {
        if (is_array($pat) and is_array($rep)) {
            for ($i = 0; $i < count($pat); $i++) {
                $pats[$i] = '/\'' . $pat[$i] . '\'(.*?),/';
                $reps[$i] = "'". $pat[$i]. "'". " => " . "'".$rep[$i] ."',";
            }
            $string  = file_get_contents($file);
            $string  = preg_replace($pats, $reps, $string);
            file_put_contents($file, $string);
            return true;
        } else {
            return false;
        }
    }
}