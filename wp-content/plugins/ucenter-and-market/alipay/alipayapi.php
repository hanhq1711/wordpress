<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex,follow">
	<title>正在前往支付宝...</title>
</head>
<?php
require_once("alipay.config.php");
require_once("alipay_submit.class.php");
date_default_timezone_set('Asia/Shanghai');
$ali_email = trim(um_get_setting('alipay_account'));
if(empty($ali_email))wp_die('系统发生错误，卖家信息错误,请稍后重试!');
$product_id = $_POST['product_id'];
$product_name = '';
$product_des = '';
if($product_id>0){$product_name = $_POST['order_name'];$product_des = get_post_field('post_excerpt',$product_id);}elseif($product_id==-1){$product_name='开通VIP月费会员';$product_des='VIP月费会员';}elseif($product_id==-2){$product_name='开通VIP季费会员';$product_des='VIP季费会员';}elseif($product_id==-3){$product_name='开通VIP年费会员';$product_des='VIP年费会员';}elseif($product_id==-4){$product_name='开通VIP终身会员';$product_des='VIP终身会员';}elseif($product_id==-5){$product_name='牛币充值';$product_des=isset($_POST['creditrechargeNum'])?'充值'.$_POST['creditrechargeNum']*(100).'牛币':'充值牛币';}else{}
$product_url = ($product_id>0)?get_permalink($product_id):get_bloginfo('url');
$order_id = $_POST['order_id'];
if(empty($product_id)||empty($order_id))wp_die('获取商品信息出错,请重试或联系卖家!');
global $wpdb;
$prefix = $wpdb->prefix;
$table = $prefix.'um_orders';
$order = $wpdb->get_row("select * from ".$table." where product_id=".$product_id." and order_id=".$order_id);
if(!$order)wp_die('获取订单出错,请重试或联系卖家!');
$service = um_get_setting('alipay_sign_type','trade_create_by_buyer'); 

        $payment_type = "1";
        $notify_url = Ali_URI."/notify.php";
        $return_url = Ali_URI."/return.php";
        $seller_email = $ali_email;
        $out_trade_no = $order->order_id;
        $subject = $product_name;
        $price = $order->order_total_price;
        $quantity = "1";
        $total_fee = $order->order_total_price;
        $logistics_fee = "0.00";
        $logistics_type = "EXPRESS";
        $logistics_payment = "SELLER_PAY";
		$body = $product_des;
        $show_url = $product_url;
        $anti_phishing_key = "";
        $exter_invoke_ip = "";
        $receive_name = $order->user_name;
        $receive_address = $order->user_address;
        $receive_zip = $order->user_zip;
        $receive_phone = $order->user_phone;
        $receive_mobile = $order->user_cellphone;


$parameter = array(
		"service" => $service,
		"partner" => trim($alipay_config['partner']),
		"payment_type"	=> $payment_type,
		"notify_url"	=> $notify_url,
		"return_url"	=> $return_url,
		"seller_email"	=> $seller_email,
		"out_trade_no"	=> $out_trade_no,
		"subject"	=> $subject,
		"price"	=> $price,
		"quantity"	=> $quantity,
		"logistics_fee"	=> $logistics_fee,
		"logistics_type"	=> $logistics_type,
		"logistics_payment"	=> $logistics_payment,
		"body"	=> $body,
		"show_url"	=> $show_url,
		"receive_name"	=> $receive_name,
		"receive_address"	=> $receive_address,
		"receive_zip"	=> $receive_zip,
		"receive_phone"	=> $receive_phone,
		"receive_mobile"	=> $receive_mobile,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
echo '<div style="display:none">'.$html_text.'</div>';

?>
</body>
</html>