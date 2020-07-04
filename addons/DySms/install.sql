SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `lw_addons_dysms`
-- ----------------------------
DROP TABLE IF EXISTS `lw_addons_dysms`;
CREATE TABLE `lw_addons_dysms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT '模板名称',
  `code` varchar(128) NOT NULL DEFAULT '' COMMENT '模板id',
  `content` varchar(256) NOT NULL DEFAULT '' COMMENT '模板详情',
  `sign_name` varchar(128) NOT NULL DEFAULT '' COMMENT '短信签名',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='短信模板表';

INSERT INTO `lw_addons_dysms` (`id`, `title`, `code`, `content`, `sign_name`, `create_time`, `update_time`, `status`) VALUES ('1', '验证码', 'SMS_162732629', '您的验证码${code}，该验证码5分钟内有效，请勿泄漏于他人！', '众仁聚美', '0', '0', '1');