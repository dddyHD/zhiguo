-- -----------------------------
-- 表结构 `ocenter_weixin_areply`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_weixin_areply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keywords` varchar(255) NOT NULL DEFAULT '关键词',
  `type` tinyint(4) NOT NULL COMMENT '回复类型 1：文本 2：图文 3：多图文',
  `image` varchar(255) DEFAULT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `is_attention` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否为关注回复 1是 2否',
  `cid` int(11) DEFAULT NULL,
  `is_news` tinyint(1) DEFAULT '0',
  `ctime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keywords` (`keywords`),
  KEY `cid` (`cid`),
  KEY `is_news` (`is_news`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='自动回复表';


-- -----------------------------
-- 表结构 `ocenter_weixin_menu`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_weixin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `pid` int(11) NOT NULL,
  `linkurl` varchar(255) NOT NULL,
  `sort` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE,
  KEY `sort` (`sort`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='微信菜单';

