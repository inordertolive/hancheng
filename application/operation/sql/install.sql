/*
Navicat MySQL Data Transfer

Source Server         : 六部服务器
Source Server Version : 50725
Source Host           : 122.114.82.199:3306
Source Database       : php_hncjne_com

Target Server Type    : MYSQL
Target Server Version : 50725
File Encoding         : 65001

Date: 2019-08-19 17:59:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for shop_operation_ads
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_ads`;
CREATE TABLE `mb_operation_ads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '广告名称',
  `content` text COMMENT '广告内容',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `thumb` int(11) DEFAULT '0' COMMENT '广告图',
  `href` varchar(255) DEFAULT NULL COMMENT '链接地址',
  `width` int(11) unsigned DEFAULT '0' COMMENT '宽度',
  `height` int(11) unsigned DEFAULT '0' COMMENT '高度',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告表';

-- ----------------------------
-- Table structure for shop_operation_ads_type
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_ads_type`;
CREATE TABLE `mb_operation_ads_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '分类名称',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='广告分类表';

-- ----------------------------
-- Table structure for shop_operation_article
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_article`;
CREATE TABLE `mb_operation_article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '栏目id',
  `title` varchar(256) NOT NULL DEFAULT '' COMMENT '标题',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `thumb` varchar(255) DEFAULT NULL,
  `view` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '阅读量',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `trash` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '回收站',
  `appuri` varchar(100) NOT NULL DEFAULT '' COMMENT 'APP跳转协议',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文档基础表';

-- ----------------------------
-- Table structure for shop_operation_article_body
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_article_body`;
CREATE TABLE `mb_operation_article_body` (
  `aid` int(11) unsigned NOT NULL COMMENT '文章id',
  `body` text NOT NULL COMMENT '详细内容',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for shop_operation_article_column
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_article_column`;
CREATE TABLE `mb_operation_article_column` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '栏目名称',
  `content` text NOT NULL COMMENT '内容',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `hide` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='栏目表';

-- ----------------------------
-- Table structure for shop_operation_coupon
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_coupon`;
CREATE TABLE `mb_operation_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '优惠券id',
  `name` varchar(255) NOT NULL COMMENT '优惠券名字',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '面值',
  `min_order_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最低使用金额',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券创建时间',
  `valid_day` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '有效天数',
  `stock` int(255) unsigned NOT NULL DEFAULT '0' COMMENT '库总存张数（不变）',
  `last_stock` int(10) NOT NULL COMMENT '剩余张数（递减），为0时不能再领取此优惠券',
  `method` int(255) unsigned NOT NULL DEFAULT '0' COMMENT '领取方式0手动发放1首页弹窗2被动领取',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态0未开启1可领取2已领完',
  `content` tinytext COMMENT '优惠券内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='优惠券';

-- ----------------------------
-- Table structure for shop_operation_coupon_record
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_coupon_record`;
CREATE TABLE `mb_operation_coupon_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '领取人id',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '有效开始时间或领取时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束使用时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '是否使用0过期1未使用2占用中3已使用4已失效',
  `use_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  `order_sn` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券领取记录';

-- ----------------------------
-- Table structure for shop_operation_message
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_message`;
CREATE TABLE `mb_operation_message` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `model` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据模型',
  `form_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发送人user_id',
  `to_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '接收人user_id',
  `title` varchar(128) NOT NULL DEFAULT '' COMMENT '消息标题',
  `content` varchar(256) NOT NULL DEFAULT '' COMMENT '消息内容',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '阅读状态',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `msgid` varchar(128) NOT NULL DEFAULT '' COMMENT '消息ID',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站内信模型扩展表';

-- ----------------------------
-- Table structure for shop_operation_message_push
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_message_push`;
CREATE TABLE `mb_operation_message_push` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `model` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据模型',
  `type` varchar(128) NOT NULL DEFAULT '' COMMENT '任务类型 如message站内信 umpush 友盟',
  `data` text NOT NULL COMMENT '任务数据包JSON字符串',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '任务执行状态',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建时间',
  `push_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行时间',
  `form_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人ID',
  `errmsg` varchar(800) NOT NULL DEFAULT '' COMMENT '错误日志',
  `push_end_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行结束时间',
  `action` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务子类型',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息推送队列模型扩展表';

-- ----------------------------
-- Table structure for shop_operation_nav
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_nav`;
CREATE TABLE `mb_operation_nav` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `typeid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '导航名称',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `thumb` int(11) DEFAULT '0' COMMENT '导航图',
  `href` varchar(255) DEFAULT NULL COMMENT '链接地址',
  `sort` int(11) unsigned NOT NULL DEFAULT '50' COMMENT '排序',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `typeid` (`typeid`) USING BTREE COMMENT '根据typeid查询'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='导航表';

-- ----------------------------
-- Table structure for shop_operation_nav_type
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_nav_type`;
CREATE TABLE `mb_operation_nav_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '导航位名称',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='导航位表';

-- ----------------------------
-- Table structure for shop_operation_service
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service`;
CREATE TABLE `mb_operation_service` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'aid',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `nickname` varchar(256) NOT NULL DEFAULT '' COMMENT '客服昵称',
  `username` varchar(256) NOT NULL DEFAULT '' COMMENT '客服账号',
  `password` varchar(128) NOT NULL DEFAULT '' COMMENT '登录密码',
  `group` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '所属分组',
  `avatar` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '客服头像',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='客服列表';

-- ----------------------------
-- Table structure for shop_operation_service_chat
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service_chat`;
CREATE TABLE `mb_operation_service_chat` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'aid',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `from_id` varchar(11) NOT NULL DEFAULT '0' COMMENT '发送者id',
  `from_name` varchar(256) NOT NULL DEFAULT '' COMMENT '发送者姓名',
  `from_avatar` varchar(256) NOT NULL DEFAULT '' COMMENT '发送者头像',
  `to_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '接收人id',
  `to_name` varchar(256) NOT NULL DEFAULT '' COMMENT '接收人姓名',
  `content` varchar(256) NOT NULL DEFAULT '' COMMENT '发送内容',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COMMENT='客服聊天记录';

-- ----------------------------
-- Table structure for shop_operation_service_data
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service_data`;
CREATE TABLE `mb_operation_service_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_talking` int(5) NOT NULL DEFAULT '0' COMMENT '正在咨询的人数',
  `in_queue` int(5) NOT NULL DEFAULT '0' COMMENT '排队等待的人数',
  `online_kf` int(5) NOT NULL COMMENT '在线客服数',
  `success_in` int(5) NOT NULL COMMENT '成功接入用户',
  `total_in` int(5) NOT NULL COMMENT '今日累积接入的用户',
  `add_date` varchar(10) NOT NULL COMMENT '写入的日期',
  `add_hour` varchar(2) NOT NULL COMMENT '写入的小时数',
  `add_minute` varchar(2) NOT NULL COMMENT '写入的分钟数',
  PRIMARY KEY (`id`),
  KEY `add_date,add_hour` (`add_date`,`add_hour`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=909 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for shop_operation_service_group
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service_group`;
CREATE TABLE `mb_operation_service_group` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'aid',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `name` varchar(256) NOT NULL DEFAULT '' COMMENT '分组名称',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='客户分组';

-- ----------------------------
-- Table structure for shop_operation_service_log
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service_log`;
CREATE TABLE `mb_operation_service_log` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'aid',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `kf_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '客服id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `user_name` varchar(256) NOT NULL COMMENT '会员名称',
  `client_id` varchar(256) NOT NULL DEFAULT '' COMMENT '客户端id',
  `end_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '咨询分组',
  `user_avatar` varchar(256) NOT NULL COMMENT '会员头像',
  `user_ip` varchar(256) NOT NULL DEFAULT '' COMMENT '用户IP',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COMMENT='客服服务记录';

-- ----------------------------
-- Table structure for shop_operation_service_now_data
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service_now_data`;
CREATE TABLE `mb_operation_service_now_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_talking` int(5) NOT NULL DEFAULT '0' COMMENT '正在咨询的人数',
  `in_queue` int(5) NOT NULL DEFAULT '0' COMMENT '排队等待的人数',
  `online_kf` int(5) NOT NULL COMMENT '在线客服数',
  `success_in` int(5) NOT NULL COMMENT '成功接入用户',
  `total_in` int(5) NOT NULL COMMENT '今日累积接入的用户',
  `now_date` varchar(10) NOT NULL COMMENT '当前日期',
  PRIMARY KEY (`id`),
  KEY `now_date` (`now_date`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for shop_operation_service_reply
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service_reply`;
CREATE TABLE `mb_operation_service_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL COMMENT '自动回复的内容',
  `status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否自动回复',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for shop_operation_service_words
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_service_words`;
CREATE TABLE `mb_operation_service_words` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'aid',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `body` varchar(256) NOT NULL DEFAULT '' COMMENT '内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='客服常用词';

-- ----------------------------
-- Table structure for shop_operation_system_message
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_system_message`;
CREATE TABLE `mb_operation_system_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `to_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '指定接收人ID',
  `content` varchar(256) NOT NULL DEFAULT '' COMMENT '消息内容',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否到达',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `event` varchar(128) NOT NULL DEFAULT '' COMMENT '事件',
  `is_all` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是群发消息',
  `msgtype` tinyint(1) NOT NULL DEFAULT '1' COMMENT '消息类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32277 DEFAULT CHARSET=utf8mb4 COMMENT='系统消息模型扩展表';

-- ----------------------------
-- Table structure for shop_operation_system_message_read
-- ----------------------------
DROP TABLE IF EXISTS `mb_operation_system_message_read`;
CREATE TABLE `mb_operation_system_message_read` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `model` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据模型',
  `sys_msg_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '系统消息ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '读取时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统消息读阅读状态表模型扩展表';


-- -----------------------------
-- 接口 `mb_admin_api_list`
-- -----------------------------
INSERT INTO `mb_admin_api_list` (`apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('ads/get_ads', '5c94aa1a043e7', 'operation', '1', '0', '1', '1', '获取指定广告位的广告列表', '1', '{\r\n	\"code\": \"1\",\r\n	\"info\": \"请求成功\",\r\n	\"data\": [{\r\n		\"thumb\": \"http://live.zzqcnz.com/uploads/images/20190407/46c8f19bea85d5b6159f04ebc36b00da.jpg\",\r\n		\"href\": \"\",\r\n		\"width\": 0,\r\n		\"height\": 0,\r\n		\"name\": \"第一张\"\r\n	}],\r\n	\"user\": \"\"\r\n}', '1553247129');
INSERT INTO `mb_admin_api_list` (`apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('nav/get_nav', '5c98e475427d2', 'operation', '1', '0', '1', '1', '获取指定导航位的导航菜单', '1', '', '1553523873');
INSERT INTO `mb_admin_api_list` (`apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('Message/getSystemMsgList', '5cc56966e9287', 'operation', '1', '1', '1', '1', '获取系统消息/站内信', '0', '', '1556441470');
INSERT INTO `mb_admin_api_list` (`apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('Message/delSystemMsg', '5cc56bffbfe7a', 'operation', '1', '1', '1', '1', '删除系统消息/站内信', '0', '', '1556442135');
-- -----------------------------
-- 接口字段 `mb_admin_api_fields`
-- -----------------------------
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('typeid', '5c94aa1a043e7', '1', '', '1', '', 'typeid【分类id。类型：int(11) unsigned】', '0', 'typeid');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('thumb', '5c94aa1a043e7', '1', '', '1', '', 'thumb【广告图。类型：int(11)】', '1', 'thumb');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('href', '5c94aa1a043e7', '1', '', '1', '', 'href【链接地址。类型：varchar(255)】', '1', 'href');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('width', '5c94aa1a043e7', '1', '', '1', '', 'width【宽度。类型：int(11) unsigned】', '1', 'width');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('height', '5c94aa1a043e7', '1', '', '1', '', 'height【高度。类型：int(11) unsigned】', '1', 'height');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('name', '5c94aa1a043e7', '1', '', '1', '', 'name【广告名称。类型：varchar(60)】', '1', 'name');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('typeid', '5c98e475427d2', '1', '', '1', '', 'typeid【分类id。类型：int(11) unsigned】', '0', 'typeid');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('name', '5c98e475427d2', '2', '', '1', '', 'name【导航名称。类型：varchar(60)】', '1', 'name');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('thumb', '5c98e475427d2', '2', '', '1', '', 'thumb【导航图。类型：int(11)】', '1', 'thumb');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('href', '5c98e475427d2', '2', '', '1', '', 'href【链接地址。类型：varchar(255)】', '1', 'href');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('page', '5cc56966e9287', '1', '1', '0', '', '页码', '0', 'page');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('create_time', '5cc56966e9287', '2', '', '1', '', '创建时间', '1', 'create_time');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('content', '5cc56966e9287', '2', '', '1', '', '消息内容', '1', 'content');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('event', '5cc56966e9287', '2', '', '1', '', '事件', '1', 'event');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('is_read', '5cc56966e9287', '1', '', '1', '', '是否已读', '1', 'is_read');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('id', '5cc56966e9287', '1', '', '1', '', '系统消息ID', '1', 'id');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('msgtype', '5cc56966e9287', '1', '', '1', '', '消息类型', '1', 'msgtype');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('msgtype', '5cc56966e9287', '1', '0', '0', '', '消息类型', '0', 'msgtype');
INSERT INTO `mb_admin_api_fields` (`fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('id', '5cc56bffbfe7a', '1', '', '1', '', 'id【系统消息id。类型：int(11) unsigned】', '0', 'id');
