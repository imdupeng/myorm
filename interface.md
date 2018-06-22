- 列表类接口的数据结构
  - 通用参数: 可以按照如下格式在URL中传递参数 /page/XXX/pagesize/100
    page:
    pagesize:
    
  - 返回:

{
    status : 1,
    code: 350,
    message : '',
    data : {
        page: 1
        pagesize: 10
        list: [
            {},
            {},
        },
    }
}

返回数据的备选方案:
{
    status : 1,
    code: 350,
    message : '',
    data : {
        page: 1
        pagesize: 10
        entities: {
            1: {name:},
            50: {},
        },
        all:[1,2,3,7,9],
        last30:[1,3,50,60],
        recent:[50,60,1,3],

    }
}

- 接口服务器异常时的返回数据结构
{
    status : 0,
    code: 350,
    message : '',
    data : 'trace string'
}

- 删除数据接口
  - 需要的数据 post 方式发送数据
    - id: 

  - 返回的数据:
{
    status : 1,
    code: 350,
    message : '',
    data : $pk
}

- 新增数据接口
  需要的数据 post 方式发送数据
    - data: 

  - 返回的数据:
{
    status : 1,
    code: 350,
    message : '',
    data : $pk
}

- 更新数据接口
  - 需要的数据 post 方式发送数据
    - id: 
    - data: 

  - 返回的数据:
{
    status : 1,
    code: 350,
    message : '',
    data : $pk
}

- 商品列表
  - 地址: /index.php/product/list/
  - 参数
    - type: 2:全部 3:最近订货 4:30天畅销
    - keywords:  搜索关键字
  - 数据实体字段:
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL COMMENT '商品名',
  `description` varchar(255) DEFAULT '' COMMENT '商品描述',
  `vendor_id` int(11) DEFAULT NULL COMMENT '供应商',
  `vendor_name` varchar(50) DEFAULT NULL COMMENT '供应商姓名',
  `purchase_price` float DEFAULT NULL COMMENT '采购价',
  `wholesale_price` float DEFAULT NULL COMMENT '批发价',
  `retail_price` float DEFAULT NULL COMMENT '零售价',
  `last_bill_at` int(11) DEFAULT NULL COMMENT '最后订货时间',
  image varchar(100) DEFAULT NULL COMMENT '缩略图',


- 商品创建
  - 地址: /index.php/product/create/
  - 参数
    - data:
  - 字段:
  `name` varchar(100) DEFAULT NULL COMMENT '商品名',
  `description` varchar(255) DEFAULT '' COMMENT '商品描述',
  `vendor_id` int(11) DEFAULT NULL COMMENT '供应商',
  `vendor_name` varchar(50) DEFAULT NULL COMMENT '供应商姓名',
  `purchase_price` float DEFAULT NULL COMMENT '采购价',
  `wholesale_price` float DEFAULT NULL COMMENT '批发价',
  `retail_price` float DEFAULT NULL COMMENT '零售价',
  `images`: [20,30,40]


- 商品删除
  - 地址: /index.php/product/create/
  - 参数
    - pk: $id

- 商品更新
  - 地址: /index.php/product/create/
  - 参数
    - pk: $id
    - data: {}

