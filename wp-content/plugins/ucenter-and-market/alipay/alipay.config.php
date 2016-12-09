<?php
require_once(dirname(__FILE__)."/../../../../wp-load.php");
define('Ali_URI',UM_URI.'/alipay');
date_default_timezone_set('Asia/Shanghai');
$ali_partner = um_get_setting('alipay_id');
$ali_key = um_get_setting('alipay_key');
if(empty($ali_partner)||empty($ali_key))wp_die('支付宝商家认证信息为空!');
$alipay_config['partner']		= $ali_partner;
$alipay_config['key']			= $ali_key;
$alipay_config['sign_type']    = strtoupper('MD5');
$alipay_config['input_charset']= strtolower('utf-8');
$alipay_config['cacert']    = getcwd().'/cacert.pem';
$alipay_config['transport']    = 'http';
?>