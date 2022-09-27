CREATE TABLE IF NOT EXISTS `mk_curd` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '表标题',
  `name` varchar(255) NOT NULL COMMENT '表名称',
  `field` text NOT NULL COMMENT '表字段',
  `sort` int NOT NULL COMMENT '排序',
  `plugin` varchar(255) NOT NULL COMMENT '生成插件',
  `number` int NOT NULL COMMENT '生成次数',
  `form_label_width` int NOT NULL,
  `form_col_md` int NOT NULL,
  `table_tree` tinyint(1) NOT NULL,
  `table_expand` tinyint(1) NOT NULL,
  `table_export` tinyint(1) NOT NULL,
  `table_sort` varchar(255) NOT NULL,
  `table_page_size` int NOT NULL,
  `table_operation_width` int NOT NULL,
  `search_catalog` varchar(255) NOT NULL,
  `search_status` varchar(255) NOT NULL,
  `search_keyword` tinyint(1) NOT NULL,
  `search_date` tinyint(1) NOT NULL,
  `preview` tinyint(1) NOT NULL,
  `create_time` datetime NOT NULL COMMENT '生成时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mk_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `nickname` varchar(255) NOT NULL COMMENT '昵称',
  `email` varchar(255) NOT NULL COMMENT '邮箱号',
  `account` varchar(255) NOT NULL COMMENT '登录账号',
  `password` varchar(60) NOT NULL COMMENT '登录密码',
  `cover` varchar(255) NOT NULL COMMENT '头像',
  `login_ip` varchar(15) NOT NULL,
  `login_count` int(11) NOT NULL,
  `login_time` datetime NOT NULL,
  `create_time` datetime NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0屏蔽 1正常',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mk_admin_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0屏蔽 1正常',
  `role` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
insert  into `mk_admin_group`(`id`,`admin_id`,`title`,`status`,`role`) values 
(1,0,'超级管理员',1,'*');

CREATE TABLE IF NOT EXISTS `mk_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员日志',
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `post` text NOT NULL,
  `create_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mk_admin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `sort` tinyint(2) NOT NULL,
  `ifshow` tinyint(1) NOT NULL COMMENT '左侧菜单是否显示',
  `logwriting` tinyint(1) NOT NULL COMMENT '0不写入 1写入日志',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=272 DEFAULT CHARSET=utf8mb4;
insert  into `mk_admin_menu`(`id`,`pid`,`title`,`icon`,`path`,`sort`,`ifshow`,`logwriting`) values 
(1,0,'控制台','/upload/image/20220211/b48a37cf8f30d842ef9803507a5df885.png','console/index',9,1,0),
(2,0,'常规管理','/upload/image/20220211/8d46a221fa0c28b5cf0990dae6bac26d.png','config',7,1,0),
(3,2,'个人中心','','admin/personal',1,1,1),
(4,2,'系统配置','','config/index',4,1,0),
(5,0,'资源库','/upload/image/20220211/d4808459eb28ca9db36caca08883449c.png','file/index',8,1,0),
(6,2,'分类管理','','catalog/index',3,1,0),
(7,0,'权限管理','/upload/image/20220211/c91588ce5ef5cec5abd4154dc8842e7a.png','authority',6,1,0),
(8,7,'管理员管理','','admin/index',4,1,0),
(9,7,'角色管理','','adminGroup/index',0,1,0),
(11,0,'会员管理','/upload/image/20220211/d7028be823a2eac0c1cf79709dabeabc.png','user',5,1,0),
(12,11,'会员列表','','user/index',0,1,0),
(13,11,'会员分组','','userGroup/index',0,1,0),
(15,0,'插件商店','/upload/image/20220211/09872db382dabec10aac1e2e34934a8e.png','plugins/list',4,1,0),
(17,15,'查看','','plugins/index',1,0,0),
(19,7,'菜单规则','','adminMenu/index',2,1,0),
(113,9,'查看','','adminGroup/index',1,0,0),
(114,9,'删除','','adminGroup/delete',0,0,1),
(115,9,'编辑','','adminGroup/update',0,0,1),
(118,19,'查看','','adminMenu/index',1,0,0),
(119,19,'删除','','adminMenu/delete',0,0,1),
(120,19,'修改','','adminMenu/update',0,0,1),
(121,9,'添加','','adminGroup/save',0,0,1),
(122,19,'添加','','adminMenu/save',0,0,1),
(125,8,'查看','','admin/index',1,0,0),
(126,8,'添加','','admin/save',0,0,1),
(127,8,'删除','','admin/delete',0,0,1),
(128,8,'修改','','admin/update',0,0,1),
(132,6,'查看','','catalog/index',1,0,0),
(133,6,'删除','','catalog/delete',0,0,1),
(134,6,'编辑','','catalog/update',0,0,1),
(135,6,'添加','','catalog/save',0,0,1),
(137,12,'添加','','user/save',0,0,1),
(138,12,'删除','','user/delete',0,0,1),
(139,12,'编辑','','user/update',0,0,1),
(141,13,'查看','','userGroup/index',1,0,0),
(142,13,'删除','','userGroup/delete',0,0,1),
(143,13,'添加','','userGroup/save',0,0,1),
(144,13,'编辑','','userGroup/update',0,0,1),
(145,12,'查看','','user/index',1,0,0),
(147,4,'链接','','config/link',0,0,0),
(148,4,'编辑','','config/update',0,0,1),
(154,5,'上传','','file/upload',7,0,1),
(157,5,'查看','','file/index',9,0,0),
(158,5,'彻底删除','','file/delete',5,0,1),
(160,5,'修改','','file/update',8,0,1),
(161,5,'清空回收站','','file/emptyTrash',4,0,1),
(168,4,'查看','','config/index',1,0,0),
(182,6,'同步','','catalog/synchro',0,0,1),
(183,6,'设置','','catalog/set',0,0,1),
(188,15,'卸载','','plugins/delete',0,0,1),
(190,15,'修改','','plugins/update',0,0,1),
(191,0,'主题商店','/upload/image/20220211/535663e74cf12f296074ef595be312e3.png','themes/index',3,1,0),
(192,191,'查看','','themes/index',1,0,0),
(193,191,'卸载','','themes/delete',0,0,1),
(194,191,'切换','','themes/update',0,0,1),
(195,191,'安装','','themes/install',0,0,1),
(198,15,'评论','','plugins/comment',0,0,1),
(199,15,'安装','','plugins/install',0,0,1),
(207,1,'检测更新','','index/checkUpdate',0,0,1),
(208,1,'系统更新','','index/update',0,0,1),
(212,15,'购买','','plugins/createOrder',0,0,1),
(213,191,'购买','','themes/createOrder',0,0,1),
(214,191,'支付方式','','themes/payMethod',0,0,1),
(215,15,'更新','','plugins/updateInstall',0,0,1),
(217,6,'自定义查询','','catalog/query',0,0,0),
(221,5,'自定义上传','','file/uploadAppoint',0,0,1),
(223,5,'放入回收站','','file/recovery',6,0,1),
(224,5,'文件还原','','file/reduction',3,0,1),
(251,7,'管理员日志','','adminLog/index',3,1,0),
(253,251,'删除','','adminLog/delete',1,0,1),
(255,251,'查看','','adminLog/index',3,0,0),
(257,1,'清除缓存','','index/cacheClear',0,0,1),
(258,5,'水印设置','','file/watermark',0,0,1),
(267,15,'支付方式','','plugins/payMethod',0,0,1),
(268,15,'订单查询','','plugins/statusOrder',0,0,1),
(269,191,'订单查询','','themes/statusOrder',0,0,1),
(270,191,'评论','','themes/comment',0,0,1),
(271,5,'下载','','file/download',7,0,1),
(297,0,'开发助手','/upload/image/20220215/e4c5c91f707df300ec49860df7eb92d2.png','curd/index',2,1,0),
(299,297,'查看','','curd/index',2,0,0),
(298,297,'生成','','curd/code',1,0,1),
(306,15,'打包','','plugins/create',0,0,1),
(307,297,'编辑','','curd/update',1,0,1),
(310,297,'删除','','curd/delete',1,0,1),
(308,297,'删除字段','','curd/deleteField',1,0,1),
(309,297,'新增字段','','curd/saveField',1,0,1),
(311,297,'修改字段','','curd/updateField',1,0,1);

CREATE TABLE IF NOT EXISTS `mk_catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `level` int(11) NOT NULL COMMENT '等级',
  `group_id` varchar(255) NOT NULL COMMENT '用户权限',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `cover` varchar(255) NOT NULL COMMENT '封面',
  `content` text NOT NULL COMMENT '内容',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `field` text NOT NULL COMMENT '自定义字段',
  `bind_html` varchar(255) NOT NULL COMMENT '绑定文件',
  `seo_url` varchar(255) NOT NULL COMMENT '目录链接(路由)',
  `seo_title` varchar(255) NOT NULL COMMENT '页面标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT '页面关键字',
  `seo_description` varchar(255) NOT NULL COMMENT '页面描述',
  `links_type` tinyint(1) NOT NULL COMMENT '1自定义链接',
  `links_value` text NOT NULL COMMENT '连接页面',
  `sort` int(11) NOT NULL COMMENT '排序',
  `type` varchar(255) NOT NULL,
  `blank` tinyint(1) NOT NULL COMMENT '1新窗口打开',
  `show` tinyint(1) NOT NULL COMMENT '0不显示1都显示2头部显示3底部显示',
  `status` tinyint(1) NOT NULL COMMENT '0屏蔽 1正常',
  `mobile` tinyint(1) NOT NULL COMMENT '手机栏目',
  `theme` varchar(255) NOT NULL COMMENT '当前主题',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
insert  into `mk_catalog`(`id`,`pid`,`level`,`group_id`,`title`,`cover`,`content`,`description`,`field`,`bind_html`,`seo_url`,`seo_title`,`seo_keywords`,`seo_description`,`links_type`,`links_value`,`sort`,`type`,`blank`,`show`,`status`,`mobile`,`theme`) values 
(1,0,1,'','首页','/upload/image/20220215/0be8a040d6db1b91d8ef0584dab5cd28.jpg','','','[]','','index','','','',0,'[]',0,'page',0,1,1,1,'template');

CREATE TABLE IF NOT EXISTS `mk_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '名称',
  `name` varchar(255) NOT NULL COMMENT '别名',
  `value` longtext NOT NULL COMMENT '值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
insert  into `mk_config`(`id`,`title`,`name`,`value`) values 
(1,'邮箱配置','email','{\"email\":\"\",\"password\":\"\",\"sender\":\"onekeyadmin\",\"smtp\":\"smtp.qq.com\",\"sendstyle\":\"ssl\"}'),
(2,'基础配置','system','{"company":"温州万旗信息科技有限公司","email":"513038996@qq.com","telephone":"0577-010101","phone":"157573963XX","fax":"0577-010101","address":"浙江省温州市洞头区北岙街道89号","business_hours":"早上8:30-下午17:30","ico":"\/upload\/favicon.ico?5874802","logo":"\/upload\/image\/20220215\/e4c5c91f707df300ec49860df7eb92df.png","copyright":"温州万旗信息科技有限公司","seo_title":"温州万旗信息科技有限公司","seo_keywords":"OneKeyAdmin","seo_description":"温州万旗信息科技有限公司","icp":"<p>Copyright &copy; 2021-2024 onekeyadmin.com All rights reserved 温州市万旗信息科技有限公司 版权所有 &nbsp;浙江省 ICP备案号2021005285<\/p>","copy_logo":"\/upload\/image\/20220420\/3ee6d6771233863c2f6957b05ace9b2b.png","qq":"513038996","wechat":"157573963XX","wechat_qrcode":"\/upload\/image\/20220521\/8408e90f09bc6761db4d4a780a8d2492.png"}'),
(3,'图片水印','watermark','{\"open\":0,\"type\":\"font\",\"sizeType\":\"actual\",\"scale\":\"15\",\"position\":\"5\",\"opacity\":\"100\",\"image\":\"\\/upload\\/watermark.png?4476064\",\"fontText\":\"OneKeyAdmin\",\"fontFamily\":\"\\/admin\\/css\\/fonts\\/FZHTJW.ttf\",\"fontSize\":\"28\",\"fontAngle\":0,\"fontColor\":\"#C91818\"}');

CREATE TABLE IF NOT EXISTS `mk_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `create_time` datetime NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0回收站 1正常',
  `theme` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;
insert  into `mk_file`(`id`,`title`,`url`,`size`,`type`,`create_time`,`status`,`theme`) values 
(1,'英文.png','/upload/image/20220211/a30d38ed39f14fbec51df4d1b00cb7bf.png',699,'image','2022-02-11 10:59:04',1,'template'),
(2,'中文.png','/upload/image/20220211/e7a0f46979cd6330bc58ee0d47792ac9.png',1005,'image','2022-02-11 10:59:04',1,'template'),
(3,'资源库.png','/upload/image/20220211/d4808459eb28ca9db36caca08883449c.png',335,'image','2022-02-11 11:14:18',1,'template'),
(4,'常规管理.png','/upload/image/20220211/8d46a221fa0c28b5cf0990dae6bac26d.png',266,'image','2022-02-11 11:14:18',1,'template'),
(5,'模板商店.png','/upload/image/20220211/535663e74cf12f296074ef595be312e3.png',175,'image','2022-02-11 11:14:18',1,'template'),
(6,'控制台.png','/upload/image/20220211/b48a37cf8f30d842ef9803507a5df885.png',385,'image','2022-02-11 11:14:19',1,'template'),
(7,'会员管理.png','/upload/image/20220211/d7028be823a2eac0c1cf79709dabeabc.png',385,'image','2022-02-11 11:14:19',1,'template'),
(8,'权限管理.png','/upload/image/20220211/c91588ce5ef5cec5abd4154dc8842e7a.png',369,'image','2022-02-11 11:14:19',1,'template'),
(9,'插件商店.png','/upload/image/20220211/09872db382dabec10aac1e2e34934a8e.png',332,'image','2022-02-11 11:14:19',1,'template'),
(10,'logo','/upload/image/20220215/e4c5c91f707df300ec49860df7eb92df.png',4533,'image','2022-02-15 16:32:33',1,'template'),
(11,'ban2.jpg','/upload/image/20220215/0be8a040d6db1b91d8ef0584dab5cd28.jpg',479414,'image','2022-02-15 16:40:25',1,'template'),
(12,'ny_bj2.jpg','/upload/image/20220215/314d6bbb79c769100887fa3a2e5c3b64.jpg',242532,'image','2022-02-15 16:40:34',1,'template'),
(13,'ny_bj2.jpeg','/upload/image/20220215/97965eb16edf667a733f2f73882174db.jpeg',161588,'image','2022-02-15 16:43:31',1,'template'),
(14,'微信图片_20220105090834.jpg','/upload/image/20220215/b04161a415bc3cc4281920e2acac6ac3.jpg',108124,'image','2022-02-15 16:58:04',1,'template');

CREATE TABLE IF NOT EXISTS `mk_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `config` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4;
insert  into `mk_themes`(`id`,`name`,`title`,`cover`,`price`,`config`) values 
(1,'template','默认模板','',10.00,'[{\"label\":\"\\u4e3b\\u8981\\u7279\\u6027\",\"field\":\"characteristic\",\"type\":{\"label\":\"\\u6587\\u672c\\u57df\",\"is\":\"el-input\",\"type\":\"textarea\",\"value\":\"\\u4f7f\\u7528\\u6700\\u65b0\\u7684 ThinkPHP6.0 + Mysql + Element\\uff0c\\u4ee3\\u7801\\u5f00\\u6e90\\u65e0\\u52a0\\u5bc6\\uff0c\\u6709\\u8be6\\u7ec6\\u7684\\u4ee3\\u7801\\u6ce8\\u91ca\\uff0c\\u6709\\u5b8c\\u6574\\u7cfb\\u7edf\\u624b\\u518c\\u30021\"}},{\"label\":\"\\u7cfb\\u7edf\\u6846\\u67b6\",\"field\":\"frame\",\"type\":{\"label\":\"\\u6587\\u672c\\u57df\",\"is\":\"el-input\",\"type\":\"textarea\",\"value\":\"\\u652f\\u6301\\u79fb\\u52a8\\u7f51\\u7ad9\\u5efa\\u8bbe\\u3001\\u5c0f\\u7a0b\\u5e8f\\u5f00\\u53d1\\u3001\\u5fae\\u4fe1\\u5e73\\u53f0\\u5f00\\u53d1\\u3001APP\\u5e94\\u7528\\u7a0b\\u5e8f\"}},{\"label\":\"\\u7cfb\\u7edf\\u6846\\u67b6\\u5217\\u8868\",\"field\":\"frame_list\",\"type\":{\"label\":\"\\u6570\\u7ec4\\u5217\\u8868\",\"is\":\"el-array\",\"value\":{\"column\":[{\"label\":\"\\u6807\\u9898\",\"field\":\"title\",\"type\":{\"label\":\"\\u6587\\u672c\",\"is\":\"el-input\",\"value\":\"\"}}],\"table\":[{\"title\":\"\\u4e00\\u952e\\u751f\\u6210\"},{\"title\":\"\\u8d44\\u6e90\\u7ba1\\u7406\"},{\"title\":\"\\u914d\\u7f6e\\u7ba1\\u7406\"},{\"title\":\"\\u5206\\u7c7b\\u7ba1\\u7406\"},{\"title\":\"\\u8bed\\u8a00\\u7ba1\\u7406\"},{\"title\":\"\\u6743\\u9650\\u7ba1\\u7406\"},{\"title\":\"\\u4f1a\\u5458\\u7ba1\\u7406\"},{\"title\":\"\\u5e94\\u7528\\u63d2\\u4ef6\"},{\"title\":\"\\u4e3b\\u9898\\u6a21\\u677f\"},{\"title\":\"\\u94a9\\u5b50\\u4e8b\\u4ef6\"},{\"title\":\"\\u8def\\u7531\\u673a\\u5236\"}]}}},{\"label\":\"\\u9879\\u76ee\\u4ecb\\u7ecd\",\"field\":\"project\",\"type\":{\"label\":\"\\u6587\\u672c\\u57df\",\"is\":\"el-input\",\"type\":\"textarea\",\"value\":\"OneKeyAdmin\\u662f\\u57fa\\u4e8eThinkphp6+Element\\u7f16\\u5199\\u7684\\u4e00\\u5957\\u540e\\u53f0\\u7ba1\\u7406\\u7cfb\\u7edf\\u3002\\u5b89\\u88c5\\u5305\\u53ea\\u67095MB\\u5927\\u5c0f\\uff0c\\u5374\\u62e5\\u6709\\u4e00\\u952e\\u751f\\u6210\\u4ee3\\u7801\\u529f\\u80fd\\u3001\\u65e0\\u9700\\u5199\\u9875\\u9762\\u5feb\\u901f\\u589e\\u5220\\u6539\\u67e5\\u3001\\u8d44\\u6e90\\u7ba1\\u7406\\u3001\\u6743\\u9650\\u7ba1\\u7406\\u3001\\u901a\\u7528\\u7684\\u4f1a\\u5458\\u6a21\\u5757\\u3001\\u7cfb\\u7edf\\u5206\\u7c7b\\u3001\\u591a\\u8bed\\u8a00\\u914d\\u7f6e\\u3001\\u57fa\\u7840\\u914d\\u7f6e\\u3001\\u7cfb\\u7edf\\u65e5\\u5fd7\\u3001\\u94a9\\u5b50\\u4e8b\\u4ef6\\u3002\"}},{\"label\":\"\\u9879\\u76ee\\u4ecb\\u7ecd\",\"field\":\"project_desc\",\"type\":{\"label\":\"\\u6587\\u672c\\u57df\",\"is\":\"el-input\",\"type\":\"textarea\",\"value\":\"\\u7528\\u6237\\u53ef\\u4e0a\\u4f20\\u63d2\\u4ef6\\u514d\\u8d39\\u6216\\u4ed8\\u8d39\\u7ed9\\u4e88TA\\u4eba\\u4f7f\\u7528\\uff0c\\u4e92\\u5e2e\\u4e92\\u52a9\\uff0c\\u5efa\\u7acb\\u4e00\\u4e2a\\u53cb\\u597d\\u7684\\u793e\\u533a\\u73af\\u5883\\u3002\\u8fd8\\u6709\\u591a\\u5957\\u4e3b\\u9898\\u6a21\\u677f\\u968f\\u65f6\\u5b89\\u88c5\\u5207\\u6362\\u4e14\\u5168\\u90e8\\u514d\\u8d39\\u4f7f\\u7528\\u54e6\"}},{\"label\":\"\\u9879\\u76ee\\u4ecb\\u7ecd\\u5217\\u8868\",\"field\":\"project_list\",\"type\":{\"label\":\"\\u6570\\u7ec4\\u5217\\u8868\",\"is\":\"el-array\",\"value\":{\"column\":[{\"label\":\"\\u56fe\\u6807\",\"field\":\"icon\",\"type\":{\"label\":\"\\u6587\\u672c\",\"is\":\"el-input\",\"width\":\"200px\",\"value\":\"\"}},{\"label\":\"\\u6807\\u9898\",\"field\":\"title\",\"type\":{\"label\":\"\\u6587\\u672c\",\"is\":\"el-input\",\"width\":\"200px\",\"value\":\"\"}},{\"label\":\"\\u5185\\u5bb9\",\"field\":\"content\",\"type\":{\"label\":\"\\u6587\\u672c\",\"is\":\"el-input\",\"width\":\"200px\",\"value\":\"\"}}],\"table\":[{\"icon\":\"el-icon-reading\",\"title\":\"\\u4e00\\u952e\\u751f\\u6210\",\"content\":\"CURD\\u3001\\u63d2\\u4ef6\\u3001\\u6a21\\u677f\"},{\"icon\":\"el-icon-mouse\",\"title\":\"\\u5e94\\u7528\\u63d2\\u4ef6\",\"content\":\"\\u5b89\\u88c5\\u3001\\u5378\\u8f7d\\u3001\\u5347\\u7ea7\"},{\"icon\":\"el-icon-brush\",\"title\":\"\\u4e3b\\u9898\\u6a21\\u677f\",\"content\":\"\\u5b89\\u88c5\\u3001\\u5378\\u8f7d\\u3001\\u5207\\u6362\"}]}}}]');

CREATE TABLE IF NOT EXISTS `mk_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `sex` tinyint(1) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `password` varchar(60) NOT NULL,
  `cover` varchar(255) NOT NULL COMMENT '头像',
  `describe` varchar(255) NOT NULL COMMENT '签名',
  `birthday` date NOT NULL COMMENT '生日',
  `now_integral` int(11) NOT NULL COMMENT '当前积分',
  `history_integral` int(11) NOT NULL COMMENT '历史积分',
  `balance` decimal(12,2) NOT NULL COMMENT '余额',
  `pay_paasword` char(6) NOT NULL COMMENT '支付密码',
  `login_ip` varchar(15) NOT NULL,
  `login_count` int(11) NOT NULL,
  `login_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  `create_time` datetime NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0屏蔽 1正常',
  `reason` varchar(255) DEFAULT NULL COMMENT '屏蔽原因',
  `hide` int(11) NOT NULL COMMENT '0隐藏 1正常',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mk_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0屏蔽 1正常',
  `integral` int(11) NOT NULL COMMENT '需要多少积分才能到达',
  `default` tinyint(1) NOT NULL COMMENT '1默认（会员注册默认）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
insert  into `mk_user_group`(`id`,`title`,`status`,`integral`,`default`) values 
(1,'VIP1',1,0,1),
(2,'VIP2',1,100,0);

CREATE TABLE IF NOT EXISTS `mk_user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(60) NOT NULL,
  `create_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;