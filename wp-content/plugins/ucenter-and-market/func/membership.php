<?php
if ( !defined('ABSPATH') ) {exit;}
function create_membership_table(){
	global $wpdb;
	include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	$table_charset = '';
	$prefix = $wpdb->prefix;
	$users_table = $prefix.'um_vip_users';
	if($wpdb->has_cap('collation')) {
		if(!empty($wpdb->charset)) {
			$table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if(!empty($wpdb->collate)) {
			$table_charset .= " COLLATE $wpdb->collate";
		}		
	}
	$create_vip_users_sql="CREATE TABLE $users_table (id int(11) NOT NULL auto_increment,user_id int(11) NOT NULL,user_type tinyint(4) NOT NULL default 0,startTime datetime NOT NULL default '0000-00-00 00:00:00',endTime datetime NOT NULL default '0000-00-00 00:00:00',PRIMARY KEY (id),INDEX uid_index(user_id),INDEX utype_index(user_type)) ENGINE = MyISAM $table_charset;";
	maybe_create_table($users_table,$create_vip_users_sql);
}
add_action('admin_menu','create_membership_table');
function getUserMemberType($uid=''){
	date_default_timezone_set ('Asia/Shanghai');
	global $wpdb;
	if(empty($uid)):
	$user_info = wp_get_current_user();
	$id = $user_info->ID;
	else: $id = $uid;
	endif;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_vip_users';
	$userType=$wpdb->get_row("select * from ".$table." where user_id=".$id);
	if($userType){
		if(time() >strtotime($userType->endTime)){
			$wpdb->query("update $table set user_type=0,endTime='0000-00-00 00:00:00' where user_id=".$id);
			return 0;
		}
		return $userType->user_type;
	}
	return FALSE;
}
function getUserMemberInfo($uid=''){
	date_default_timezone_set ('Asia/Shanghai');
	global $wpdb;
	if(empty($uid)):
	$user_info = wp_get_current_user();
	$id = $user_info->ID;
	else: $id = $uid;
	endif;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_vip_users';
	$userInfo=$wpdb->get_row("select * from ".$table." where user_id=".$id,'ARRAY_A');
	if(!$userInfo){
		$Info = array('id'=>$userInfo['id'],'user_id'=>$id,'user_type'=>'非会员','user_status'=>'未开通过会员','startTime'=>'N/A','endTime'=>'N/A');
	}else{
		$Info['id'] = $userInfo['id'];
		$Info['user_id'] = $id;
		$Info['startTime'] = $userInfo['startTime'];
		$Info['endTime'] = $userInfo['endTime'];
		$Info['user_type']='过期会员';
		if(time() >strtotime($userInfo['endTime'])){
			$Info['user_status']='已过期';$wpdb->query("update $table set user_type=0,endTime='0000-00-00 00:00:00' where user_id=".$id);
		}else{
			$left=(int)(((strtotime($userInfo['endTime']))-time())/(3600*24));$Info['user_status']=$left.'天后到期';
			switch ($userInfo['user_type']){
				case 4:
					$Info['user_type']='终身会员';
					break;
				case 3:
					$Info['user_type']='年费会员';
					break;
				case 2:
					$Info['user_type']='季费会员';
					break;
				default:
					$Info['user_type']='月费会员';		
			}
		}
	}
	return $Info;
}
function getUserMemberOrders($uid=''){
	global $wpdb;
	if(empty($uid)):
	$user_info = wp_get_current_user();
	$id = $user_info->ID;
	else: $id = $uid;
	endif;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_orders';
	$vip_orders=$wpdb->get_Results("select * from ".$table." where user_id=".$id." and product_id in (-1,-2,-3,-4)",'ARRAY_A');
	return $vip_orders;
}
function output_order_vipType($code){
	switch($code){
		case 4:
			$type = '终身会员';
			break;
		case 3:
			$type = '年费会员';
			break;
		case 2:
			$type = '季费会员';
			break;
		default:
			$type = '月费会员';
	}
	return $type;
}
function create_the_vip_order(){
	$success=0;
	$order_id ='';
	$msg ='';
	if(!is_user_logged_in()){$msg='请先登录';}else{
		$user_info = wp_get_current_user();$uid = $user_info->ID;$user_name=$user_info->display_name;$user_email = $user_info->user_email;
		$product_id = $_POST['product_id'];
		if($product_id==-4){$order_price=um_get_setting('life_mb_price',120);$order_name='终身会员';}elseif($product_id==-3){$order_price=um_get_setting('annual_mb_price',45);$order_name='年费会员';}elseif($product_id==-2){$order_price=um_get_setting('quarterly_mb_price',12);$order_name='季费会员';}else{$order_price=um_get_setting('monthly_mb_price',5);$order_name='月费会员';}
		$ratio = um_get_setting('aff_ratio',10);
		$rewards = $order_price*$ratio/100;
		$rewards = (int)$rewards;
		$insert = insert_order($product_id,$order_name,$order_price,1,$order_price,1,'',$uid,$_POST['aff_user_id'],$rewards,$user_name,$user_email,'','','','','');
		if($insert){
			$success = 1;
			$order_id = $insert;
			if(!empty($user_email)) {store_email_template($order_id,'',$user_email);}
		}else{
			$msg = '创建订单失败，请重新再试';
		}
		
	}
	$return = array('success'=>$success,'msg'=>$msg,'order_id'=>$order_id);
	echo json_encode($return);
	exit;
}
add_action( 'wp_ajax_nopriv_create_vip_order', 'create_the_vip_order' );
add_action( 'wp_ajax_create_vip_order', 'create_the_vip_order' );
function elevate_user_vip($type=1,$user_id,$user_name,$from,$to){
	date_default_timezone_set ('Asia/Shanghai');
	$admin_email = get_bloginfo ('admin_email');
	$blogname = get_bloginfo('name');
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_vip_users';
	$userInfo=$wpdb->get_row("select * from ".$table." where user_id=".$user_id);
	$period = 3600*24*30;
	switch($type){
		case 4:
			$period = 3600*24*365*99;
			break;
		case 3:
			$period = 3600*24*365;
			break;
		case 2:
			$period = 3600*24*90;
			break;
		default:
			$period = 3600*24*30;
	}
	//$vip_status = getUserMemberType($user_id);
	$endTime = time()+$period;
	$endTime = strftime('%Y-%m-%d %X',$endTime);
	if($type==4)$endTime='2037-12-31 23:59:59';
	if(!$userInfo){
		$wpdb->query( "INSERT INTO $table (user_id,user_type,startTime,endTime) VALUES ('$user_id', '$type', NOW(),'$endTime')" );
	}else{
		if(time() > strtotime($userInfo->endTime)){
			$wpdb->query( "UPDATE $table SET user_type='$type', startTime=NOW(), endTime='$endTime' WHERE user_id='$user_id'" );
		}else{
			$endTime = strtotime($userInfo->endTime)+$period;
			$endTime = strftime('%Y-%m-%d %X',$endTime);
			$wpdb->query( "UPDATE $table SET user_type='$type', endTime='$endTime' WHERE user_id='$user_id'" );
		}
	}
	//email
	$vip=$wpdb->get_row("select * from ".$table." where user_id=".$user_id);
	$content = '<p>您已成功开通或续费了会员，以下为当前会员信息，如有任何疑问，请及时联系我们（Email:<a href="mailto:'.$admin_email.'" target="_blank">'.$admin_email.'</a>）。</p><div style="background-color:#fefcc9; padding:10px 15px; border:1px solid #f7dfa4; font-size: 12px;line-height:160%;">会员状态：'.output_order_vipType($vip->user_type).'<br>开通时间：'.$vip->startTime.'<br>到期时间：'.$vip->endTime.'</div>';
	$html = store_email_template_wrap($user_name,$content);
	if(empty($from)){$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));}else{$wp_email=$from;}
	$title='会员状态变更提醒';
	$fr = "From: \"" . $blogname . "\" <$wp_email>";
	$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
	wp_mail( $to, $title, $html, $headers );
}
function um_manual_promotevip($user_id,$user_name,$to,$type=1,$endTime){
	date_default_timezone_set ('Asia/Shanghai');
	$admin_email = get_bloginfo ('admin_email');
	$blogname = get_bloginfo('name');
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_vip_users';
	$userInfo=$wpdb->get_row("select * from ".$table." where user_id=".$user_id);
	if(!$userInfo){
		$wpdb->query( "INSERT INTO $table (user_id,user_type,startTime,endTime) VALUES ('$user_id', '$type', NOW(),'$endTime')" );
	}else{
		if(strtotime($endTime) > strtotime($userInfo->endTime) && time() > strtotime($userInfo->endTime)){
			$wpdb->query( "UPDATE $table SET user_type='$type', startTime=NOW(), endTime='$endTime' WHERE user_id='$user_id'" );
		}elseif(strtotime($endTime) > strtotime($userInfo->endTime) && time() <= strtotime($userInfo->endTime)){
			$wpdb->query( "UPDATE $table SET user_type='$type', endTime='$endTime' WHERE user_id='$user_id'" );
		}
	}
	//email
	$vip=$wpdb->get_row("select * from ".$table." where user_id=".$user_id);
	$content = '<p>系统管理员已为您成功开通或续费了会员，以下为当前会员信息，如有任何疑问，请及时联系我们（Email:<a href="mailto:'.$admin_email.'" target="_blank">'.$admin_email.'</a>）。</p><div style="background-color:#fefcc9; padding:10px 15px; border:1px solid #f7dfa4; font-size: 12px;line-height:160%;">会员状态：'.output_order_vipType($vip->user_type).'<br>开通时间：'.$vip->startTime.'<br>到期时间：'.$vip->endTime.'</div>';
	$html = store_email_template_wrap($user_name,$content);
	$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
	$title='会员状态变更提醒';
	$fr = "From: \"" . $blogname . "\" <$wp_email>";
	$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
	wp_mail( $to, $title, $html, $headers );
}
function um_member_icon($uid){
	$uid = (int)$uid;
	$type = getUserMemberType($uid);
	if($type===3)$icon = '<i class="ico annual_member"></i>';
	elseif($type===2)$icon = '<i class="ico quarter_member"></i>';
	elseif($type===1)$icon = '<i class="ico month_member"></i>';
	elseif($type===0)$icon = '<i class="ico expired_member"></i>';
	else $icon = '';
	return $icon;
}

?>