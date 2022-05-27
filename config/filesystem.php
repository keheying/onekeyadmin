<?php
// +----------------------------------------------------------------------
// | 上传磁盘
// +----------------------------------------------------------------------
return [
    // 默认磁盘
    'default' => 'public',
    // 磁盘列表
    'disks'   => [
        'public' => [
            'type'       => 'local',
            'root'       => app()->getRootPath() . 'public/upload',
            'url'        => '/upload',
            'visibility' => 'public',
        ],
        'root' => [
            'type'       => 'local',
            'root'       => app()->getRootPath(),
        ],
        'plugins' => [
            'type'       => 'local',
            'root'       => plugin_path(),
        ],
        // 更多的磁盘配置信息
    ],
];
