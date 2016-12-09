<?php
function um_message_install(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'um_message';   
    if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) :   
		$sql = " CREATE TABLE `$table_name` (
			`msg_id` int NOT NULL AUTO_INCREMENT, 
			PRIMARY KEY(msg_id),
			INDEX uid_index(user_id),
			INDEX mtype_index(msg_type),
			INDEX mdate_index(msg_date),
			`user_id` int,
			`msg_type` varchar(20),
			`msg_date` datetime,
			`msg_title` tinytext,
			`msg_content` text
		) ENGINE = MyISAM CHARSET=utf8;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');   
			dbDelta($sql);   
    endif;
}
add_action( 'admin_menu', 'um_message_install' ); 
function add_um_message( $uid=0, $type='', $date='', $title='', $content='' ){

	$uid = intval($uid);
	$title = sanitize_text_field($title);
	
	if( !$uid || empty($title) ) return;

	$type = $type ? sanitize_text_field($type) : 'unread';
	$date = $date ? $date : current_time('mysql');
	$content = htmlspecialchars($content);
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_message';

	if($wpdb->query( "INSERT INTO $table_name (user_id,msg_type,msg_date,msg_title,msg_content) VALUES ('$uid', '$type', '$date', '$title', '$content')" ))
		return 1;
	
	return 0;
	
}
add_action( 'add_um_message_event', 'add_um_message', 10, 5 );
function update_um_message_type( $id=0, $uid=0, $type='' ){

	$id = intval($id);
	$uid = intval($uid);

	if( ( !$id || !$uid) || empty($type) ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'um_message';

	if( $id===0 ){
		$sql = " UPDATE $table_name SET msg_type = '$type' WHERE user_id = '$uid' ";
	}else{
		$sql = " UPDATE $table_name SET msg_type = '$type' WHERE msg_id = '$id' ";
	}

	if($wpdb->query( $sql ))
		return 1;
	
	return 0;
	
}
add_action( 'update_um_message_type_event', 'update_um_message_type', 10, 3 );
function get_um_message( $uid=0 , $count=0, $where='', $limit=0, $offset=0 ){
	
	$uid = intval($uid);
	
	if( !$uid ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'um_message';
	
	if($count){
		if($where) $where = " AND ($where)";
		$check = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE user_id='$uid' $where" );
	}else{
		$check = $wpdb->get_results( "SELECT msg_id,msg_type,msg_date,msg_title,msg_content FROM $table_name WHERE user_id='$uid' AND $where ORDER BY (CASE WHEN msg_type LIKE 'un%' THEN 1 ELSE 0 END) DESC, msg_date DESC LIMIT $offset,$limit" );
	}
	if($check)	return $check;

	return 0;

}
function get_um_credit_message( $uid=0 , $limit=0, $offset=0 ){
	
	$uid = intval($uid);
	
	if( !$uid ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'um_message';
	
	$check = $wpdb->get_results( "SELECT msg_id,msg_date,msg_title FROM $table_name WHERE msg_type='credit' AND user_id='$uid' ORDER BY msg_date DESC LIMIT $offset,$limit" );

	if($check)	return $check;

	return 0;

}
function get_um_pm( $pm=0, $from=0, $count=false, $single=false, $limit=0, $offset=0 ){
	
	$pm = intval($pm);
	$from = intval($from);
	
	if( !$pm || !$from ) return;

	global $wpdb;
	$table_name = $wpdb->prefix . 'um_message';
	
	$title_sql = $single ? "msg_title='{\"pm\":$pm,\"from\":$from}'" : "( msg_title='{\"pm\":$pm,\"from\":$from}' OR msg_title='{\"pm\":$from,\"from\":$pm}' )";
	
	if($count){
		$check = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE ( msg_type='repm' OR msg_type='unrepm' ) AND $title_sql" );
	}else{
		$check = $wpdb->get_results( "SELECT msg_id,msg_date,msg_title,msg_content FROM $table_name WHERE ( msg_type='repm' OR msg_type='unrepm' ) AND $title_sql ORDER BY msg_date DESC LIMIT $offset,$limit" );
	}
	if($check)	return $check;

	return 0;

}

?>