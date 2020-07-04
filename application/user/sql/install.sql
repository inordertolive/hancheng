-- -----------------------------
-- 导出时间 `2019-08-09 16:23:44`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user`;
CREATE TABLE `mb_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_name` varchar(256) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(256) NOT NULL DEFAULT '' COMMENT '密码',
  `user_nickname` varchar(256) NOT NULL DEFAULT '' COMMENT '昵称',
  `user_type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '会员类型0普通会员1白银会员2黄金会员',
  `user_level` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员等级',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `head_img` varchar(256) NOT NULL DEFAULT '' COMMENT '头像',
  `sex` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `user_email` varchar(255) NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `birthday` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '生日',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录ip',
  `user_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员余额',
  `user_virtual_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '会员虚拟币',
  `total_consumption_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总消费金额',
  `total_consumption_virtual_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总消费虚拟币',
  `total_revenue_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总收入金额',
  `total_revenue_virtual_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总收入虚拟币',
  `freeze_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `score` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员积分',
  `count_score` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '累计获取积分',
  `wechat_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '绑定的微信表id',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `invite_code` varchar(256) NOT NULL DEFAULT '' COMMENT '会员邀请码',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk-mobile` (`mobile`) USING BTREE,
  KEY `idx-id` (`id`,`user_name`(255),`mobile`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='会员主表';

-- -----------------------------
-- 表数据 `mb_user`
-- -----------------------------
INSERT INTO `mb_user` VALUES ('1', '1', '1', '星辰', '1', '0', '', '', '0', '', '0', '0', '0', '23.00', '9.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '', '1');

-- -----------------------------
-- 表结构 `mb_user_address`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_address`;
CREATE TABLE `mb_user_address` (
  `address_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员昵称',
  `model` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据模型',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `name` varchar(128) NOT NULL COMMENT '姓名',
  `mobile` varchar(128) NOT NULL COMMENT '收货电话',
  `address` varchar(1000) NOT NULL COMMENT '收货人地址',
  `is_default` varchar(32) NOT NULL DEFAULT '1' COMMENT '是否默认',
  `province` varchar(128) NOT NULL COMMENT '省',
  `city` varchar(128) NOT NULL COMMENT '市',
  `district` varchar(128) NOT NULL COMMENT '区',
  `detailInfo` varchar(128) NOT NULL COMMENT '短地址',
  `postal_code` int(11) unsigned DEFAULT NULL COMMENT '邮政编码',
  `national_code` int(11) unsigned DEFAULT NULL COMMENT '国家编码',
  PRIMARY KEY (`address_id`),
  KEY `index_uid` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员会员地址模型表';

-- -----------------------------
-- 表数据 `mb_user_address`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_certified`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_certified`;
CREATE TABLE `mb_user_certified` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `auth_type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '认证类型1实名认证2企业认证',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '姓名',
  `card_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '证件类型(1身份证，2护照，3营业执照)',
  `card_no` varchar(128) NOT NULL DEFAULT '' COMMENT '证件号码',
  `card_img` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '证件照/身份证正面',
  `evidence` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '证明材料/身份证反面',
  `mechanism_name` varchar(128) NOT NULL DEFAULT '' COMMENT '企业名称',
  `business_license` varchar(50) NOT NULL DEFAULT '' COMMENT '营业执照',
  `business_id` varchar(128) NOT NULL DEFAULT '' COMMENT '营业执照号',
  `reason` varchar(128) NOT NULL DEFAULT '' COMMENT '拒绝原因',
  `current_type` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已完成认证类型',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '状态0待审核1已通过2已拒绝',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_uid` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='认证表模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_certified`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_follow`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_follow`;
CREATE TABLE `mb_user_follow` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关注人id',
  `fuid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '被关注id',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关注时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`user_id`,`fuid`) USING BTREE,
  KEY `uid_2` (`user_id`) USING BTREE,
  KEY `tid` (`fuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='关注表模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_follow`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_info`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_info`;
CREATE TABLE `mb_user_info` (
  `user_id` int(11) NOT NULL,
  `hobby` varchar(256) NOT NULL DEFAULT '' COMMENT '爱好',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '会员标签，请使用英文逗号,分隔',
  `autograph` varchar(255) NOT NULL DEFAULT '' COMMENT '会员签名',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uk-uid` (`user_id`) USING BTREE COMMENT '唯一uid索引'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------
-- 表数据 `mb_user_info`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_label`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_label`;
CREATE TABLE `mb_user_label` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `type_name` varchar(256) NOT NULL DEFAULT '' COMMENT '分类名',
  `value` varchar(256) NOT NULL DEFAULT '' COMMENT '属性值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='会员标签表';

-- -----------------------------
-- 表数据 `mb_user_label`
-- -----------------------------
INSERT INTO `mb_user_label` VALUES ('1', '1555298427', '1555298427', '1', '分类1', '分类2,分类3,分类4');

-- -----------------------------
-- 表结构 `mb_user_level`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_level`;
CREATE TABLE `mb_user_level` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(256) NOT NULL DEFAULT '' COMMENT '等级名称',
  `upgrade_score` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '升级所需分数',
  `levelid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '等级标识',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COMMENT='会员等级';

-- -----------------------------
-- 表数据 `mb_user_level`
-- -----------------------------
INSERT INTO `mb_user_level` VALUES ('1', '1级', '10', '1', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('2', '2级', '20', '2', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('3', '3级', '30', '3', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('4', '4级', '41', '4', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('5', '5级', '55', '5', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('6', '6级', '73', '6', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('7', '7级', '99', '7', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('8', '8级', '138', '8', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('9', '9级', '194', '9', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('10', '10级', '276', '10', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('11', '11级', '392', '11', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('12', '12级', '551', '12', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('13', '13级', '764', '13', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('14', '14级', '1046', '14', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('15', '15级', '1410', '15', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('16', '16级', '1872', '16', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('17', '17级', '2452', '17', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('18', '18级', '3169', '18', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('19', '19级', '4044', '19', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('20', '20级', '5103', '20', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('21', '21级', '6370', '21', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('22', '22级', '7874', '22', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('23', '23级', '9644', '23', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('24', '24级', '11713', '24', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('25', '25级', '14116', '25', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('26', '26级', '16888', '26', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('27', '27级', '20068', '27', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('28', '28级', '23699', '28', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('29', '29级', '27823', '29', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('30', '30级', '32486', '30', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('31', '31级', '37737', '31', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('32', '32级', '43627', '32', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('33', '33级', '50209', '33', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('34', '34级', '57539', '34', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('35', '35级', '65675', '35', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('36', '36级', '74680', '36', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('37', '37级', '84615', '37', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('38', '38级', '95549', '38', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('39', '39级', '107551', '39', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('40', '40级', '120692', '40', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('41', '41级', '135048', '41', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('42', '42级', '150695', '42', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('43', '43级', '167716', '43', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('44', '44级', '186192', '44', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('45', '45级', '206212', '45', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('46', '46级', '227863', '46', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('47', '47级', '251238', '47', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('48', '48级', '276433', '48', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('49', '49级', '303546', '49', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('50', '50级', '332679', '50', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('51', '51级', '363936', '51', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('52', '52级', '397426', '52', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('53', '53级', '433258', '53', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('54', '54级', '471547', '54', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('55', '55级', '512411', '55', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('56', '56级', '555970', '56', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('57', '57级', '602349', '57', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('58', '58级', '651673', '58', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('59', '59级', '704075', '59', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('60', '60级', '759687', '60', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('61', '61级', '818647', '61', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('62', '62级', '881096', '62', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('63', '63级', '947179', '63', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('64', '64级', '1017042', '64', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('65', '65级', '1090837', '65', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('66', '66级', '1168718', '66', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('67', '67级', '1250844', '67', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('68', '68级', '1337377', '68', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('69', '69级', '1428481', '69', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('70', '70级', '1524326', '70', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('71', '71级', '1625085', '71', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('72', '72级', '1730933', '72', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('73', '73级', '1842051', '73', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('74', '74级', '1958623', '74', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('75', '75级', '2080835', '75', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('76', '76级', '2208880', '76', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('77', '77级', '2342951', '77', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('78', '78级', '2483248', '78', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('79', '79级', '2629974', '79', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('80', '80级', '2783334', '80', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('81', '81级', '2943540', '81', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('82', '82级', '3110805', '82', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('83', '83级', '3285348', '83', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('84', '84级', '3467390', '84', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('85', '85级', '3657158', '85', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('86', '86级', '3854882', '86', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('87', '87级', '4060796', '87', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('88', '88级', '4275137', '88', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('89', '89级', '4498149', '89', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('90', '90级', '4730077', '90', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('91', '91级', '4971171', '91', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('92', '92级', '5221686', '92', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('93', '93级', '5481880', '93', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('94', '94级', '5752016', '94', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('95', '95级', '6032361', '95', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('96', '96级', '6323186', '96', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('97', '97级', '6624767', '97', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('98', '98级', '6937381', '98', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('99', '99级', '7261315', '99', '0', '0', '1');
INSERT INTO `mb_user_level` VALUES ('100', '100级', '7596854', '100', '0', '0', '1');

-- -----------------------------
-- 表结构 `mb_user_level_votes`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_level_votes`;
CREATE TABLE `mb_user_level_votes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `name` varchar(128) NOT NULL DEFAULT '0' COMMENT '等级名称',
  `upgrade_score` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '升级所需分数',
  `levelid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '等级值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COMMENT='会员收入等级模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_level_votes`
-- -----------------------------
INSERT INTO `mb_user_level_votes` VALUES ('1', '1534229391', '1级', '10', '1');
INSERT INTO `mb_user_level_votes` VALUES ('2', '1534229391', '2级', '20', '2');
INSERT INTO `mb_user_level_votes` VALUES ('3', '1534229391', '3级', '30', '3');
INSERT INTO `mb_user_level_votes` VALUES ('4', '1534229391', '4级', '42', '4');
INSERT INTO `mb_user_level_votes` VALUES ('5', '1534229391', '5级', '60', '5');
INSERT INTO `mb_user_level_votes` VALUES ('6', '1534229391', '6级', '91', '6');
INSERT INTO `mb_user_level_votes` VALUES ('7', '1534229391', '7级', '148', '7');
INSERT INTO `mb_user_level_votes` VALUES ('8', '1534229391', '8级', '248', '8');
INSERT INTO `mb_user_level_votes` VALUES ('9', '1534229391', '9级', '418', '9');
INSERT INTO `mb_user_level_votes` VALUES ('10', '1534229391', '10级', '690', '10');
INSERT INTO `mb_user_level_votes` VALUES ('11', '1534229391', '11级', '1110', '11');
INSERT INTO `mb_user_level_votes` VALUES ('12', '1534229391', '12级', '1731', '12');
INSERT INTO `mb_user_level_votes` VALUES ('13', '1534229391', '13级', '2618', '13');
INSERT INTO `mb_user_level_votes` VALUES ('14', '1534229391', '14级', '3853', '14');
INSERT INTO `mb_user_level_votes` VALUES ('15', '1534229391', '15级', '5528', '15');
INSERT INTO `mb_user_level_votes` VALUES ('16', '1534229391', '16级', '7754', '16');
INSERT INTO `mb_user_level_votes` VALUES ('17', '1534229391', '17级', '10656', '17');
INSERT INTO `mb_user_level_votes` VALUES ('18', '1534229391', '18级', '14379', '18');
INSERT INTO `mb_user_level_votes` VALUES ('19', '1534229391', '19级', '19086', '19');
INSERT INTO `mb_user_level_votes` VALUES ('20', '1534229391', '20级', '24961', '20');
INSERT INTO `mb_user_level_votes` VALUES ('21', '1534229391', '21级', '32210', '21');
INSERT INTO `mb_user_level_votes` VALUES ('22', '1534229391', '22级', '41061', '22');
INSERT INTO `mb_user_level_votes` VALUES ('23', '1534229391', '23级', '51766', '23');
INSERT INTO `mb_user_level_votes` VALUES ('24', '1534229391', '24级', '64603', '24');
INSERT INTO `mb_user_level_votes` VALUES ('25', '1534229391', '25级', '79876', '25');
INSERT INTO `mb_user_level_votes` VALUES ('26', '1534229391', '26级', '97916', '26');
INSERT INTO `mb_user_level_votes` VALUES ('27', '1534229391', '27级', '119084', '27');
INSERT INTO `mb_user_level_votes` VALUES ('28', '1534229391', '28级', '143769', '28');
INSERT INTO `mb_user_level_votes` VALUES ('29', '1534229391', '29级', '172394', '29');
INSERT INTO `mb_user_level_votes` VALUES ('30', '1534229391', '30级', '205411', '30');
INSERT INTO `mb_user_level_votes` VALUES ('31', '1534229391', '31级', '243310', '31');
INSERT INTO `mb_user_level_votes` VALUES ('32', '1534229391', '32级', '286612', '32');
INSERT INTO `mb_user_level_votes` VALUES ('33', '1534229391', '33级', '335874', '33');
INSERT INTO `mb_user_level_votes` VALUES ('34', '1534229391', '34级', '391694', '34');
INSERT INTO `mb_user_level_votes` VALUES ('35', '1534229391', '35级', '454704', '35');
INSERT INTO `mb_user_level_votes` VALUES ('36', '1534229391', '36级', '525579', '36');
INSERT INTO `mb_user_level_votes` VALUES ('37', '1534229391', '37级', '605032', '37');
INSERT INTO `mb_user_level_votes` VALUES ('38', '1534229391', '38级', '693820', '38');
INSERT INTO `mb_user_level_votes` VALUES ('39', '1534229391', '39级', '792742', '39');
INSERT INTO `mb_user_level_votes` VALUES ('40', '1534229391', '40级', '902642', '40');
INSERT INTO `mb_user_level_votes` VALUES ('41', '1534229391', '41级', '1024410', '41');
INSERT INTO `mb_user_level_votes` VALUES ('42', '1534229391', '42级', '1158982', '42');
INSERT INTO `mb_user_level_votes` VALUES ('43', '1534229391', '43级', '1307342', '43');
INSERT INTO `mb_user_level_votes` VALUES ('44', '1534229391', '44级', '1470524', '44');
INSERT INTO `mb_user_level_votes` VALUES ('45', '1534229391', '45级', '1649612', '45');
INSERT INTO `mb_user_level_votes` VALUES ('46', '1534229391', '46级', '1845741', '46');
INSERT INTO `mb_user_level_votes` VALUES ('47', '1534229391', '47级', '2060100', '47');
INSERT INTO `mb_user_level_votes` VALUES ('48', '1534229391', '48级', '2293930', '48');
INSERT INTO `mb_user_level_votes` VALUES ('49', '1534229391', '49级', '2548530', '49');
INSERT INTO `mb_user_level_votes` VALUES ('50', '1534229391', '50级', '2825252', '50');
INSERT INTO `mb_user_level_votes` VALUES ('51', '1534229391', '51级', '3125510', '51');
INSERT INTO `mb_user_level_votes` VALUES ('52', '1534229391', '52级', '3450773', '52');
INSERT INTO `mb_user_level_votes` VALUES ('53', '1534229391', '53级', '3802570', '53');
INSERT INTO `mb_user_level_votes` VALUES ('54', '1534229391', '54级', '4182495', '54');
INSERT INTO `mb_user_level_votes` VALUES ('55', '1534229391', '55级', '4592200', '55');
INSERT INTO `mb_user_level_votes` VALUES ('56', '1534229391', '56级', '5033404', '56');
INSERT INTO `mb_user_level_votes` VALUES ('57', '1534229391', '57级', '5507888', '57');
INSERT INTO `mb_user_level_votes` VALUES ('58', '1534229391', '58级', '6017501', '58');
INSERT INTO `mb_user_level_votes` VALUES ('59', '1534229391', '59级', '6564158', '59');
INSERT INTO `mb_user_level_votes` VALUES ('60', '1534229391', '60级', '7149843', '60');
INSERT INTO `mb_user_level_votes` VALUES ('61', '1534229391', '61级', '7776610', '61');
INSERT INTO `mb_user_level_votes` VALUES ('62', '1534229391', '62级', '8446583', '62');
INSERT INTO `mb_user_level_votes` VALUES ('63', '1534229391', '63级', '9161958', '63');
INSERT INTO `mb_user_level_votes` VALUES ('64', '1534229391', '64级', '9925005', '64');
INSERT INTO `mb_user_level_votes` VALUES ('65', '1534229391', '65级', '10738068', '65');
INSERT INTO `mb_user_level_votes` VALUES ('66', '1534229391', '66级', '11603566', '66');
INSERT INTO `mb_user_level_votes` VALUES ('67', '1534229391', '67级', '12523996', '67');
INSERT INTO `mb_user_level_votes` VALUES ('68', '1534229391', '68级', '13501931', '68');
INSERT INTO `mb_user_level_votes` VALUES ('69', '1534229391', '69级', '14540026', '69');
INSERT INTO `mb_user_level_votes` VALUES ('70', '1534229391', '70级', '15641013', '70');
INSERT INTO `mb_user_level_votes` VALUES ('71', '1534229391', '71级', '16807710', '71');
INSERT INTO `mb_user_level_votes` VALUES ('72', '1534229391', '72级', '18043014', '72');
INSERT INTO `mb_user_level_votes` VALUES ('73', '1534229391', '73级', '19349906', '73');
INSERT INTO `mb_user_level_votes` VALUES ('74', '1534229391', '74级', '20731456', '74');
INSERT INTO `mb_user_level_votes` VALUES ('75', '1534229391', '75级', '22190816', '75');
INSERT INTO `mb_user_level_votes` VALUES ('76', '1534229391', '76级', '23731229', '76');
INSERT INTO `mb_user_level_votes` VALUES ('77', '1534229391', '77级', '25356024', '77');
INSERT INTO `mb_user_level_votes` VALUES ('78', '1534229391', '78级', '27068622', '78');
INSERT INTO `mb_user_level_votes` VALUES ('79', '1534229391', '79级', '28872534', '79');
INSERT INTO `mb_user_level_votes` VALUES ('80', '1534229391', '80级', '30771364', '80');
INSERT INTO `mb_user_level_votes` VALUES ('81', '1534229391', '81级', '32768810', '81');
INSERT INTO `mb_user_level_votes` VALUES ('82', '1534229391', '82级', '34868664', '82');
INSERT INTO `mb_user_level_votes` VALUES ('83', '1534229391', '83级', '37074814', '83');
INSERT INTO `mb_user_level_votes` VALUES ('84', '1534229391', '84级', '39391246', '84');
INSERT INTO `mb_user_level_votes` VALUES ('85', '1534229391', '85级', '41822044', '85');
INSERT INTO `mb_user_level_votes` VALUES ('86', '1534229391', '86级', '44371391', '86');
INSERT INTO `mb_user_level_votes` VALUES ('87', '1534229391', '87级', '47043572', '87');
INSERT INTO `mb_user_level_votes` VALUES ('88', '1534229391', '88级', '49842972', '88');
INSERT INTO `mb_user_level_votes` VALUES ('89', '1534229391', '89级', '52774082', '89');
INSERT INTO `mb_user_level_votes` VALUES ('90', '1534229391', '90级', '55841494', '90');
INSERT INTO `mb_user_level_votes` VALUES ('91', '1534229391', '91级', '59049910', '91');
INSERT INTO `mb_user_level_votes` VALUES ('92', '1534229391', '92级', '62404135', '92');
INSERT INTO `mb_user_level_votes` VALUES ('93', '1534229391', '93级', '65909082', '93');
INSERT INTO `mb_user_level_votes` VALUES ('94', '1534229391', '94级', '69569777', '94');
INSERT INTO `mb_user_level_votes` VALUES ('95', '1534229391', '95级', '73391352', '95');
INSERT INTO `mb_user_level_votes` VALUES ('96', '1534229391', '96级', '77379054', '96');
INSERT INTO `mb_user_level_votes` VALUES ('97', '1534229391', '97级', '81538240', '97');
INSERT INTO `mb_user_level_votes` VALUES ('98', '1534229391', '98级', '85874383', '98');
INSERT INTO `mb_user_level_votes` VALUES ('99', '1534229391', '99级', '90393070', '99');
INSERT INTO `mb_user_level_votes` VALUES ('100', '1534229391', '100级', '95100005', '100');

-- -----------------------------
-- 表结构 `mb_user_money_log`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_money_log`;
CREATE TABLE `mb_user_money_log` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `before_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动前金额',
  `change_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '操作金额',
  `after_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动后金额',
  `change_type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '操作类型',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '变动时间',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `order_no` varchar(128) NOT NULL DEFAULT '' COMMENT '业务流水号',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='会员金额变动表模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_money_log`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_recharge_rule`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_recharge_rule`;
CREATE TABLE `mb_user_recharge_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `group` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据模型',
  `name` varchar(128) NOT NULL DEFAULT '0' COMMENT '规则名称',
  `app_name` varchar(128) NOT NULL DEFAULT '' COMMENT '内购名称',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付金额',
  `add_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '到账金额',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `sort` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '排序值',
  PRIMARY KEY (`id`),
  KEY `app_name` (`app_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='充值规则模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_recharge_rule`
-- -----------------------------
INSERT INTO `mb_user_recharge_rule` VALUES ('1', '1', '4886', 'votes.698', '698.00', '4886.00', '1530100904', '1', '16');
INSERT INTO `mb_user_recharge_rule` VALUES ('2', '0', '60', '', '0.01', '60.00', '1532486862', '1', '1');
INSERT INTO `mb_user_recharge_rule` VALUES ('3', '1', '42', 'votes.6', '0.01', '42.00', '1531450352', '1', '9');
INSERT INTO `mb_user_recharge_rule` VALUES ('4', '1', '280', 'votes.40', '40.00', '280.00', '1531450375', '1', '10');
INSERT INTO `mb_user_recharge_rule` VALUES ('5', '1', '315', 'votes.45', '45.00', '315.00', '1531450391', '1', '11');
INSERT INTO `mb_user_recharge_rule` VALUES ('6', '1', '476', 'votes.68', '68.00', '476.00', '1531450407', '1', '12');
INSERT INTO `mb_user_recharge_rule` VALUES ('7', '1', '686', 'votes.98', '98.00', '686.00', '1532081640', '1', '13');
INSERT INTO `mb_user_recharge_rule` VALUES ('9', '0', '400', '', '40.00', '400.00', '1532486875', '1', '2');
INSERT INTO `mb_user_recharge_rule` VALUES ('10', '0', '450', '', '45.00', '450.00', '1532486886', '1', '3');
INSERT INTO `mb_user_recharge_rule` VALUES ('11', '0', '680', '', '68.00', '680.00', '1532486896', '1', '4');
INSERT INTO `mb_user_recharge_rule` VALUES ('12', '0', '980', '', '98.00', '980.00', '1532486906', '1', '5');
INSERT INTO `mb_user_recharge_rule` VALUES ('13', '0', '1980', '', '198.00', '1980.00', '1532486920', '1', '6');
INSERT INTO `mb_user_recharge_rule` VALUES ('14', '0', '2980', '', '298.00', '2980.00', '1532486906', '1', '7');
INSERT INTO `mb_user_recharge_rule` VALUES ('15', '0', '6980', '', '698.00', '6980.00', '1532486920', '1', '8');
INSERT INTO `mb_user_recharge_rule` VALUES ('16', '1', '1386', 'votes.188', '198.00', '1386.00', '1531450407', '1', '14');
INSERT INTO `mb_user_recharge_rule` VALUES ('17', '1', '2086', 'votes.298', '298.00', '2086.00', '1532081640', '1', '15');

-- -----------------------------
-- 表结构 `mb_user_score_log`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_score_log`;
CREATE TABLE `mb_user_score_log` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `before_score` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动前金额',
  `change_score` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '操作金额',
  `after_score` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动后金额',
  `change_type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '操作类型 1 充值 2打赏消费3红包收入4发红包退还',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '变动时间',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `order_no` varchar(128) NOT NULL DEFAULT '' COMMENT '业务流水号',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员积分变动表模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_score_log`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_signin`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_signin`;
CREATE TABLE `mb_user_signin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `days` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '连续签到的天数',
  `is_sign` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否签到过',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '签到的时间',
  PRIMARY KEY (`id`),
  KEY `index_uid` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户签到统计表';

-- -----------------------------
-- 表数据 `mb_user_signin`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_signin_log`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_signin_log`;
CREATE TABLE `mb_user_signin_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `days` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '连续签到的天数',
  `integral` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赠送积分',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '签到的时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态0无效1正常',
  PRIMARY KEY (`id`),
  KEY `index_uid` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到记录表';

-- -----------------------------
-- 表数据 `mb_user_signin_log`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_suggestions`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_suggestions`;
CREATE TABLE `mb_user_suggestions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `title` varchar(256) NOT NULL DEFAULT '' COMMENT '投诉建议类型名称',
  `body` varchar(1000) NOT NULL DEFAULT '' COMMENT '内容',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投诉建议';

-- -----------------------------
-- 表数据 `mb_user_suggestions`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_vip`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_vip`;
CREATE TABLE `mb_user_vip` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'aid',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `name` varchar(256) NOT NULL DEFAULT '' COMMENT 'vip名称',
  `thumb` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'vip图片',
  `month_price` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'vip月价格',
  `season_price` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'vip季价格',
  `year_price` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'vip年价格',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='VIP规则';

-- -----------------------------
-- 表数据 `mb_user_vip`
-- -----------------------------
INSERT INTO `mb_user_vip` VALUES ('1', '1565059621', '1565059621', '1', '白银会员', '39', '10', '30', '100');
INSERT INTO `mb_user_vip` VALUES ('2', '1565060570', '1565060570', '1', '黄金会员', '39', '20', '60', '200');
INSERT INTO `mb_user_vip` VALUES ('3', '1565323986', '1565323986', '1', '钻石会员', '39', '20', '60', '200');

-- -----------------------------
-- 表结构 `mb_user_virtual_log`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_virtual_log`;
CREATE TABLE `mb_user_virtual_log` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `before_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动前金额',
  `change_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '操作金额',
  `after_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动后金额',
  `change_type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '操作类型 1 充值 2打赏消费3红包收入4发红包退还',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '变动时间',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `order_no` varchar(128) NOT NULL DEFAULT '' COMMENT '业务流水号',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='会员金额变动表模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_virtual_log`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_withdraw`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_withdraw`;
CREATE TABLE `mb_user_withdraw` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `model` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据模型',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_no` varchar(128) NOT NULL DEFAULT '' COMMENT '提现流水号',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  `true_name` varchar(128) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `check_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态',
  `cash_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '付款状态',
  `check_reason` varchar(128) NOT NULL DEFAULT '' COMMENT '审核失败原因',
  `cash_reason` varchar(128) NOT NULL DEFAULT '' COMMENT '付款异常原因',
  `check_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `cash_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '付款时间',
  `account_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '账户类型 1 微信 2 支付宝 3 银行卡',
  `account_id` varchar(128) NOT NULL DEFAULT '' COMMENT '提现账户 微信UNIONID  支付宝账户 银行卡号',
  `cash_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `poundage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `pay_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '到账金额',
  `order_id` varchar(128) NOT NULL DEFAULT '' COMMENT '第三方订单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现表模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_withdraw`
-- -----------------------------

-- -----------------------------
-- 表结构 `mb_user_withdraw_account`
-- -----------------------------
DROP TABLE IF EXISTS `mb_user_withdraw_account`;
CREATE TABLE `mb_user_withdraw_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档id',
  `model` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据模型',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `true_name` varchar(128) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `account_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '账户类型 1 微信 2 支付宝 3 银行卡',
  `account_id` varchar(128) NOT NULL DEFAULT '' COMMENT '提现账户 微信UNIONID 支付宝账户 银行卡号',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '设为默认',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现账户表模型扩展表';

-- -----------------------------
-- 表数据 `mb_user_withdraw_account`
-- -----------------------------

-- -----------------------------
-- 增加接口
-- -----------------------------
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('68', 'user/userSignin', '5caf00505dd00', 'user', '1', '1', '1', '1', '会员签到', '0', '', '1554972771');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('70', 'user/get_user_info', '5c78c4772da97', 'user', '1', '1', '1', '1', '获取会员详细信息', '0', '{\r\n    \"code\": \"1\",\r\n    \"info\": \"请求成功\",\r\n    \"data\": {\r\n        \"easemob\": \"317812559bfe09f8cf22ce0df868f9e3\",\r\n        \"hospital\": \"\",\r\n        \"professional\": \"\",\r\n        \"background\": \"http://192.168.2.134:105/uploads/images/20190517/14170671712.jpg\",//背景图\r\n        \"avatar_id\": 0,\r\n        \"invitation_code\": \"IC0036\",\r\n        \"id\": 36,//用户id\r\n        \"nickname\": \"昵称、\",//昵称\r\n        \"user_type\": 1,//用户类型  0：用户   1：主播    4：机构\r\n        \"avatar\": \"http://192.168.2.134:105/uploads/images/093642327837.jpg\",//头像\r\n        \"sex\": 1,//性别   \r\n        \"birthday\": 915159662,\r\n        \"hobby\": \"听音乐，看书，玩游戏\",//爱好\r\n        \"mobile\": \"151****0552\",//手机号\r\n        \"last_login_time\": 0,\r\n        \"last_login_ip\": 0,\r\n        \"user_money\": \"4900.00\",//账户余额\r\n        \"total_consumption_money\": \"0.00\",//总消费金额\r\n        \"total_revenue_money\": \"0.00\",//总收益金额\r\n        \"user_bobi\": \"0.00\",//播币\r\n        \"total_consumption_bobi\": \"0.00\",//总消费播币\r\n        \"votes_total\": \"0.00\",//打赏总收入\r\n        \"user_integral\": 0,//积分\r\n        \"count_integral\": 0,//累计获取积分\r\n        \"user_level\": 0,//会员等级\r\n        \"create_time\": 1557540480,\r\n        \"update_time\": 1558075036,\r\n        \"is_link_mic\": 1,//是否有连麦权限\r\n        \"is_recording\": 1,//是否有录制限制\r\n        \"tags\": [//会员标签\r\n            \"分类2\",\r\n            \"分类3\"\r\n        ],\r\n        \"autograph\": \"\",//签名\r\n        \"status\": 1,\r\n        \"invite_code\": \"\",//邀请码\r\n        \"divide_into\": 0,//分成比例\r\n        \"uuid\": \"697ad7750d07a040b348e20444612764\",//登陆设备id\r\n        \"is_follow\": 0,//是否关注  1：已关注   0：未关注\r\n        \"follow\": 2,//关注人数\r\n        \"fans\": 0,//粉丝人数\r\n        \"video\": 2,//视频数量\r\n        \"photo_list\": [//相册\r\n            \"http://192.168.2.134:105/uploads/add_diary_pictures/20190514/164520540146.jpg\",\r\n            \"http://192.168.2.134:105/uploads/add_diary_pictures/20190514/164520552706.png\",\r\n            \"http://192.168.2.134:105/uploads/add_diary_pictures/20190514/164520576616.jpg\",\r\n            \"http://192.168.2.134:105/uploads/add_diary_pictures/112512627998.jpg\",\r\n            \"http://192.168.2.134:105/uploads/add_diary_pictures/112512730887.jpg\"\r\n        ],\r\n        \"video_list\": [//视频列表\r\n            {\r\n                \"file_name\": \"马二头\",\r\n                \"image_url\": \"http://1251972944.vod2.myqcloud.com/44c1401dvodtranssh1251972944/8dfaa7e05285890789048991708/1557735073_4149705933.100_0.jpg\",\r\n                \"video_url\": \"http://1251972944.vod2.myqcloud.com/6dd75275vodsh1251972944/8dfaa7e05285890789048991708/lIE52ZacVzYA.mp4\",\r\n                \"play_num\": 1\r\n            },\r\n            {\r\n                \"file_name\": \"7E4E6FA7-777A-4237-A7F7-24C50D0FEB02\",\r\n                \"image_url\": \"http://1251972944.vod2.myqcloud.com/44c1401dvodtranssh1251972944/b18f29915285890789042314773/1557717040_3237680157.100_0.jpg\",\r\n                \"video_url\": \"http://1251972944.vod2.myqcloud.com/6dd75275vodsh1251972944/b18f29915285890789042314773/A7IySSiLEx4A.mp4\",\r\n                \"play_num\": 1\r\n            }\r\n        ],\r\n        \"address\": \"\"//地址\r\n    },\r\n    \"user\": {\r\n        \"id\": 36,\r\n        \"nickname\": \"昵称、\",\r\n        \"avatar\": \"http://192.168.2.134:105/uploads/images/093642327837.jpg\",\r\n        \"sex\": 1,\r\n        \"user_bobi\": \"0.00\",\r\n        \"user_money\": \"4900.00\",\r\n        \"user_integral\": 0,\r\n        \"total_consumption_money\": \"0.00\",\r\n        \"total_revenue_money\": \"0.00\",\r\n        \"votes_total\": \"0.00\",\r\n        \"user_type\": 1,\r\n        \"user_level\": 0,\r\n        \"easemob\": \"317812559bfe09f8cf22ce0df868f9e3\",\r\n        \"status\": 1\r\n    }\r\n}', '1551418528');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('71', 'user/user_name_login', '5c78dbfd977cf', 'user', '1', '0', '1', '1', '用户名密码登录', '0', '{\r\n	\"code\": \"1\",\r\n	\"info\": \"登录成功\",\r\n	\"data\": {\r\n		\"access_token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJMd3dhblBIUCIsImlhdCI6MTU1NTA0NTg0NywibmJmIjoxNTU1MDQ1ODQ3LCJzY29wZXMiOiJyb2xlX2FjY2VzcyIsImV4cCI6MTU1NTA1MzA0NywicGFyYW1zIjp7ImlkIjo3LCJuaWNrbmFtZSI6Ilx1NjYxZlx1OGZiMDMiLCJzdGF0dXMiOjF9fQ.6Y_0MLkJ3uDWh4dVVko82RZNvViEJU8tdm8D3QtVEgk\",\r\n		\"refresh_token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJMd3dhblBIUCIsImlhdCI6MTU1NTA0NTg0NywibmJmIjoxNTU1MDQ1ODQ3LCJzY29wZXMiOiJyb2xlX3JlZnJlc2giLCJleHAiOjE1NTc2Mzc4NDcsInBhcmFtcyI6eyJpZCI6Nywibmlja25hbWUiOiJcdTY2MWZcdThmYjAzIiwic3RhdHVzIjoxfX0.6KQFCN942ZRbdEp9pd2FiPz6718YXWNPuDIciN01Suw\",\r\n		\"token_type\": \"bearer\"\r\n	},\r\n	\"user\": \"\"\r\n}', '1551424535');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('72', 'user/user_mobile_login', '5c78dca45ebc1', 'user', '1', '0', '1', '1', '手机号验证码登录', '0', '{\r\n	\"code\": \"1\",\r\n	\"info\": \"登录成功\",\r\n	\"data\": {\r\n		\"access_token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJMd3dhblBIUCIsImlhdCI6MTU1NTA0NTg0NywibmJmIjoxNTU1MDQ1ODQ3LCJzY29wZXMiOiJyb2xlX2FjY2VzcyIsImV4cCI6MTU1NTA1MzA0NywicGFyYW1zIjp7ImlkIjo3LCJuaWNrbmFtZSI6Ilx1NjYxZlx1OGZiMDMiLCJzdGF0dXMiOjF9fQ.6Y_0MLkJ3uDWh4dVVko82RZNvViEJU8tdm8D3QtVEgk\",\r\n		\"refresh_token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJMd3dhblBIUCIsImlhdCI6MTU1NTA0NTg0NywibmJmIjoxNTU1MDQ1ODQ3LCJzY29wZXMiOiJyb2xlX3JlZnJlc2giLCJleHAiOjE1NTc2Mzc4NDcsInBhcmFtcyI6eyJpZCI6Nywibmlja25hbWUiOiJcdTY2MWZcdThmYjAzIiwic3RhdHVzIjoxfX0.6KQFCN942ZRbdEp9pd2FiPz6718YXWNPuDIciN01Suw\",\r\n		\"token_type\": \"bearer\"\r\n	},\r\n	\"user\": \"\"\r\n}', '1551424690');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('81', 'user/follow', '5cac603d80bd7', 'user', '1', '1', '1', '1', '关注主播/取消关注主播', '0', '关注成功返回：\r\n{\r\n	\"code\": \"1\",\r\n	\"info\": \"关注成功\",\r\n	\"data\": {\r\n		\"follow\": 1\r\n	},\r\n	\"user\": \"\"\r\n}\r\n取消关注返回：\r\n{\r\n	\"code\": \"1\",\r\n	\"info\": \"取消关注成功\",\r\n	\"data\": {\r\n		\"follow\": 0\r\n	},\r\n	\"user\": \"\"\r\n}', '1554800716');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('90', 'user/registerUser', '5cad9f63e4f94', 'user', '1', '0', '1', '1', '会员注册接口', '0', '', '1554882428');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('104', 'user/forgetPassword', '5caeeba9866aa', 'user', '1', '0', '1', '1', '忘记密码找回密码接口', '0', '', '1554967526');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('113', 'user/edit_user_info', '5cb54af125f1c', 'user', '1', '1', '1', '1', '个人资料-个人资料修改', '0', '', '1555385104');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('115', 'Coupon/coupon_list', '5cb5ad18a18fb', 'user', '1', '1', '1', '1', '用户--优惠券--优惠券列表', '0', '{\r\n    \"code\": \"1\",\r\n    \"info\": \"数据信息\",\r\n    \"data\": [\r\n        {\r\n            \"id\": 9, // 用户领取优惠券id值\r\n            \"start_time\": 1553679506,\r\n            \"end_time\": \"2019-04-03 17:38\", //结束时间\r\n            \"status\": 2, //0过期1未使用2占用中3已使用4已失效\r\n            \"money\": \"5.00\", //金额\r\n            \"min_order_money\": \"50.00\", //订单金额有效期\r\n            \"coupon_name\": \"满50减5元优惠券\"  //名称\r\n        }\r\n    ],\r\n    \"user\": \"\"\r\n}', '1555410230');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('116', 'Coupon/coupon_detail', '5cb5aec30095a', 'user', '1', '1', '1', '1', '会员--优惠券--优惠券详情', '0', '{\r\n    \"code\": \"1\",\r\n    \"info\": \"数据信息\",\r\n    \"data\": {\r\n        \"id\": 6, //领取记录id值\r\n        \"end_time\": \"2019-04-03 17:38\", //有效期\r\n        \"status\": 4,  //0过期1未使用2占用中3已使用4已失效\r\n        \"money\": \"5.00\", //优惠券金额\r\n        \"min_order_money\": \"50.00\", //使用条件\r\n        \"coupon_name\": \"满50减5元优惠券\", //优惠券名称\r\n        \"content\": \"\" //优惠券内容\r\n    },\r\n    \"user\": \"\"\r\n}', '1555410664');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('119', 'User/get_user_label', '5cb6957fd4288', 'user', '0', '0', '1', '1', '个人资料标签修改-获取标签列表', '0', '', '1555469765');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('121', 'user/attention', '5cb6c07f79fb8', 'user', '1', '1', '1', '0', '关注和粉丝列表', '0', '{\r\n  \"code\": \"1\",\r\n  \"info\": \"查询成功\",\r\n  \"data\": {\r\n    \"total\": 2,\r\n    \"per_page\": 15,\r\n    \"current_page\": 1,\r\n    \"last_page\": 1,\r\n    \"data\": [\r\n      {\r\n        \"id\": 2,//用户id\r\n        \"autograph\": \"\",//签名\r\n        \"avatar\": \"\",//头像\r\n        \"nickname\": \"星辰1\",//用户名字\r\n        \"create_time\": 1555480308,//时间\r\n        \"fuid\": 2,\r\n        \"uid\": 1\r\n      },\r\n      {\r\n        \"id\": 3,\r\n        \"autograph\": \"\",\r\n        \"avatar\": \"\",\r\n        \"nickname\": \"星辰2\",\r\n        \"create_time\": 1555480308,\r\n        \"fuid\": 3,\r\n        \"uid\": 1\r\n      }\r\n    ]\r\n  },\r\n  \"user\": \"\"\r\n}', '1555481329');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('122', 'UserLabel/get_user_label', '5cb6d0ca3c331', 'user', '0', '1', '1', '1', '个人资料-标签修改-获取标签列表', '0', '{\r\n    \"code\": \"1\",\r\n    \"info\": \"操作成功\",\r\n    \"data\": {\r\n        \"label_list\": [\r\n            {\r\n                \"type_name\": \"分类1\",//标签分类 \r\n                \"value\": [// 分类下的标签名 array\r\n                    \"分类2\",//标签名 string\r\n                    \"分类3\",\r\n                    \"分类4\"\r\n                ]\r\n            },\r\n            {\r\n                \"type_name\": \"分类-2\",\r\n                \"value\": [\r\n                    \"美女\",\r\n                    \"小鲜肉\",\r\n                    \"帅哥\"\r\n                ]\r\n            }\r\n        ],\r\n        \"user_tages\": [//会员现有标签 arrar()\r\n            \"感性\",//现有标签名\r\n            \"诚实\",\r\n            \"守信\",\r\n            \"小鲜肉\"\r\n        ]\r\n    },\r\n    \"user\": \"\"\r\n}', '1555484933');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('123', 'UserLabel/edit_user_label', '5cb6d21e49e76', 'user', '0', '1', '1', '1', '个人资料-标签修改-保存修改', '0', '', '1555485258');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('125', 'money/get_money_detail', '5cb6c3ee60e5f', 'user', '1', '1', '1', '1', '余额明细', '0', '{\r\n    \"code\": \"1\",\r\n    \"info\": \"数据信息\",\r\n    \"data\": [\r\n        {\r\n            \"create_time\": \"2019-04-03 17:50:48\", //时间\r\n            \"remark\": \"系统充值10,操作管理员工号:1\", //描述\r\n            \"change_money\": \"10.00\",//金额\r\n            \"change_type\": 6 //1充值 2消费\r\n        }\r\n    ],\r\n    \"user\": \"\"\r\n}', '1555481788');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('140', 'user/suggestions', '5cb97ad30ea88', 'user', '1', '0', '1', '1', '会员投诉建议', '0', '{\"code\":\"1\",\"info\":\"提交成功\",\"data\":[],\"user\":\"\"}', '1555659508');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('143', 'user/user_authentication', '5cb9ac3a7d9c3', 'user', '1', '1', '1', '1', '会员--验证用户实名认证', '0', '{\"code\":\"0\",\"info\":\"请管理员审核后在申请-.-\",\"data\":[],\"user\":\"\"}\r\n{\"code\":\"1\",\"info\":\"上传成功等待管理员审核\",\"data\":[],\"user\":\"\"}', '1555672303');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('194', 'user/user_background', '5cc2ab24c16e5', 'user', '0', '1', '1', '1', '会员-上传背景图', '0', '{\r\n  \"code\": \"1\",\r\n  \"info\": \"操作成功\",\r\n  \"data\": [],\r\n  \"user\": \"\"\r\n}', '1556262032');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('197', 'user/get_easemob_user', '5cc2d001e4c6b', 'user', '1', '0', '1', '1', '获取环信会员的昵称和头像', '0', '', '1556271151');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('198', 'user/suggestions', '5cc3f28296cf0', 'user', '1', '0', '1', '1', '保存意见反馈', '0', '', '1556345541');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('199', 'money/get_my_money', '5cc45274d6be9', 'user', '1', '1', '1', '1', '我的钱包-获取播币，总收益', '0', '', '1556370266');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('200', 'money/get_money_detail', '5cc45422e5c87', 'user', '1', '1', '1', '1', '我的钱包-消费明细', '0', '', '1556370491');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('203', 'money/recharge_rule', '5cd2b4631e656', 'user', '1', '1', '1', '1', '获取充值规则', '0', '', '1557312630');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('209', 'user/get_user_status', '5cdcb347f14f0', 'user', '1', '1', '1', '1', '会员-用户认证状态', '0', '{\r\n    \"code\": \"0\",// 0：已提交认证     1：暂未提交实名认证\r\n    \"info\": \"请管理员审核后再申请-.-\",\r\n    \"data\": [\r\n        {\r\n            \"status\": 0,//认证状态   0：待审核    1：已通过   2：已拒绝\r\n            \"name\": \"李志豪\",//认证名称\r\n            \"auth_type\": 1,//认证类型   1：主播  4：机构\r\n            \"card_type\": 1,//证件类型(1：身份证，2：护照)\r\n            \"card_no\": \"410220199901010001\",//证件号码\r\n            \"card_img\": \"http://192.168.2.134:105/uploads/images/20190516/085826622393.jpg\",//证件照/身份证正面\r\n            \"evidence\": \"http://192.168.2.134:105/uploads/images/20190516/085826622393.jpg\",//证明材料/身份证反面\r\n            \"mechanism_name\": \"\",//企业名称\r\n            \"business_license\": 0//营业执照\r\n        }\r\n    ],\r\n    \"user\": {\r\n        \"id\": 36,\r\n        \"nickname\": \"昵称啊1222\",\r\n        \"avatar\": \"http://192.168.2.134:105/uploads/images/093642327837.jpg\",\r\n        \"sex\": 1,\r\n        \"user_bobi\": \"0.00\",\r\n        \"user_money\": \"0.00\",\r\n        \"user_integral\": 0,\r\n        \"total_consumption_money\": \"0.00\",\r\n        \"total_revenue_money\": \"0.00\",\r\n        \"votes_total\": \"0.00\",\r\n        \"user_type\": 0,\r\n        \"user_level\": 0,\r\n        \"easemob\": \"317812559bfe09f8cf22ce0df868f9e3\",\r\n        \"status\": 1\r\n    }\r\n}', '1557967773');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('211', 'money/withdraw', '5ce25d5e1ffb8', 'user', '1', '1', '1', '1', '会员-提现申请', '0', '', '1558338938');
INSERT INTO `mb_admin_api_list` (`id`, `apiName`, `hash`, `module`, `checkSign`, `needLogin`, `status`, `method`, `info`, `isTest`, `returnStr`, `create_time`) VALUES ('212', 'money/show_withdraw', '5ce39a2a80e80', 'user', '1', '1', '1', '1', '提现展示页面', '0', '{\r\n    \"code\": \"1\",\r\n    \"info\": \"获取账号信息成功\",\r\n    \"data\": {\r\n        \"user_money\": \"5000.00\",//账户余额\r\n        \"total_revenue_money\": \"0.00\",//总收入\r\n        \"user_bobi\": \"0.00\"//播币\r\n    },\r\n    \"user\": {\r\n        \"id\": 36,\r\n        \"nickname\": \"昵称、\",\r\n        \"avatar\": \"http://192.168.2.134:105/uploads/images/093642327837.jpg\",\r\n        \"sex\": 1,\r\n        \"user_bobi\": \"0.00\",\r\n        \"user_money\": \"5600.00\",\r\n        \"user_integral\": 0,\r\n        \"total_consumption_money\": \"0.00\",\r\n        \"total_revenue_money\": \"0.00\",\r\n        \"votes_total\": \"0.00\",\r\n        \"user_type\": 1,\r\n        \"user_level\": 0,\r\n        \"easemob\": \"317812559bfe09f8cf22ce0df868f9e3\",\r\n        \"status\": 1\r\n    }\r\n}', '1558420060');

-- -----------------------------
-- 增加接口参数
-- -----------------------------

INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('106', 'anchor_id', '5cac603d80bd7', '1', '', '1', '', '主播ID', '0', 'anchor_id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('526', 'username', '5c78dbfd977cf', '2', '', '1', '', 'username【账号。类型：varchar(256)】', '0', 'username');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('527', 'password', '5c78dbfd977cf', '2', '', '1', '', 'password【密码。类型：varchar(256)】', '0', 'password');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('528', 'mobile', '5c78dca45ebc1', '2', '', '1', '', 'mobile【手机号。类型：varchar(256)】', '0', 'mobile');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('529', 'code', '5c78dca45ebc1', '1', '', '1', '', 'code【接收到的验证码。类型：int(6)】', '0', 'code');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('530', 'scene', '5c78dca45ebc1', '2', '', '1', '', 'scene【场景值。类型：varchar(256)】', '0', 'scene');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('541', 'code_id', '5c78dca45ebc1', '1', '', '1', '', 'code_id【验证码表id。类型：int(11) unsigned】', '0', 'code_id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('543', 'access_token', '5c78dbfd977cf', '2', '', '1', '', 'access_token【用户access_token。类型：varchar】', '1', 'access_token');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('544', 'refresh_token', '5c78dbfd977cf', '2', '', '1', '', 'refresh_token【用户refresh_token。类型：varchar(32)】', '1', 'refresh_token');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('545', 'token_type', '5c78dbfd977cf', '2', '', '1', '', 'token_type【用户token_type。类型：varchar】', '1', 'token_type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('546', 'access_token', '5c78dca45ebc1', '2', '', '1', '', 'access_token【用户access_token。类型：varchar】', '1', 'access_token');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('547', 'refresh_token', '5c78dca45ebc1', '2', '', '1', '', 'refresh_token【用户refresh_token。类型：varchar(32)】', '1', 'refresh_token');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('548', 'token_type', '5c78dca45ebc1', '2', '', '1', '', 'token_type【用户token_type。类型：varchar】', '1', 'token_type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('580', 'mobile', '5cad9f63e4f94', '1', '', '1', '', 'mobile【手机号。类型：char(11)】', '0', 'mobile');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('581', 'code', '5cad9f63e4f94', '1', '', '1', '', 'code【验证码。类型：int(4)】', '0', 'code');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('582', 'code_id', '5cad9f63e4f94', '1', '', '1', '', 'code_id【验证码id。类型：int(11)】', '0', 'code_id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('583', 'scene', '5cad9f63e4f94', '2', '', '1', '', 'scene【验证码场景。类型：char(20)】', '0', 'scene');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('609', 'password', '5cad9f63e4f94', '2', '', '1', '', 'password【密码。类型：varchar(256)】', '0', 'password');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('635', 'scene', '5caeeba9866aa', '2', '', '1', '', 'scene【验证场景或来源。类型：varchar(30)】', '0', 'scene');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('636', 'password', '5caeeba9866aa', '2', '', '1', '', 'password【密码。类型：varchar(256)】', '0', 'password');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('637', 'mobile', '5caeeba9866aa', '2', '', '1', '', 'mobile【手机号。类型：varchar(256)】', '0', 'mobile');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('638', 'code', '5caeeba9866aa', '1', '', '1', '', 'code【接收到的验证码。类型：int(6)】', '0', 'code');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('639', 'code_id', '5caeeba9866aa', '1', '', '1', '', 'code_id【验证码id。类型：int(11)】', '0', 'code_id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('694', 'id', '5c78dbfd977cf', '1', '', '1', '', 'id【会员ID。类型：int(11) unsigned】', '1', 'id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('695', 'nickname', '5c78dbfd977cf', '2', '', '1', '', 'nickname【昵称。类型：varchar(256)】', '1', 'nickname');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('696', 'avatar', '5c78dbfd977cf', '2', '', '1', '', 'avatar【头像。类型：varchar(256)】', '1', 'avatar');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('697', 'user_type', '5c78dbfd977cf', '2', '', '1', '', 'user_type【会员类型0注册会员1普通会员2医生3整形设计师4机构。类型：int(11) unsigned】', '1', 'user_type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('698', 'user_level', '5c78dbfd977cf', '1', '', '1', '', 'user_level【会员等级。类型：int(11) unsigned】', '1', 'user_level');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('699', 'mobile', '5c78dbfd977cf', '2', '', '1', '', 'mobile【手机号。类型：char(11)】', '1', 'mobile');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('708', 'avatar', '5cb54af125f1c', '2', '', '0', '', 'avatar【头像。类型：varchar(256)】', '0', 'avatar');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('709', 'nickname', '5cb54af125f1c', '2', '', '1', '', 'nickname【昵称。类型：varchar(256)】', '0', 'nickname');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('716', 'type', '5cb5ad18a18fb', '1', '', '1', '', 'type 【type  1:全部 2：待使用 3：已失效】', '0', 'type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('717', 'user_coupon_id', '5cb5aec30095a', '1', '', '1', '', 'id【领取记录id值。类型：int(10) unsigned】', '0', 'user_coupon_id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('718', 'id', '5cb5ad18a18fb', '1', '', '1', '', 'id【领取记录id值。类型：int(10) unsigned】', '1', 'id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('719', 'end_time', '5cb5ad18a18fb', '11', '', '1', '', 'end_time【结束使用时间。类型：int(10) unsigned】', '1', 'end_time');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('720', 'status', '5cb5ad18a18fb', '1', '', '1', '', 'status【是否使用0过期1未使用2占用中3已使用4已失效。类型：tinyint(1) unsigned】', '1', 'status');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('721', 'money', '5cb5ad18a18fb', '5', '', '1', '', 'money【面值。类型：decimal(10,2)】', '1', 'money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('722', 'min_order_money', '5cb5ad18a18fb', '5', '', '1', '', 'min_order_money【最低使用金额。类型：decimal(10,2)】', '1', 'min_order_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('723', 'min_order_money', '5cb5ad18a18fb', '2', '', '1', '', 'name【优惠券名字。类型：varchar(255)】', '1', 'min_order_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('724', 'id', '5cb5aec30095a', '1', '', '1', '', 'id【。类型：int(10) unsigned】', '1', 'id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('725', 'coupon_name', '5cb5aec30095a', '2', '', '1', '', 'coupon_name【优惠券名字。类型：varchar(255)】', '1', 'coupon_name');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('726', 'end_time', '5cb5aec30095a', '11', '', '1', '', 'end_time【结束时间。类型：int(10) unsigned】', '1', 'end_time');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('727', 'status', '5cb5aec30095a', '1', '', '1', '', 'status【是否使用0过期1未使用2占用中3已使用4已失效。类型：tinyint(1) unsigned】', '1', 'status');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('728', 'money', '5cb5aec30095a', '5', '', '1', '', 'money【面值。类型：decimal(10,2)】', '1', 'money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('729', 'min_order_money', '5cb5aec30095a', '5', '', '1', '', 'min_order_money【最低使用金额。类型：decimal(10,2)】', '1', 'min_order_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('730', 'content', '5cb5aec30095a', '2', '', '1', '', '优惠券内容', '1', 'content');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('738', 'user_id', '5c78c4772da97', '1', '', '0', '', 'user_id【用户id。类型：int(11) unsigned】', '0', 'user_id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('740', 'type', '5cb6c07f79fb8', '1', '1和2', '1', '', '1我的关注 2 我的粉丝', '0', 'type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('741', 'tags', '5cb6d21e49e76', '2', '', '0', '', 'tags【会员标签，请使用英文逗号,分隔。类型：varchar(255)】', '0', 'tags');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('744', 'date', '5cb6c3ee60e5f', '2', '', '0', '', '时间，格式：2019-04-26', '0', 'date');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('746', 'easemob', '5c78dbfd977cf', '2', '', '0', '', 'easemob【环信密码。类型：varchar(32)】', '1', 'easemob');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('784', 'order_price', '5cb5ad18a18fb', '5', '', '0', '', '订单价格', '0', 'order_price');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('816', 'title', '5cb97ad30ea88', '2', '', '1', '', 'title【投诉建议类型。类型：varchar(32)】', '0', 'title');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('817', 'body', '5cb97ad30ea88', '2', '', '1', '', 'body【投诉建议内容。类型：varchar(1000)】', '0', 'body');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('821', 'type', '5cb9ac3a7d9c3', '1', '1和2', '1', '', '//1认证 2实名认证	', '0', 'type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('822', 'card_img', '5cb9ac3a7d9c3', '2', '', '0', '', '身份证正面 type 为 1 必填 若为2 就 不必填	', '0', 'card_img');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('823', 'evidence', '5cb9ac3a7d9c3', '2', '', '0', '', '身份证反面 type 为 1 必填 若为2 就 不必填	', '0', 'evidence');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('824', 'card_no', '5cb9ac3a7d9c3', '2', '', '1', '', '身份证证件号	', '0', 'card_no');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('825', 'name', '5cb9ac3a7d9c3', '2', '', '1', '', '认证用户名字	', '0', 'name');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('826', 'auth_type', '5cb9ac3a7d9c3', '2', '', '0', '', 'type 为 1 必填 若为2 就 不必填 1实名认证2医生认证3整形设计师认证4机构认证	', '0', 'auth_type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('827', 'qualification', '5cb9ac3a7d9c3', '2', '', '0', '', 'type 为 1 必填 若为2 就 不必填 资质认证用,请用英文逗号\",\"分割\'	', '0', 'qualification');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('966', 'eid', '5cc2d001e4c6b', '2', '', '1', '', 'eid【环信账号】', '0', 'eid');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('967', 'background', '5cc2ab24c16e5', '2', '', '1', '', '上传背景图片id', '0', 'background');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('970', 'title', '5cc3f28296cf0', '2', '', '0', '', 'title【投诉建议类型名称。类型：varchar(256)】', '0', 'title');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('971', 'body', '5cc3f28296cf0', '2', '', '1', '', 'body【内容。类型：varchar(1000)】', '0', 'body');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('972', 'user_id', '5cc3f28296cf0', '1', '', '0', '', 'user_id【会员id。类型：int(11) unsigned】', '0', 'user_id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('973', 'user_money', '5cc45274d6be9', '5', '', '1', '', 'user_money【会员余额。类型：decimal(10,2) unsigned】', '1', 'user_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('974', 'votes_total', '5cc45274d6be9', '5', '', '1', '', 'votes_total【打赏总收入。类型：decimal(10,2) unsigned】', '1', 'votes_total');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('975', 'date', '5cc45422e5c87', '2', '', '0', '', '日期【日期。格式2019-04-28】', '0', 'date');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('976', 'before_money', '5cc45422e5c87', '5', '', '1', '', 'before_money【变动前金额。类型：decimal(10,2)】', '1', 'before_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('977', 'change_money', '5cc45422e5c87', '5', '', '1', '', 'change_money【操作金额。类型：decimal(10,2)】', '1', 'change_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('978', 'after_money', '5cc45422e5c87', '5', '', '1', '', 'after_money【变动后金额。类型：decimal(10,2)】', '1', 'after_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('979', 'change_type', '5cc45422e5c87', '2', '', '1', '', 'change_type【操作类型 1 充值 2打赏消费3红包收入4发红包退还。类型：int(11) unsigned】', '1', 'change_type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('980', 'remark', '5cc45422e5c87', '2', '', '1', '', 'remark【备注。类型：varchar(500)】', '1', 'remark');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('981', 'create_time', '5cc45422e5c87', '11', '', '1', '', 'create_time【变动时间。类型：int(11) unsigned】', '1', 'create_time');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('996', 'uuid', '5c78dca45ebc1', '2', '', '1', '', 'uuid【登录设备UUID。类型：varchar(100)】', '0', 'uuid');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('997', 'uuid', '5c78dbfd977cf', '2', '', '1', '', 'uuid【登录设备UUID。类型：varchar(100)】', '0', 'uuid');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1002', 'group', '5cd2b4631e656', '1', '', '1', '', 'group【规则分组。类型：int(11) unsigned，默认0通用，1 IOS】', '0', 'group');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1003', 'name', '5cd2b4631e656', '2', '', '1', '', 'name【规则名称。类型：varchar(128)】', '1', 'name');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1004', 'id', '5cd2b4631e656', '1', '', '1', '', 'id【文档id。类型：int(11) unsigned】', '1', 'id');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1005', 'money', '5cd2b4631e656', '5', '', '1', '', 'money【实付金额。类型：decimal(10,2)】', '1', 'money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1006', 'add_money', '5cd2b4631e656', '5', '', '1', '', 'add_money【到账金额。类型：decimal(10,2)】', '1', 'add_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1007', 'app_name', '5cd2b4631e656', '2', '', '1', '', 'app_name【内购名称。类型：varchar(128)】', '1', 'app_name');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1023', 'account', '5ce25d5e1ffb8', '2', '', '0', '', '提现账号', '0', 'account');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1024', 'money', '5ce25d5e1ffb8', '5', '', '1', '', '提现金额', '0', 'money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1025', 'type', '5ce25d5e1ffb8', '1', '', '1', '', '提现类型   1：微信  2：支付宝', '0', 'type');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1029', 'total_consumption_money', '5cc45274d6be9', '5', '', '1', '', 'total_consumption_money【总消费金额。类型：decimal(10,2) unsigned】', '1', 'total_consumption_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1030', 'total_revenue_money', '5cc45274d6be9', '5', '', '1', '', 'total_revenue_money【总收益金额。类型：decimal(10,2) unsigned】', '1', 'total_revenue_money');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1031', 'user_bobi', '5cc45274d6be9', '5', '', '1', '', 'user_bobi【会员播币。类型：decimal(10,2)】', '1', 'user_bobi');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1032', 'user_integral', '5cc45274d6be9', '1', '', '1', '', 'user_integral【会员积分。类型：int(11) unsigned】', '1', 'user_integral');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1033', 'count_integral', '5cc45274d6be9', '1', '', '1', '', 'count_integral【累计获取积分。类型：int(11) unsigned】', '1', 'count_integral');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1034', 'total_consumption_bobi', '5cc45274d6be9', '5', '', '1', '', 'total_consumption_bobi【总消费播币。类型：decimal(10,2) unsigned】', '1', 'total_consumption_bobi');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1036', 'status', '5caf00505dd00', '1', '', '1', '', 'status【状态0失败1签到成功2已签到返回。类型：tinyint(1) unsigned】', '1', 'status');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1037', 'days', '5caf00505dd00', '1', '', '1', '', 'days【连续签到的天数。类型：tinyint(2) unsigned】', '1', 'days');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1038', 'email', '5cb54af125f1c', '10', '', '0', '', '用户邮箱', '0', 'email');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1039', 'mobile', '5cb54af125f1c', '7', '', '0', '', '用户手机号', '0', 'mobile');
INSERT INTO `mb_admin_api_fields` (`id`, `fieldName`, `hash`, `dataType`, `default`, `isMust`, `range`, `info`, `type`, `showName`) VALUES ('1040', 'region', '5cb54af125f1c', '2', '', '0', '', '地区 ', '0', 'region');
