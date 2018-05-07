<?php
require_once(APP_PATH."Common/Common/vendors.php");
$config = array (
		//应用ID,您的APPID。
		'app_id' => modC('ALIPAY_APPID','','Order'),

		//商户私钥，您的原始格式RSA私钥
        'merchant_private_key' => modC('MERCHANT_PRIVATE_KEY','','Order'),
		//异步通知地址
		'notify_url' => 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/order/index/alipay_notify',

		//同步跳转
		'return_url' => U('Order/Index/completion','',true,true),

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => modC('ALIPAY_PUBLIC_KEY','','Order')
	
);
