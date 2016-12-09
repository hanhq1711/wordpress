<?php
/* Create database for orders and coupon code */
function create_orders_table(){
		global $wpdb;
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
		$table_charset = '';
		$prefix = $wpdb->prefix;
		$orders_table = $prefix.'um_orders';
		$coupons_table = $prefix.'um_coupons';
		if($wpdb->has_cap('collation')) {
			if(!empty($wpdb->charset)) {
				$table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if(!empty($wpdb->collate)) {
				$table_charset .= " COLLATE $wpdb->collate";
			}		
		}
		$create_orders_sql = "CREATE TABLE $orders_table (id int(11) NOT NULL auto_increment,order_id varchar(30) NOT NULL,trade_no varchar(30) NOT NULL,product_id int(20) NOT NULL,product_name varchar(250),order_time datetime NOT NULL default '0000-00-00 00:00:00',order_success_time datetime NOT NULL default '0000-00-00 00:00:00',order_price double(10,2) NOT NULL,order_currency varchar(20) NOT NULL default 'credit',order_quantity int(11) NOT NULL,order_total_price double(10,2) NOT NULL,order_status tinyint(4) NOT NULL default 0,order_note text,user_id int(11) NOT NULL,aff_user_id int(11),aff_rewards double(10,2),user_name varchar(60),user_email varchar(100),user_address varchar(250),user_zip varchar(10),user_phone varchar(20),user_cellphone varchar(20),user_message text,user_alipay varchar(100),PRIMARY KEY (id),INDEX orderid_index(order_id),INDEX tradeno_index(trade_no),INDEX productid_index(product_id),INDEX uid_index(user_id),INDEX affuid_index(aff_user_id)) ENGINE = MyISAM $table_charset;";
		maybe_create_table($orders_table,$create_orders_sql);
		$create_coupons_sql = "CREATE TABLE $coupons_table (id int(11) NOT NULL auto_increment,coupon_code varchar(20) NOT NULL,coupon_type varchar(20) NOT NULL default 'once',coupon_status int(11) NOT NULL default 1,discount_value double(10,2) NOT NULL default 0.90,expire_date datetime NOT NULL default '0000-00-00 00:00:00',PRIMARY KEY (id),INDEX couponcode_index(coupon_code)) ENGINE = MyISAM $table_charset;";
		maybe_create_table($coupons_table,$create_coupons_sql);
}
add_action('admin_menu','create_orders_table');

/* Add custom post type for market */
function create_store_post_type() {
    register_post_type( 'store',
        array(
            'labels' => array(
                'name' => _x( '商品', 'taxonomy general name' ),
                'singular_name' => _x( '商品', 'taxonomy singular name' ),
                'add_new' => __( '添加商品', 'um' ),
                'add_new_item' => __( '添加新商品', 'um' ),
                'edit' => __( '编辑', 'um' ),
                'edit_item' => __( '编辑商品', 'um' ),
                'new_item' => __( '新商品', 'um' ),
                'view' => __( '浏览', 'um' ),
				'all_items' => __( '所有商品', 'um' ),
                'view_item' => __( '浏览商品', 'um' ),
                'search_items' => __( '搜索商品', 'um' ),
                'not_found' => __( '未找到商品', 'um' ),
                'not_found_in_trash' => __( '回收站未找到商品', 'um' ),
                'parent' => __( '父级商品', 'um' ),
				'menu_name' => __( '商品资源', 'um' ),
            ),
 
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'author', 'editor', 'comments', 'excerpt', 'thumbnail', 'custom-fields' ),
            'taxonomies' => array( '' ),
            'menu_icon' => 'dashicons-cart',
            'has_archive' => true,
			'rewrite'	=> array('slug'=>um_get_setting('store_archive_slug','store'))
        )
    );
}
//add_action( 'init', 'create_store_post_type' );

/* Load product and product archives template */
function include_store_template_function( $template_path ) {
    if ( get_post_type() == 'store' ) {
        if ( is_single() ) {
            $template_path = UM_DIR.'/template/product.php';
        }elseif(is_archive()){
        	$template_path = UM_DIR.'/template/product-archives.php';
		}
    }
    return $template_path;
}
add_filter( 'template_include', 'include_store_template_function' );
//add_action('template_redirect','include_store_template_function');

/* Update rewrite rules */
function um_update_rewrite_rules(){
	flush_rewrite_rules();
}
add_action('update_option_ucenter_market','um_update_rewrite_rules');

/* Add tag and category function for products */
function create_store_taxonomies() {
	$cat_pre = um_get_setting('store_cat_pre');
	$cat_pre = empty($cat_pre)?'':'/'.$cat_pre;
	$tag_pre = um_get_setting('store_tag_pre');
	$tag_pre = empty($tag_pre)?'':'/'.$tag_pre;
	$store_slug = um_get_setting('store_archive_slug','store');
	// Categories
	$products_category_labels = array(
		'name' => _x( '商品分类', 'taxonomy general name' ),
		'singular_name' => _x( '商品分类', 'taxonomy singular name' ),
		'search_items' => __( '搜索商品分类', 'um' ),
		'all_items' => __( '所有商品分类', 'um' ),
		'parent_item' => __( '父级商品分类', 'um' ),
		'parent_item_colon' => __( '父级商品分类:', 'um' ),
		'edit_item' => __( '编辑商品分类', 'um' ), 
		'update_item' => __( '更新商品分类', 'um' ),
		'add_new_item' => __( '添加新商品分类', 'um' ),
		'new_item_name' => __( '新商品分类名称', 'um' ),
		'menu_name' => __( '商品分类', 'um' ),
	);  
	register_taxonomy( 'products_category', 'store', array(
			'hierarchical'  => true,
			'labels'        => $products_category_labels,
			'show_ui'       => true,
			'query_var'     => true,
			'rewrite'       => array( 
				'slug'          => $store_slug.$cat_pre,
				'with_front'    => false,
			),
	) );
	// Tags
	$products_tag_labels = array(
		'name' => _x( '商品标签', 'taxonomy general name' ),
		'singular_name' => _x( '商品标签', 'taxonomy singular name' ),
		'search_items' => __( '搜索商品标签', 'um' ),
		'popular_items' => __( '热门商品标签', 'um' ),
		'all_items' => __( '所有商品标签', 'um' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( '编辑商品标签', 'um' ), 
		'update_item' => __( '更新商品标签', 'um' ),
		'add_new_item' => __( '添加新商品标签', 'um' ),
		'new_item_name' => __( '新商品标签名称', 'um' ),
		'separate_items_with_commas' => __( '逗号分割不同商品标签', 'um' ),
		'add_or_remove_items' => __( '添加或移除商品标签', 'um' ),
		'choose_from_most_used' => __( '从最常用商品标签中选择', 'um' ),
		'menu_name' => __( '商品标签', 'um' ),
	); 

	register_taxonomy('products_tag', 'store', array(
		'hierarchical'  => false,
		'labels'        => $products_tag_labels,
		'show_ui'       => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'     => true,
		'rewrite'       => array( 
			'slug' => $store_slug.$tag_pre,
			'with_front'    => false,
		),
	) );
}
add_action( 'init', 'create_store_taxonomies', 0 );

/* Set permalink */
function custom_store_link( $link, $post = 0 ){
	$store_slug = um_get_setting('store_archive_slug','store');
	$product_slug = um_get_setting('product_link_mode')=='post_name'?$post->post_name:$post->ID;
	if ( $post->post_type == 'store' ){
		return home_url( $store_slug.'/goods/' . $product_slug .'.html' );
	} else {
		return $link;
	}
}
add_filter('post_type_link', 'custom_store_link', 1, 3);
function custom_store_rewrites_init(){
	$store_slug = um_get_setting('store_archive_slug','store');
	if(um_get_setting('product_link_mode')=='post_name'):
	add_rewrite_rule(
		$store_slug.'/goods/([一-龥a-zA-Z0-9_-]+)?.html([\s\S]*)?$',
		'index.php?post_type=store&name=$matches[1]',
		'top' );
	else:
	add_rewrite_rule(
		$store_slug.'/goods/([0-9]+)?.html([\s\S]*)?$',
		'index.php?post_type=store&p=$matches[1]',
		'top' );
	endif;
}
add_action( 'init', 'custom_store_rewrites_init' );

/* Products management column */
function store_columns( $columns ) {
    $columns['product_ID'] = '商品编号';
	$columns['product_price'] = '价格';
	$columns['product_quantity'] = '数量';
	$columns['product_sales'] = '销量';
    unset( $columns['comments'] );
    return $columns;
}
add_filter( 'manage_edit-store_columns', 'store_columns' );
function populate_columns( $column ) {
    if ( 'product_ID' == $column ) {
        $product_ID = esc_html( get_the_ID() );
        echo $product_ID;
    }
    elseif ( 'product_price' == $column ) {
        $product_price = get_post_meta( get_the_ID(), 'product_price', true ) ? get_post_meta( get_the_ID(), 'product_price', true ) : '0.00';
		$currency = get_post_meta( get_the_ID(), 'pay_currency', true );
		if($currency==0)$text='牛币';else $text = '元';
		$price = $product_price.' '.$text;
        echo $price;
    }elseif( 'product_quantity' == $column ){
		$product_quantity = get_post_meta( get_the_ID(), 'product_amount', true ) ? (int)get_post_meta( get_the_ID(), 'product_amount', true ) : 0;
		echo $product_quantity.' 件';
	}elseif( 'product_sales' == $column ){
		$product_sales = get_post_meta( get_the_ID(), 'product_sales', true ) ? (int)get_post_meta( get_the_ID(), 'product_sales', true ) : 0;
		echo $product_sales.' 件';
	}
}
add_action( 'manage_posts_custom_column', 'populate_columns' );

/* Products management column sort */
function sort_store_columns($columns){
	$columns['product_ID'] = '商品编号';
	$columns['product_price'] = '价格';
	$columns['product_quantity'] = '数量';
	$columns['product_sales'] = '销量';
	return $columns;
}
add_filter('manage_edit-store_sortable_columns','sort_store_columns');
function column_orderby($vars){
	if(!is_admin())
		return $vars;
	if(isset($vars['orderby'])&&'product_price'==$vars['orderby']){
		$vars = array_merge($vars,array('meta_key'=>'product_price','orderby'=>'meta_value'));
	}elseif(isset($vars['orderby'])&&'product_quantity'==$vars['orderby']){
		$vars = array_merge($vars,array('meta_key'=>'product_quantity','orderby'=>'meta_value'));
	}elseif(isset($vars['orderby'])&&'product_sales'==$vars['orderby']){
		$vars = array_merge($vars,array('meta_key'=>'product_sales','orderby'=>'meta_value'));
	}
	return $vars;
}
add_filter('request','column_orderby');

/* Products management column filter */
function store_filter_list() {
    $screen = get_current_screen();
    global $wp_query;
    if ( $screen->post_type == 'store' ) {
        wp_dropdown_categories( array(
            'show_option_all' => '显示所有分类',
            'taxonomy' => 'products_category',
            'name' => '商品分类',
			'id' => 'filter-by-products_category',
            'orderby' => 'name',
            'selected' => ( isset( $wp_query->query['products_category'] ) ? $wp_query->query['products_category'] : '' ),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ) );
    }
}
add_action( 'restrict_manage_posts', 'store_filter_list' );
function perform_filtering( $query ) {
    $qv = &$query->query_vars;
    if ( isset( $qv['products_category'] ) && is_numeric( $qv['products_category'] ) ) {
        $term = get_term_by( 'id', $qv['products_category'], 'products_category' );
        $qv['products_category'] = $term->slug;
    }
	return $query;
}
add_filter( 'parse_query','perform_filtering' );

// 获取商品售价
function um_get_product_price($product_id=0){
	if($product_id==0) $price = 0;
	else $price = get_post_meta($product_id,'product_price',true) ? get_post_meta($product_id,'product_price',true) : 0;
	return sprintf('%0.2f',$price);
}

// 获取商品折扣售价
function product_smallest_price($product_id){
	$original_price = um_get_product_price($product_id);
	$vip_discount = json_decode(get_post_meta($product_id,'product_vip_discount',true),true);
	$vip_discount = empty($vip_discount)?1:$vip_discount;
	$vip_discount1 = isset($vip_discount['product_vip1_discount'])?$vip_discount['product_vip1_discount']:1;
	$vip_discount2 = isset($vip_discount['product_vip2_discount'])?$vip_discount['product_vip2_discount']:1;
	$vip_discount3 = isset($vip_discount['product_vip3_discount'])?$vip_discount['product_vip3_discount']:1;
	$vip_discount4 = isset($vip_discount['product_vip4_discount'])?$vip_discount['product_vip4_discount']:1;
	if(is_user_logged_in()){$vip=getUserMemberType();$discount_type = $vip?'vip_discount'.$vip:'vip_discount';$vip_discount=$vip?$$discount_type:1;}else{$vip_discount=1;}
	$coupon_discount = get_post_meta($product_id,'product_coupon_discount',true);
	if($vip_discount<1&&$vip_discount>=0){$vip_price = $original_price*$vip_discount;}else{$vip_price = $original_price;}
	$discount_begin_date = get_post_meta($product_id,'product_discount_begin_date',true) ? get_post_meta($product_id,'product_discount_begin_date',true) : 0;
	$discount_period = get_post_meta($product_id,'product_discount_period',true) ? get_post_meta($product_id,'product_discount_period',true) : 0;
	if($discount_begin_date==0||$discount_period==0){
		$coupon_price=$original_price;
	}elseif(strtotime($discount_begin_date)<=time()&&strtotime('+'.$discount_period.' days',strtotime($discount_begin_date))>=time()){
		$coupon_price=$coupon_discount*$original_price;
	}else{
		$coupon_price=$original_price;
	}
	$vip_discount_arr = array($vip_discount1,$vip_discount2,$vip_discount3,$vip_discount4);
	sort($vip_discount_arr);
	$smallest_vip_price = sprintf('%0.2f',$vip_discount_arr[0]*$original_price);
	$vip_price_show = ($vip_discount_arr[0]<1&&$vip_discount_arr[0]<$coupon_discount)? 1:0;
	$coupon_price_show = ($coupon_price<$original_price)? 1:0;
	$price_arr = array($original_price,$vip_price,$coupon_price);
	sort($price_arr);
	$last_price = sprintf('%0.2f',$price_arr[0]);
	$price=array($original_price,$vip_price,$coupon_price,$vip_price_show,$coupon_price_show,$last_price,$smallest_vip_price);
	return $price;
}

//自动填充订单用户信息
function get_user_autofill_info(){
	$autofill = array();
	if(is_user_logged_in()){
		$current_user = wp_get_current_user(); 
		$autofill['user_name'] = $current_user->display_name;
		$autofill['user_email'] = $current_user->user_email;
		$id = $current_user->ID;
		global $wpdb;
		$prefix = $wpdb->prefix;
		$history_orders = $wpdb->get_Results("select * from ".$prefix."um_orders where user_id=".$id." order by id DESC",'ARRAY_A');
		if($history_orders){
			$order=$history_orders[0];
			return $order;
		}else{
			return $autofill;
		}		
	}else{
		return $autofill;
	}
}

//获取用户订单记录(可指定单独某件商品)
function get_user_order_records($product_id=0,$user_id=0,$success_orders=0){
	$record = array();
	if(is_user_logged_in()){
		$current_user = wp_get_current_user(); 
		$autofill['user_name'] = $current_user->display_name;
		if($user_id==0){$id = $current_user->ID;}else{$id=$user_id;}
		global $wpdb;
		$prefix = $wpdb->prefix;
		if($product_id==0):
			if($success_orders==0){$orders=$wpdb->get_Results("select * from ".$prefix."um_orders where user_id=".$id,'ARRAY_A');}else{$orders=$wpdb->get_Results("select * from ".$prefix."um_orders where order_status=4 and user_id=".$id,'ARRAY_A');}
		else:
			if($success_orders==0){$orders=$wpdb->get_Results("select * from ".$prefix."um_orders where user_id=".$id." and product_id=".$product_id,'ARRAY_A');}else{$orders=$wpdb->get_Results("select * from ".$prefix."um_orders where order_status=4 and user_id=".$id." and product_id=".$product_id,'ARRAY_A');}
		endif;
		$record = $orders;
	}
	return $record;
}

//获取某条订单记录
function get_the_order($order_id){
	global $wpdb;
	$prefix = $wpdb->prefix;
	$order=$wpdb->get_row("select * from ".$prefix."um_orders where order_id=".$order_id);
	return $order;
}

//输出交易状态
function output_order_status($code){
	switch($code){
		case 1:
			$status_text = '等待买家付款';
			break;
		case 2:
			$status_text = '已付款，等待卖家发货';
			break;
		case 3:
			$status_text = '已发货，等待买家确认';
			break;
		case 4:
			$status_text = '交易成功';
			break;
		case 9:
			$status_text = '交易关闭';
			break;
		default:
			$status_text = '订单建立成功';
	}
	return $status_text;
}

//产生订单号
function generate_order_num(){
	$orderNum = mt_rand(10,25).time().mt_rand(1000,9999);
	return $orderNum;
}

//使用优惠码更新总价
function update_coupon_code_total_price($code='',$total_price=0,$ajax=1){
	if(isset($_POST['coupon_code'])&&isset($_POST['order_total_price'])&&$ajax=1){$code=$_POST['coupon_code'];$total_price=$_POST['order_total_price'];}
	$success = 0;
	$new_total_price = $total_price;
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_coupons';
	$row=$wpdb->get_row("select * from ".$table." where coupon_code='".$code."'",'ARRAY_A');
	if(!$row){
		$msg = '优惠码不存在';
	}elseif($row['coupon_status']!=1||strtotime($row['expire_date'])<=time()){
		$msg = '优惠码已被使用或过期';
	}else{
		if($row['discount_value']<1){
			$new_total_price = sprintf('%0.2f',$total_price*$row['discount_value']);
			if($row['coupon_type']=='once'&&$ajax!=1)$wpdb->query( "UPDATE $table SET coupon_status=0 WHERE coupon_code='$code'" );
			$success = 1;
			$msg = '已成功使用优惠码';
		}else{
			$msg = '优惠码无效';
		}
	}
	if($ajax==1){
		$return = array('msg'=>$msg,'success'=>$success,'total_price'=>$new_total_price);
		echo json_encode($return);
		exit;
	}else{
		return $new_total_price;
	}
}
//add_action( 'wp_ajax_nopriv_use_coupon_code', 'update_coupon_code_total_price' );
add_action( 'wp_ajax_use_coupon_code', 'update_coupon_code_total_price' );

//插入订单记录
function insert_order($product_id,$product_name,$order_price='',$order_quantity,$order_total_price,$order_status=0,$order_note='',$user_id,$aff_user_id='',$rewards,$user_name,$user_email='',$user_address='',$user_zip='',$user_phone='',$user_cellphone='',$user_message=''){
	date_default_timezone_set ('Asia/Shanghai');
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_orders';
	$order_id = generate_order_num();
	$order_time = date("Y-m-d H:i:s");
	if(empty($order_price)){$order_price_arr = product_smallest_price($product_id);$order_price=$order_price_arr[5];}
	if($product_id>0){$order_currency = (get_post_meta($product_id,'pay_currency',true)!=1)?'credit':'cash';}else{$order_currency='cash';}
	if($wpdb->query( "INSERT INTO $table (order_id,product_id,product_name,order_time,order_price,order_currency,order_quantity,order_total_price,order_status,order_note,user_id,aff_user_id,aff_rewards,user_name,user_email,user_address,user_zip,user_phone,user_cellphone,user_message) VALUES ('$order_id','$product_id','$product_name','$order_time','$order_price','$order_currency','$order_quantity','$order_total_price','$order_status','$order_note','$user_id','$aff_user_id','$rewards','$user_name','$user_email','$user_address','$user_zip','$user_phone','$user_cellphone','$user_message')" )) return $order_id;
	return 0;
}

//创建订单(若牛币支付方式则直接支付)
function create_the_order(){
	$redirect = 0;
	$success = 0;
	$msg = '';
	$order_note = '';
	$order_id = 0;
	if (!wp_verify_nonce( trim($_POST['wp_nonce']), 'order-nonce' ) ){
		$msg = 'NonceIsInvalid';
	}else{
		$price = product_smallest_price($_POST['product_id']);
		//获取折扣后总价
		$cost = $price[5]*$_POST['order_quantity'];
		//获取使用优惠码后总价
		$coupon_support = get_post_meta($_POST['product_id'],'product_coupon_code_support',true);
		if($coupon_support==1&&!empty($_POST['coupon_code'])){
			$cost_coupond = update_coupon_code_total_price($_POST['coupon_code'],$cost,0);
			$order_note = json_encode(array('coupon_code'=>$_POST['coupon_code']));
		}else{
			$cost_coupond = $cost;
		}
		$currency = get_post_meta($_POST['product_id'],'pay_currency',true);
		$current_user = wp_get_current_user();
		$uid = $current_user->ID;
		$aff_uid = $_POST['aff_user_id']==$uid ? 0 :$_POST['aff_user_id'];
		if($currency==0){
			//使用积分直接支付
			//获取用户当前积分并判断是否足够消费
			$credit = (int)get_user_meta($uid,'um_credit',true);
			if($credit<$cost){
				//积分不足
				$msg = '牛币不足，立即<a href="'.um_get_user_url('credit').'" target="_blank">充值牛币</a>';
			}else{
				//插入数据库记录
				$ratio = um_get_setting('aff_ratio',10);
				$rewards = $cost*$ratio/100;
				$rewards = (int)$rewards;
				$insert = insert_order($_POST['product_id'],$_POST['order_name'],$price[5],$_POST['order_quantity'],$cost,4,$order_note,$uid,$aff_uid,$rewards,$_POST['receive_name'],$_POST['receive_email'],$_POST['receive_address'],$_POST['receive_zip'],$_POST['receive_phone'],$_POST['receive_mobile'],$_POST['order_msg']);
				if($insert):
				//扣除积分//发送站内信
				update_um_credit( $uid , $cost , 'cut' , 'um_credit' , '下载资源消费'.$cost.'牛币' );
				//推广者积分
				if($aff_uid>0){
					update_um_credit( $aff_uid , $rewards , 'add' , 'um_credit' , '推广用户消费'.$cost.'牛币，获得'.$rewards.'牛币奖励' );
				}
				//更新已消费积分
				if(get_user_meta($uid,'um_credit_void',true)){
					$void = get_user_meta($uid,'um_credit_void',true);
					$void = $void + $cost;
					update_user_meta($uid,'um_credit_void',$void);
				}else{
					add_user_meta( $uid,'um_credit_void',$cost,true );
				}
				//给资源发布者添加积分并更新积分消息记录
				$author = get_post_field('post_author',$_POST['product_id']);
				update_um_credit(  $author , $cost , 'add' , 'um_credit' , sprintf(__('你发布收费商品资源《%1$s》被其他用户购买，获得售价%2$s牛币','um') ,get_post_field('post_title',$_POST['product_id']),$cost) );//出售获得积分
				//更新资源购买次数与剩余数量
				update_success_order_product($_POST['product_id'],$_POST['order_quantity']);
				//发送邮件
				$to = $_POST['receive_email'];
				$dl_links = get_post_meta($_POST['product_id'],'product_download_links',true);
				$pay_content = get_post_meta($_POST['product_id'],'product_pay_content',true);
				//如果包含付费可见下载链接则附加链接内容至邮件
				if(!empty($dl_links)||!empty($pay_content)){
					$title = '你在'.get_bloginfo('name').'购买的内容';
					$content = '<p>你在'.get_bloginfo('name').'使用牛币购买了以下内容:</p>';
					$content .= deal_pay_dl_content($dl_links);
					$content .= '<p style="margin-top:10px;">'.$pay_content.'</p><p>感谢你的来访与支持，祝生活愉快！</p>';			
				}else{
					$title = '感谢你在'.get_bloginfo('name').'使用牛币付费购买资源';
					$content = '<p>你在'.get_bloginfo('name').'使用牛币付费购买资源'.$_POST['order_name'].'</p><p>支付已成功,扣除了你'.$cost.'牛币。</p><p>感谢你的来访与支持，祝生活愉快！</p>';
				}
				$type = '牛币商城';
				um_basic_mail('',$to,$title,$content,$type);
				$msg = '购买成功，已扣除'.$cost.'牛币';
				$success = 1;
				else:
					$msg = '创建订单失败，请重新再试';
				endif;
			}
		}else{
			$ratio = um_get_setting('aff_ratio',10);
			$rewards = $cost*$ratio/100;
			$rewards = sprintf('%0.2f',$rewards);
			//现金支付方式，首先插入数据库订单记录
			$insert = insert_order($_POST['product_id'],$_POST['order_name'],$price[5],$_POST['order_quantity'],$cost_coupond,1,$order_note,$uid,$aff_uid,$rewards,$_POST['receive_name'],$_POST['receive_email'],$_POST['receive_address'],$_POST['receive_zip'],$_POST['receive_phone'],$_POST['receive_mobile'],$_POST['order_msg']);
			if($insert){
				$redirect = 1;
				$success = 1;
				$order_id = $insert;
				if(!empty($_POST['receive_email'])) {store_email_template($order_id,'',$_POST['receive_email']);}
			}else{
				$msg = '创建订单失败，请重新再试';
			}
		}
	}
	$return = array('success'=>$success,'msg'=>$msg,'redirect'=>$redirect,'order_id'=>$order_id);
	echo json_encode($return);
	exit;	
}
//add_action( 'wp_ajax_nopriv_create_order', 'create_the_order' );
add_action( 'wp_ajax_create_order', 'create_the_order' );

//继续支付
function continue_the_order(){
	$redirect = 0;
	$success = 0;
	$msg = '';
	$id = isset($_POST['id'])?(int)$_POST['id']:0;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_orders';
	$order = $wpdb->get_row( "SELECT id,order_id,product_id,product_name,order_time,order_price,order_currency,order_quantity,order_total_price,order_status,user_name,user_email FROM $table_name WHERE id=".$id );
	if($order){
		if($order->order_currency=='credit'&&$order->order_status==1):
			$return = credit_pay($order->order_total_price,$order->id,$order->product_name,$order->order_quantity,$order->user_email);
			$success = $return['success'];
			$msg = $return['msg'];
		elseif($order->order_currency=='cash'&&$order->order_status==1):
			$redirect = 1;
			$success = 1;
			if(!empty($order->user_email)) {store_email_template($order->order_id,'',$order->user_email);}
		else
			exit;
		endif;
	}else{
		$msg = '未找到该订单，请刷新页面重试';
	}
	echo json_encode(array('redirect'=>$redirect,'success'=>$success,'msg'=>$msg,'order'=>$order));
	exit;
}
//add_action( 'wp_ajax_nopriv_continue_order', 'continue_the_order' );
add_action( 'wp_ajax_continue_order', 'continue_the_order' );

function credit_pay($cost,$product_id,$order_name,$order_quantity,$receive_email){
	$success = 0;
	$msg = '';
	$uid = get_current_user_id();
	$credit = (int)get_user_meta($uid,'tin_credit',true);
	if($credit<$cost){
		$msg = '牛币不足，立即<a href="'.tin_get_user_url('credit').'" target="_blank">充值牛币</a>';
	}else{
		update_tin_credit( $uid , $cost , 'cut' , 'tin_credit' , '下载资源消费'.$cost.'牛币' );
		if(get_user_meta($uid,'tin_credit_void',true)){
			$void = get_user_meta($uid,'tin_credit_void',true);
			$void = $void + $cost;
			update_user_meta($uid,'tin_credit_void',$void);
		}else{
			add_user_meta( $uid,'tin_credit_void',$cost,true );
		}
		$author = get_post_field('post_author',$product_id);
		update_tin_credit(  $author , $cost , 'add' , 'tin_credit' , sprintf(__('你发布收费商品资源《%1$s》被其他用户购买，获得售价%2$s牛币','tinection') ,get_post_field('post_title',$product_id),$cost) );
		update_success_order_product($product_id,$order_quantity);
		$to = $receive_email;
		$dl_links = get_post_meta($product_id,'product_download_links',true);
		$pay_content = get_post_meta($product_id,'product_pay_content',true);
		if(!empty($dl_links)||!empty($pay_content)){
			$title = '你在'.get_bloginfo('name').'购买的内容';
			$content = '<p>你在'.get_bloginfo('name').'使用牛币购买了以下内容:</p>';
			$content .= deal_pay_dl_content($dl_links);
			$content .= '<p style="margin-top:10px;">'.$pay_content.'</p><p>感谢你的来访与支持，祝生活愉快！</p>';			
		}else{
			$title = '感谢你在'.get_bloginfo('name').'使用牛币付费购买资源';
			$content = '<p>你在'.get_bloginfo('name').'使用牛币付费购买资源'.$order_name.'</p><p>支付已成功,扣除了你'.$cost.'牛币。</p><p>感谢你的来访与支持，祝生活愉快！</p>';
		}
		$type = '牛币商城';
		tin_basic_mail('',$to,$title,$content,$type);
		$msg = '购买成功，已扣除'.$cost.'牛币';
		$success = 1;
	}
	return array('success'=>$success,'msg'=>$msg);
}

//商品中下载信息等付费内容处理
function deal_pay_dl_content($dl_links){
	$content = '';
	if(!empty($dl_links)):
	$arr_links = explode(PHP_EOL,$dl_links);
	foreach($arr_links as $arr_link){
		$arr_link = explode('|',$arr_link);
		$arr_link[0] = isset($arr_link[0]) ? $arr_link[0]:'';
		$arr_link[1] = isset($arr_link[1]) ? $arr_link[1]:'';
		$arr_link[2] = isset($arr_link[2]) ? $arr_link[2]:'';
		$content .= '<li><p>'.$arr_link[0].'</p><p>下载链接：<a href="'.$arr_link[1].'" title="'.$arr_link[0].'" target="_blank">'.$arr_link[1].'</a>下载密码：'.$arr_link[2].'</p></li>';
	}
	endif;
	return $content;
}

function store_pay_content_show($content){
	$hidden_content = '';
	$content = do_shortcode($content);
	$content = wpautop($content);
	if(is_single()){
		$price = product_smallest_price(get_the_ID());//get_post_meta(get_the_ID(),'product_price',true);
		$dl_links = get_post_meta(get_the_ID(),'product_download_links',true);
		$pay_content = get_post_meta(get_the_ID(),'product_pay_content',true);
		$hidden_content = deal_pay_dl_content($dl_links);
		$hidden_content .= $pay_content;
		if($price[5]==0||count(get_user_order_records(get_the_ID(),0,1))>0):
			$see_content = empty($hidden_content)?$content:$content.'<div class="label-title"><span id="title"><i class="fa fa-paypal"></i>&nbsp;付费内容</span><p>'.$hidden_content.'</p></div>';
		else:
			$see_content = empty($hidden_content)?$content:$content.'<div class="label-title"><span id="title"><i class="fa fa-paypal"></i>&nbsp;付费内容</span><p>你只有购买支付后才能查看该内容！</p></div>';
		endif;
	}else{
		$see_content = $content;
	}
	return $see_content;
}

//现金订单支付成功后更新对应商品的信息
function update_success_order_product($product_id,$amount_minus=1){
	if($product_id>0){
		$amount = (int)get_post_meta($product_id,'product_amount',true);
		$amount = $amount - $amount_minus;
		if($amount<0)$amount = 0;
		update_post_meta($product_id,'product_amount',$amount);
		$sales = get_post_meta($product_id,'product_sales',true) ? (int)get_post_meta($product_id,'product_sales',true) : 0;
		$sales = $sales + $amount_minus;
		update_post_meta($product_id,'product_sales',$sales);
	}
}

//现金订单支付状态更新后通知购买者
//商城邮件模板
function store_email_template_wrap($user_name='',$content){
	$blogname =  get_bloginfo('name');
	$bloghome = get_bloginfo('url');
	$logo = um_get_setting('logo_img');
	$store_slug = um_get_setting('store_archive_slug','store');
	$html = '<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><meta name="viewport" content="target-densitydpi=device-dpi, width=800, initial-scale=1, maximum-scale=1, user-scalable=1"><style>a:hover{text-decoration:underline !important;}</style></head><body><div style="width:800px;margin: 0 auto;"><table width="800" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FBF8F1" style="border-radius:5px; overflow:hidden; border-top:4px solid #00c3b6; border-right:1px solid #dbd1ce; border-bottom:1px solid #dbd1ce; border-left:1px solid #dbd1ce;font-family:微软雅黑;"><tbody><tr><td><table width="800" border="0" align="center" cellpadding="0" cellspacing="0" height="48"><tbody><tr><td width="74" height="35" border="0" align="center" valign="middle" style="padding-left:20px;"><a href="'.$bloghome.'" target="_blank" style="text-decoration: none;">';
	if(!empty($logo)) {$html .= '<img style="vertical-align:middle;background-color:#838fa8" src="'.$logo.'" height="35" border="0">';}else{$html .= '<span style="vertical-align:middle;font-size:20px;line-height:32px;white-space:nowrap;">'.$blogname.'</span>';}
	$html .= '</a></td><td width="703" height="48" colspan="2" align="right" valign="middle" style="color:#333; padding-right:20px;font-size:14px;font-family:微软雅黑"><a style="padding:0 10px;text-decoration:none;" target="_blank" href="'.$bloghome.'">首页</a><a style="padding:0 10px;text-decoration:none;" target="_blank" href="'.$bloghome.'/articles">文章</a><a style="padding:0 10px;text-decoration:none;" target="_blank" href="'.$bloghome.'/'.$store_slug.'">商城</a></td></tr></tbody></table></td></tr><tr><td><div style="padding:10px 20px;font-size:14px;color:#333333;border-top:1px solid #dbd1ce;font-family:微软雅黑">';
	if(!empty($user_name)){$html .= '<p><strong>亲爱的会员'.$user_name.' 您好：</strong></p><p>感谢您在'.$blogname.'( <a target="_blank" href="'.$bloghome.'">'.$bloghome.'</a>)购物!<br></p>';}else{$html .='';}
	$html .= $content;
	$html .= '<p style="padding:10px 0;margin-top:30px;margin-bottom:0;color:#a8979a;font-size:12px;border-top:1px dashed #dbd1ce;">此为系统邮件请勿回复<span style="float:right">&copy;&nbsp;'.date('Y').'&nbsp;'.$blogname.'</span></p></div></td></tr></tbody></table></div></body></html>';
	return $html;
}

function store_email_template($order_id,$from='',$to,$title=''){
	$blogname =  get_bloginfo('name');
	$order = get_the_order($order_id);
	$order_url = um_get_user_url('orders',$order->user_id);
	$admin_email = get_bloginfo ('admin_email');
	$user_name = $order->user_name;
	$user_ucenter_url = get_author_posts_url($order->user_id);
	$product_name = $order->product_name;
	$order_status_text = output_order_status($order->order_status);
	$order_total_price = $order->order_total_price;
	$order_time = $order->order_time;
	$content = '<p>以下是您的订单最新信息，您可进入“<a target="_blank" href="'.$order_url.'">订单详情</a>”页面随时关注订单状态，如有任何疑问，请及时联系我们（Email:<a href="mailto:'.$admin_email.'" target="_blank">'.$admin_email.'</a>）。</p><div style="background-color:#fefcc9; padding:10px 15px; border:1px solid #f7dfa4; font-size: 12px;line-height:160%;">商品名：'.$product_name.'<br>订单号：'.$order_id.'<br>总金额：'.$order_total_price.'<br>下单时间：'.$order_time.'<br>交易状态：<strong>'.$order_status_text.'</strong></div>';
	$html = store_email_template_wrap($user_name,$content);
	if(empty($from)){$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));}else{$wp_email=$from;}
	if(empty($title)){$title=$blogname.'商城提醒';}
	$fr = "From: \"" . $blogname . "\" <$wp_email>";
	$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
	wp_mail( $to, $title, $html, $headers );
	//如果交易成功通知管理员
	if($order->order_status==4){
		$content_admin = '<p>你的站点有新完成的支付宝交易订单,以下是订单信息:<div style="background-color:#fefcc9; padding:10px 15px; border:1px solid #f7dfa4; font-size: 12px;line-height:160%;">买家名：<a href="'.$user_ucenter_url.'" title="用户个人中心" target="_blank">'.$user_name.'</a><br>商品名：'.$product_name.'<br>订单号：'.$order_id.'<br>总金额：'.$order_total_price.'<br>下单时间：'.$order_time.'<br>交易状态：<strong>'.$order_status_text.'</strong></div>';
		$html_admin = store_email_template_wrap('',$content_admin);
		wp_mail( $admin_email, $title, $html_admin, $headers );
	}
}

// 发货(内容、下载链接、会员到账、积分到账)
function send_goods_by_order($order_id,$from='',$to,$title=''){
	$order = get_the_order($order_id);
	$product_id = $order->product_id;
	$blogname = get_bloginfo('name');
	$user_id = $order->user_id;
	$user_name = $order->user_name;
	if($product_id>0){
		$dl_links = get_post_meta($product_id,'product_download_links',true);
		$pay_content = get_post_meta($product_id,'product_pay_content',true);
		//如果包含付费可见下载链接则附加链接内容至邮件
		if(!empty($dl_links)||!empty($pay_content)){
			$content = '<p>你在'.$blogname.'商城付费购买了以下内容:</p>';
			$content .= deal_pay_dl_content($dl_links);
			$content .= '<p style="margin-top:10px;">'.$pay_content.'</p><p>感谢你的支持，祝生活愉快！</p>';			
		}else{
			$content = '<p>你在'.$blogname.'商城付费购买了资源'.$order->product_name.'已成功支付'.$order->order_total_price.'元。</p><p>感谢你的来访与支持，祝生活愉快！</p>';
		}
		$html = store_email_template_wrap($user_name,$content);
		if(empty($from)){$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));}else{$wp_email=$from;}
		if(empty($title)){$title=$blogname.'商城提醒';}
		$fr = "From: \"" . $blogname . "\" <$wp_email>";
		$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
		wp_mail( $to, $title, $html, $headers );		
	}elseif($product_id==-1){
		elevate_user_vip(1,$user_id,$user_name,$from,$to);
	}elseif($product_id==-2){	
		elevate_user_vip(2,$user_id,$user_name,$from,$to);
	}elseif($product_id==-3){
		elevate_user_vip(3,$user_id,$user_name,$from,$to);
	}elseif($product_id==-4){
		elevate_user_vip(4,$user_id,$user_name,$from,$to);
	}elseif($product_id==-5){
		add_user_credits_by_order($order->order_total_price,$user_id,$user_name,$from,$to);
	}else{
	
	}
}

//添加优惠码
function add_um_couponcode($p_code,$p_type,$p_discount,$p_expire_date){
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_coupons';
	$row=$wpdb->query("INSERT INTO $table (coupon_code,coupon_type,discount_value,expire_date) VALUES ('$p_code','$p_type','$p_discount','$p_expire_date')");
}

//删除优惠码
function delete_um_couponcode($p_id){
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_coupons';
	$row=$wpdb->query("DELETE FROM $table WHERE id='".$p_id."'");
}

// 输出优惠码
function output_um_couponcode(){
	global $wpdb;
	$prefix = $wpdb->prefix;
	$table = $prefix.'um_coupons';
	$couponcodes = $wpdb->get_Results("SELECT * FROM $table ORDER BY id DESC",'ARRAY_A');
	return $couponcodes;
}

// 创建积分充值订单
function create_credit_recharge_order(){
	$success = 0;
	$order_id = '';
	$msg = '';
	$credits = ((int)$_POST['creditrechargeNum'])*100;
	$order_name = '充值'.$credits.'牛币';
	$product_id = $_POST['product_id'];
	if(!is_user_logged_in()){$msg='请先登录';}else{
		$user_info = wp_get_current_user();$uid = $user_info->ID;$user_name=$user_info->display_name;$user_email = $user_info->user_email;
		if($product_id!=-5){$msg='系统发生错误，请刷新再试';}else{
			$order_price = $credits/(int)um_get_setting('exchange_ratio',100);
			$order_price = sprintf('%0.2f',$order_price);
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
	}
	$return = array('success'=>$success,'msg'=>$msg,'order_id'=>$order_id);
	echo json_encode($return);
	exit;
}
add_action( 'wp_ajax_nopriv_create_credit_recharge_order', 'create_credit_recharge_order' );
add_action( 'wp_ajax_create_credit_recharge_order', 'create_credit_recharge_order' );

// 支付成功添加积分
function add_user_credits_by_order($money,$user_id,$user_name,$from,$to){
	date_default_timezone_set ('Asia/Shanghai');
	$ratio = um_get_setting('exchange_ratio',100);
	$credits = (int)($ratio*$money);
	$admin_email = get_bloginfo ('admin_email');
	$blogname = get_bloginfo('name');
	//add credits and msg
	update_um_credit( $user_id , $credits , 'add' , 'um_credit' , sprintf(__('充值牛币%1$s，花费%2$s元','um') , $credits,$money) );
	//email
	$content = '<p>您已成功充值了'.$credits.'牛币，当前牛币为：'.$new_credits.'，如有任何疑问，请及时联系我们（Email:<a href="mailto:'.$admin_email.'" target="_blank">'.$admin_email.'</a>）。</p>';
	$html = store_email_template_wrap($user_name,$content);
	if(empty($from)){$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));}else{$wp_email=$from;}
	$title='会员状态变更提醒';
	$fr = "From: \"" . $blogname . "\" <$wp_email>";
	$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
	wp_mail( $to, $title, $html, $headers );
}

// 获取订单（包括会员订单和商品订单）
function get_um_orders( $uid=0 , $count=0, $where='', $limit=0, $offset=0 ){
	$uid = intval($uid);
	$where_prefix = '';
	if( $uid != 0 ) {
		$where_prefix = "WHERE user_id='".$uid."'";
		if($where) $where = $where_prefix." AND ($where)";else $where = $where_prefix;
	}else{
		if($where) $where = "WHERE ($where)";
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_orders';
	if($count){		
		$check = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );
	}else{
		$check = $wpdb->get_results( "SELECT id,order_id,product_name,order_time,order_price,order_quantity,order_total_price,order_status,user_name FROM $table_name $where ORDER BY id DESC LIMIT $offset,$limit" );
	}
	if($check)	return $check;
	return 0;
}

// 关闭过期订单
function close_expire_order(){
	$success = 0;
	$msg = '';
	$id = $_POST['id'];
	if(empty($id)||!current_user_can('edit_users')){$msg='系统出错或你无权限执行该动作';}else{
		global $wpdb;
		$prefix = $wpdb->prefix;
		$table = $prefix.'um_orders';
		$id = intval($id);
		$check = $wpdb->get_row("select * from ".$table." where id=".$id);
		if($check){
			$wpdb->query( "UPDATE $table SET order_status=9, order_success_time=NOW() WHERE id='$id'" );
			if(!empty($check->user_email))store_email_template($check->order_id,'',$check->user_email);
			$success = 1;
		}
	}
	$return = array('success'=>$success,'msg'=>$msg);
	echo json_encode($return);
	exit;
}
add_action( 'wp_ajax_closeorder', 'close_expire_order' );
//add_action( 'wp_ajax_nopriv_closeorder', 'close_expire_order' );

/* Set UM value */
if ( !defined( 'UM_VAL' ) ) {
	define( 'UM_VAL', 1 );
}

?>