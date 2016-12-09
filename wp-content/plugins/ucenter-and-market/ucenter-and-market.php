<?php

/*
Plugin Name: UC
Plugin URI: http://www.baidu.com
Description: 为你的网站添加付费投稿功能
Version: 1.0
Author: Unknow
Author URI: http://www.baidu.com/
*/

?>
<?php
if ( !defined('ABSPATH') ) {exit;}
if ( !defined( 'UM_DIR' ) ) {
	define( 'UM_DIR', plugin_dir_path(__FILE__) );
}
if ( !defined( 'UM_URI' ) ) {
	define( 'UM_URI', plugin_dir_url(__FILE__) );
}
if ( !defined( 'UM_VER' ) ) {
	define( 'UM_VER', '1.1' );
}
if ( !defined( 'UM_TYPE' ) ) {
	define( 'UM_TYPE', 'release' );
}
if ( !defined( 'UM' ) ) {
	define( 'UM', 'ucenter&Market' );
}
require_once('func/functions.php');
require_once('func/setting-api.php');
require_once('func/affiliate.php');
require_once('func/follow.php');
require_once('func/membership.php');
require_once('func/shop.php');
require_once('func/credit.php');
require_once('func/message.php');
require_once('func/mail.php');
require_once('func/meta-box.php');
require_once('func/open-social.php');
require_once('func/extension.php');
require_once('template/loginbox.php');
require_once('template/order.php');
require_once('widgets/ucenter.php');
require_once('widgets/credits-rank.php');


/* Add admin menu */
if( is_admin() ) {
    add_action('admin_menu', 'display_um_menu');
}
function display_um_menu() {
    add_menu_page('UC', 'UC', 'administrator','ucenter_market', 'um_setting_page','dashicons-groups');
    add_submenu_page('ucenter_market', 'UC &gt; 设置', '插件设置', 'administrator','ucenter_market', 'um_setting_page');
}
function um_setting_page(){
	settings_errors();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
	        <a class="nav-tab" href="javascript:;" id="tab-title-ucenter">用户中心设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-mail">邮件设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-social">社会化登录设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-other">其他设置</a>      
	    </h2>
		<form action="options.php" method="POST">
			<?php settings_fields( 'ucenter_market_group' ); ?>
			<?php
				settings_errors();
				$labels = um_get_option_labels();
				extract($labels);
			?>
			<?php foreach ( $sections as $section_name => $section ) { ?>
	            <div id="tab-<?php echo $section_name; ?>" class="div-tab hidden">
	                <?php um_option_do_settings_section($option_page, $section_name); ?>
	            </div>                      
	        <?php } ?>
			<input type="hidden" name="<?php echo $option_name;?>[current_tab]" id="current_tab" value="" />
			<?php submit_button(); ?>
		</form>
		<?php um_option_tab_script(); ?>
	</div>
<?php
}

/* Active page html */
function um_setting_active_page(){
	settings_errors();
	$order = um_get_setting('order');
	$sn = um_get_setting('sn');
	?>
	<div class="wrap">
		<form action="options.php" method="POST">
			<?php settings_fields( 'ucenter_market_group' ); ?>
			<?php
				settings_errors();
				$labels = um_get_option_labels();
				extract($labels);
			?>
			<?php foreach ( $sections as $section_name => $section ) { ?>
	            <div id="tab-<?php echo $section_name; ?>" class="div-tab <?php if($section_name!='auth') echo 'hidden'; ?>">
	                <?php um_option_do_settings_section($option_page, $section_name); ?>
	            </div>                      
	        <?php } ?>
			<input type="hidden" name="<?php echo $option_name;?>[current_tab]" id="current_tab" value="" />
			<?php submit_button(); ?>
		</form>
	</div>

<?php	
}


add_action('wp_ajax_upimg', 'upimg');
function upimg($url = null) {

	if ( ! function_exists( 'wp_handle_upload' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	$uploadedfile = $_FILES['file'];

	$upload_overrides = array( 'test_form' => false );

	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

	if ( $movefile && !isset( $movefile['error'] ) ) {
	    $arr = array(
			'file' => $movefile['url'],
			'msg'  => '',
		);
		echo json_encode($arr);
	} else {
	    $arr = array(
			'file' => '',
			'msg'  => $movefile['error'],
		);
		echo json_encode($arr);
	}

	die();
}

function is_mobile() {
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$mobile_browser = Array(
		"mqqbrowser", //手机QQ浏览器
		"opera mobi", //手机opera
		"juc","iuc",//uc浏览器
		"fennec","ios","applewebKit/420","applewebkit/525","applewebkit/532","ipad","iphone","ipaq","ipod",
		"iemobile", "windows ce",//windows phone
		"240x320","480x640","acer","android","anywhereyougo.com","asus","audio","blackberry","blazer","coolpad" ,"dopod", "etouch", "hitachi","htc","huawei", "jbrowser", "lenovo","lg","lg-","lge-","lge", "mobi","moto","nokia","phone","samsung","sony","symbian","tablet","tianyu","wap","xda","xde","zte"
	);
	$is_mobile = false;
	foreach ($mobile_browser as $device) {
		if (stristr($user_agent, $device)) {
			$is_mobile = true;
			break;
		}
	}
	return $is_mobile;
}