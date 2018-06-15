-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        10.1.16-MariaDB - mariadb.org binary distribution
-- 服务器操作系统:                      Win32
-- HeidiSQL 版本:                  9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- 导出 weshop 的数据库结构
DROP DATABASE IF EXISTS `weshop`;
CREATE DATABASE IF NOT EXISTS `weshop` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `weshop`;

-- 导出  表 weshop.address 结构
DROP TABLE IF EXISTS `address`;
CREATE TABLE IF NOT EXISTS `address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) DEFAULT NULL COMMENT '伙伴id',
  `name` varchar(20) DEFAULT NULL COMMENT '姓名',
  `phone` varchar(20) DEFAULT NULL COMMENT '电话',
  `address` varchar(50) DEFAULT NULL COMMENT '地址',
  `sheng` int(11) DEFAULT NULL COMMENT '省',
  `shi` int(11) DEFAULT NULL COMMENT '市',
  `xian` int(11) DEFAULT NULL COMMENT '县',
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='零售客户收货地址';

-- 数据导出被取消选择。
-- 导出  表 weshop.bill 结构
DROP TABLE IF EXISTS `bill`;
CREATE TABLE IF NOT EXISTS `bill` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` bigint(20) DEFAULT NULL COMMENT '订单号 2位年+2位月+8位id流水 便于分表',
  `creator_user_id` int(11) NOT NULL COMMENT '创建人',
  `po_from_user_id` int(11) DEFAULT NULL COMMENT '供货人user_id',
  `po_from_partner_id` int(11) DEFAULT NULL COMMENT '供货人伙伴id',
  `sale_to_user_id` int(11) DEFAULT NULL COMMENT '客户/代理user_id',
  `sale_to_partner_id` int(11) DEFAULT NULL COMMENT '客户/代理伙伴id',
  `address_info_id` int(11) DEFAULT NULL COMMENT '收货地址',
  `sender_info_id` int(11) DEFAULT NULL COMMENT '发运信息',
  `first_bill_id` int(11) DEFAULT NULL COMMENT '第一个订单id',
  `last_bill_id` int(11) DEFAULT NULL COMMENT '下级订单id',
  `goods_id` int(11) NOT NULL COMMENT '商品',
  `goods_desc` int(11) DEFAULT NULL COMMENT '商品描述',
  `number` int(11) NOT NULL COMMENT '订货数量',
  `purchas_price` int(11) NOT NULL COMMENT '采购价',
  `description` int(11) DEFAULT NULL COMMENT '订单备注',
  `sale_price` int(11) NOT NULL COMMENT '销售价',
  `creator_status` int(11) DEFAULT NULL COMMENT '创建人处理状态 1未发送 2已发送',
  `logistics_status` int(11) DEFAULT NULL COMMENT '物流状态 1未发运 2已发运',
  `logistics_number` int(11) DEFAULT NULL COMMENT '物流单号',
  `logistics_image_id` int(11) DEFAULT NULL COMMENT '物流运单图片',
  `receiver_status` int(11) DEFAULT NULL COMMENT '接收人处理状态 1待处理 2已创建新订单 3已处理发货',
  `year` int(11) NOT NULL COMMENT '年度',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `send_time` int(11) DEFAULT NULL COMMENT '发送给供应商的时间',
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单';

-- 数据导出被取消选择。
-- 导出  表 weshop.goods 结构
DROP TABLE IF EXISTS `goods`;
CREATE TABLE IF NOT EXISTS `goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `copy_from_id` int(11) DEFAULT NULL COMMENT '从哪个商品复制',
  `category_id` int(11) DEFAULT NULL COMMENT '商品分类',
  `name` varchar(255) DEFAULT NULL COMMENT '商品名',
  `description` text COMMENT '商品描述',
  `vendor_id` int(11) DEFAULT NULL COMMENT '供应商',
  `vendor_name` varchar(50) DEFAULT NULL COMMENT '供应商姓名',
  `purchase_price` float DEFAULT NULL COMMENT '采购价',
  `wholesale_price` float DEFAULT NULL COMMENT '批发价',
  `retail_price` float DEFAULT NULL COMMENT '零售价',
  `last_bill_at` int(11) DEFAULT NULL COMMENT '最后订货时间',
  `image_count` int(11) DEFAULT NULL COMMENT '图片数量',
  `deleted_at` int(11) DEFAULT NULL,
  `pstatus` int(11) DEFAULT NULL COMMENT '商品状态，2正常3下架4废弃',
  `orderby` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='商品';

-- 数据导出被取消选择。
-- 导出  表 weshop.goods_image 结构
DROP TABLE IF EXISTS `goods_image`;
CREATE TABLE IF NOT EXISTS `goods_image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL COMMENT '商品',
  `image_id` int(11) NOT NULL COMMENT '图片',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品图片';

-- 数据导出被取消选择。
-- 导出  表 weshop.goods_tag 结构
DROP TABLE IF EXISTS `goods_tag`;
CREATE TABLE IF NOT EXISTS `goods_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL COMMENT '商品',
  `tag_id` int(11) NOT NULL COMMENT '标签',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品标签';

-- 数据导出被取消选择。
-- 导出  表 weshop.image 结构
DROP TABLE IF EXISTS `image`;
CREATE TABLE IF NOT EXISTS `image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(11) NOT NULL DEFAULT '' COMMENT '路径',
  `file_name` varchar(11) NOT NULL DEFAULT '' COMMENT '文件名',
  `hash` varchar(40) DEFAULT NULL COMMENT 'hash',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图片';

-- 数据导出被取消选择。
-- 导出  表 weshop.logistics_bill 结构
DROP TABLE IF EXISTS `logistics_bill`;
CREATE TABLE IF NOT EXISTS `logistics_bill` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_order_id` int(11) DEFAULT NULL COMMENT '第一个订单id',
  `user_id` int(11) DEFAULT NULL COMMENT '处理人',
  `status` int(11) DEFAULT NULL COMMENT '状态',
  `number` varchar(20) DEFAULT NULL COMMENT '订单号',
  `company_id` int(11) DEFAULT NULL COMMENT '物流公司',
  `image_id` int(11) DEFAULT NULL COMMENT '运单图片',
  `send_time` int(11) DEFAULT NULL COMMENT '发货时间',
  `create_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `info` varchar(500) DEFAULT NULL COMMENT '识别出的运单信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='物流运单';

-- 数据导出被取消选择。
-- 导出  表 weshop.message 结构
DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `to_user_id` int(11) DEFAULT NULL COMMENT '接收人',
  `to_open_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL COMMENT '关联订单',
  `title` int(11) DEFAULT NULL COMMENT '标题',
  `message` int(11) DEFAULT NULL COMMENT '消息',
  `create_time` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL COMMENT '0待发送 1已发送 2已阅读 3已删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息';

-- 数据导出被取消选择。
-- 导出  表 weshop.message_template 结构
DROP TABLE IF EXISTS `message_template`;
CREATE TABLE IF NOT EXISTS `message_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title_template` varchar(50) DEFAULT NULL COMMENT '标题模板',
  `body_template` varchar(200) DEFAULT '' COMMENT '消息模板',
  `code` int(11) DEFAULT NULL COMMENT '消息类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息模板';

-- 数据导出被取消选择。
-- 导出  表 weshop.partner 结构
DROP TABLE IF EXISTS `partner`;
CREATE TABLE IF NOT EXISTS `partner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户',
  `type` tinyint(11) DEFAULT NULL COMMENT '伙伴类型',
  `open_id` varchar(20) DEFAULT NULL COMMENT '绑定微信',
  `wechat` varchar(20) DEFAULT NULL COMMENT '微信号',
  `name` varchar(20) DEFAULT NULL COMMENT '姓名',
  `phone` varchar(11) DEFAULT NULL COMMENT '电话',
  `note` varchar(11) DEFAULT NULL COMMENT '备注',
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='伙伴';

-- 数据导出被取消选择。
-- 导出  表 weshop.search_history 结构
DROP TABLE IF EXISTS `search_history`;
CREATE TABLE IF NOT EXISTS `search_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户',
  `keywords` int(11) DEFAULT NULL COMMENT '搜索关键字',
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='搜索历史';

-- 数据导出被取消选择。
-- 导出  表 weshop.sender 结构
DROP TABLE IF EXISTS `sender`;
CREATE TABLE IF NOT EXISTS `sender` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '所属用户',
  `name` int(11) DEFAULT NULL COMMENT '发件人姓名',
  `phone` int(11) DEFAULT NULL COMMENT '发件人电话',
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户快递的发件人选项';

-- 数据导出被取消选择。
-- 导出  表 weshop.tag 结构
DROP TABLE IF EXISTS `tag`;
CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL COMMENT '标签名',
  `group` int(11) DEFAULT NULL COMMENT '标签分组',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='标签';

-- 数据导出被取消选择。
-- 导出  表 weshop.user 结构
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `avalon` int(11) DEFAULT NULL COMMENT '头像',
  `name` varchar(20) DEFAULT NULL COMMENT '姓名',
  `open_id` char(11) NOT NULL DEFAULT '' COMMENT '微信openid',
  `phone` char(11) NOT NULL DEFAULT '""' COMMENT '电话',
  `disable` int(11) NOT NULL DEFAULT '0' COMMENT '禁用',
  `last_login_at` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `last_operate_at` int(11) DEFAULT NULL COMMENT '最后操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户';

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
