--
-- 表的结构 `ocenter_mall_spec`
--

CREATE TABLE IF NOT EXISTS `ocenter_mall_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL COMMENT '规格名称',
  `values` varchar(256) NOT NULL COMMENT '规格参数',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='规格表' AUTO_INCREMENT=5 ;

--
-- 转存表中的数据 `ocenter_mall_spec`
--

INSERT INTO `ocenter_mall_spec` (`id`, `name`, `values`, `create_time`, `status`) VALUES
(1, '颜色', '', 1483942617, 1);

--
-- 表的结构 `ocenter_mall_spec_value`
--

CREATE TABLE IF NOT EXISTS `ocenter_mall_spec_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spec_id` int(11) NOT NULL COMMENT '规格id',
  `name` varchar(128) NOT NULL COMMENT '规格详情名',
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `spec_id` (`spec_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='规格详情表' AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `ocenter_mall_spec_value`
--

INSERT INTO `ocenter_mall_spec_value` (`id`, `spec_id`, `name`, `create_time`) VALUES
(1, 1, '红色', 1483942617),
(2, 1, '黑色', 1483942617);



-- -----------------------------
-- 表结构 `ocenter_mall_goods`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_mall_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '操作者',
  `name` varchar(32) NOT NULL COMMENT '商品名称',
  `url` varchar(200) NOT NULL COMMENT '商品的外链',
  `status` tinyint(4) NOT NULL COMMENT '商品状态',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `cate` int(11) NOT NULL COMMENT '商品类型',
  `size` varchar(200) NOT NULL COMMENT '商品尺寸',
  `color` varchar(200) NOT NULL COMMENT '商品颜色',
  `instro` text NOT NULL COMMENT '商品介绍',
  `pictures` varchar(64) NOT NULL COMMENT '商品图片',
  `banner` int(11) NOT NULL COMMENT '商品详情页图',
  `update_time` int(11) NOT NULL,
  `is_hot` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否热门',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT '阅读量',
  `sales` int(11) NOT NULL DEFAULT '0' COMMENT '销量',
  PRIMARY KEY (`id`),
  KEY `cate` (`cate`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


-- -----------------------------
-- 表结构 `ocenter_mall_goods_category`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_mall_goods_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `cate_picture` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `allow_post` tinyint(4) NOT NULL,
  `pid` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- -----------------------------
-- 表结构 `ocenter_mall_goods_car`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_mall_goods_car` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `goods_id` int(11) DEFAULT NULL COMMENT '商品的id',
  `count` float NOT NULL DEFAULT '0' COMMENT '商品数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='购物车' AUTO_INCREMENT=1 ;

--
-- 表的结构 `ocenter_mall_service`
--

CREATE TABLE IF NOT EXISTS `ocenter_mall_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `QQ` int(20) NOT NULL COMMENT '客服QQ',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='规格表' AUTO_INCREMENT=5 ;
