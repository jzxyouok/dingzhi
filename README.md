#dingzhi

###目录和文件说明
- 配置文件data/config.php。
- 其中manager是后台目录，可修改名称，同时要修改data/config.php里的配置。
- 后台菜单manager/includes/inc_menu.php，删到没有二级菜单的时候，一级菜单自然就不显示。
- 基本的目录包括data,includes,languages,manager,temp。
- data和temp要可写？
- 如果mac里，后台登录验证码无法显示，将includes/cls_captcha.php第35行的$img_type设置成jpeg。

###数据表

[数据字典](http://bbs.ecshop.com/thread-78827-1-1.html)

- cart 购物车
- goods 商品表
- order_info 订单
	- order_amount 应付款金额（订单总价）
	- goods_amount 商品总金额
	- inv_payee 发票抬头
	- invoice_no 发货单号
- users 用户表
- address 收货地址
- user_address 原有的收货地址表（不用）
- region 地区表
- shop_config 网站设置

###订单状态
在文件includes/inc_constant.php

######刚下完订单
- order_status 0 OS_UNCONFIRMED
- shipping_status 0 SS_UNSHIPPED
- pay_status 0 PS_UNPAYED

######取消
- order_status 2 OS_CANCELED
- shipping_status 0 SS_UNSHIPPED
- pay_status 0 PS_UNPAYED

######确认
- order_status 1 OS_CONFIRMED
- shipping_status 0 SS_UNSHIPPED
- pay_status 0 PS_UNPAYED

######已付款
- order_status 1 OS_CONFIRMED
- shipping_status 0 SS_UNSHIPPED
- pay_status 2 PS_PAYED

######配货中
- order_status 1 OS_CONFIRMED
- shipping_status 3 SS_PREPARING
- pay_status 2 PS_PAYED

######已发货
- order_status 5 OS_SPLITED
- shipping_status 1 SS_SHIPPED
- pay_status 2 PS_PAYED

######已收货
- order_status 5 OS_SPLITED
- shipping_status 2 SS_RECEIVED
- pay_status 2 PS_PAYED

######退货
- order_status 4 OS_RETURNED
- shipping_status 0 SS_UNSHIPPED
- pay_status 0 PS_UNPAYED

####综合状态（仅用于order_query_sql函数）
- CS_AWAIT_PAY 100 待付款：货到付款且已发货且未付款，非货到付款且未付款
- CS_AWAIT_SHIP 101 待发货：货到付款且未发货，非货到付款且已付款且未发货
- CS_FINISHED 102 已完成：已确认、已付款、已发货

###其它

后台帐号admin admin888

商店名称请到商店设置里修改。

用了TP3.2框架后，如果页面返回404，有可能是数据库没连好，或者Application/Runtime不可写。