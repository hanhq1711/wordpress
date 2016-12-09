<?php
require_once("alipay.config.php");
require_once("alipay_notify.class.php");
require_once("alipay_submit.class.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功
	$out_trade_no = $_POST['out_trade_no'];

	$trade_no = $_POST['trade_no'];

	$trade_status = $_POST['trade_status'];
	
	$email = $_POST['buyer_email'];

    $logistics_name = '无';
    $invoice_no = '';
    $transport_type = 'EXPRESS';

	$buyer_alipay = $_POST['buyer_email'];

	$prefix = $wpdb->prefix;
	$table = $prefix.'um_orders';


	if($_POST['trade_status'] == 'WAIT_BUYER_PAY') {
			
        echo "success";	

    }else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
		global $wpdb;
		$row=$wpdb->get_row("select * from ".$table." where order_id=".$out_trade_no);
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
        echo "success";

    }else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
		global $wpdb;
		$row=$wpdb->get_row("select * from ".$table." where order_id=".$out_trade_no);
		if($row){
			if($row->order_status<=2){$wpdb->query( "UPDATE $table SET order_status=3, trade_no='$trade_no', user_alipay='$buyer_alipay' WHERE order_id='$out_trade_no'" );
				if(!empty($row->user_email)){$email = $row->user_email;}
				store_email_template($out_trade_no,'',$email);}
		}
        echo "success";	

    }else if($_POST['trade_status'] == 'TRADE_FINISHED'||$_POST['trade_status'] == 'TRADE_SUCCESS') {
		global $wpdb;
		$row=$wpdb->get_row("select * from ".$table." where order_id=".$out_trade_no);
		if($row){
			if($row->order_status<=3){
				$success_time = $_POST['notify_time'];
				$wpdb->query( "UPDATE $table SET order_status=4, trade_no='$trade_no', order_success_time='$success_time', user_alipay='$buyer_alipay' WHERE order_id='$out_trade_no'" );
				update_success_order_product($row->product_id,$row->order_quantity);
				if(!empty($row->user_email)){$email = $row->user_email;}
				//发送订单状态变更email
				store_email_template($out_trade_no,'',$email);
				//发送购买可见内容或下载链接或会员状态变更
				send_goods_by_order($out_trade_no,'',$email);
			}		
		}	
        echo "success";

    }elseif($_POST['trade_status'] == 'TRADE_CLOSED'){
		global $wpdb;
		$row=$wpdb->get_row("select * from ".$table." where order_id=".$out_trade_no);
		if($row){
			if($row->order_status<=3){
				$success_time = $_POST['notify_time'];
				$wpdb->query( "UPDATE $table SET order_status=9, trade_no='$trade_no', order_success_time='$success_time', user_alipay='$buyer_alipay' WHERE order_id='$out_trade_no'" );
				if(!empty($row->user_email)){$email = $row->user_email;}
				store_email_template($out_trade_no,'',$email);
			}		
		}	
        echo "success";
	}else {
        echo "success";

    }

}
else {

    echo "fail";

}
?>