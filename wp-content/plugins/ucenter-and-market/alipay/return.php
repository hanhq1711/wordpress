<?php

require_once("alipay.config.php");
require_once("alipay_notify.class.php");
require_once("alipay_submit.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
if(!is_user_logged_in()){
	wp_die('请先登录系统');
}
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {


	$out_trade_no = $_GET['out_trade_no'];
	$trade_no = $_GET['trade_no'];
	$email = $_GET['buyer_email'];
    $logistics_name = '无';

    $invoice_no = '';
    $transport_type = 'EXPRESS';

	$trade_status = $_GET['trade_status'];

	$buyer_alipay = $_GET['buyer_email'];

	$prefix = $wpdb->prefix;
	$table = $prefix.'um_orders';

	if($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {

		global $wpdb;
		$row=$wpdb->get_row("select * from ".$table." where order_id=".$out_trade_no);
		$product_id = $row->product_id; 
		if($row){
			if($row->order_status<=1){$wpdb->query( "UPDATE $table SET order_status=2, trade_no='$trade_no', user_alipay='$buyer_alipay' WHERE order_id='$out_trade_no'" );
				if(!empty($row->user_email)){$email = $row->user_email;}
				store_email_template($out_trade_no,'',$email);}
		}


		$parameter = array(
			"service" => "send_goods_confirm_by_platform",
			"partner" => trim($alipay_config['partner']),
			"trade_no"	=> $trade_no,
			"logistics_name"	=> $logistics_name,
			"invoice_no"	=> $invoice_no,
			"transport_type"	=> $transport_type,
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);


		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($parameter);

		$doc = new DOMDocument();
		$doc->loadXML($html_text);
		if(!empty($doc->getElementsByTagName( "alipay" )->item(0)->nodeValue) ){
			$row_new=$wpdb->get_row("select * from ".$table." where order_id=".$out_trade_no);
			if($row_new){
				if($row_new->order_status<=2){$wpdb->query( "UPDATE $table SET order_status=3 WHERE order_id='$out_trade_no'" );store_email_template($out_trade_no,'',$email);}
			}
		}


    }elseif($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {

		global $wpdb;
		$row=$wpdb->get_row("select * from ".$table." where order_id=".$out_trade_no);
		$product_id = $row->product_id;
		if($row){
			if($row->order_status<=3){
				$success_time = $_POST['notify_time'];
				$wpdb->query( "UPDATE $table SET order_status=4, trade_no='$trade_no', order_success_time='$success_time', user_alipay='$buyer_alipay' WHERE order_id='$out_trade_no'" );
				update_success_order_product($row->product_id,$row->order_quantity);
				if(!empty($row->user_email)){$email = $row->user_email;}
				store_email_template($out_trade_no,'',$email);
				send_goods_by_order($out_trade_no,'',$email);
				
			}		
		}

    }
    else {
      echo "<br /><br />";
    }
		
	echo "<br /><br />";

}
else {

    wp_die('错误的请求！如果您已经完成付款，请联系管理员!');
    exit;
}
?>
<?php global $wpdb;$row=$wpdb->get_row("select * from ".$table." where order_id=".$_GET['out_trade_no']);$product_id = $row->product_id; $product_url = ($product_id>0)?get_permalink($product_id):get_bloginfo('url'); ?>
        <title>支付宝支付结果</title>
		<style type="text/css">
            .font_title{
                font-family:"Microsoft Yahei",微软雅黑;
                font-size:16px;
                color:#000;
                font-weight:bold;
            }
            .font_content{
                font-family:"Microsoft Yahei",微软雅黑;
                font-size:13px;
                color:#888;
                font-weight:normal;
            }
            table{
                border: 0 solid #CCCCCC;
            }
        </style>
	</head>
    <body>
		<table align="center" width="350" cellpadding="5" cellspacing="0">
            <tr>
                <td align="center" class="font_title" colspan="2">恭喜，支付成功!</td>
            </tr>
            <tr>
                <td class="font_content" align="left">支付金额:<?php echo $_GET['total_fee'].' 元'; ?>服务器正在自动提交发货状态，选择担保交易的请主动至支付宝交易记录中确认收货。</td>
            </tr>
			<tr>
                <td class="font_content" align="center"><a href="<?php echo $product_url; ?>" title="返回商品主页"><button style="cursor:pointer;">返回商品主页</button></a></td>
            </tr>
        </table>
    </body>
</html>