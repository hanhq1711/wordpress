<?php
/**
 * Main Template of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.6.13
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com>
 * @copyright Copyright (c) 2015-2015, Zhiyan
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
**/
?>
<?php
global $wp_query;
// Current author
$curauth = $wp_query->get_queried_object();
$user_name = filter_var($curauth->user_url, FILTER_VALIDATE_URL) ? '<a href="'.$curauth->user_url.'" target="_blank" rel="external">'.$curauth->display_name.'</a>' : $curauth->display_name;
$user_info = get_userdata($curauth->ID);
$posts_count =  $wp_query->found_posts;
$comments_count = get_comments( array('status' => '1', 'user_id'=>$curauth->ID, 'count' => true) );
$collects = $user_info->um_collect?$user_info->um_collect:0;
$collects_array = explode(',',$collects);
$collects_count = $collects!=0?count($collects_array):0;
$credit = intval($user_info->um_credit);
$credit_void = intval($user_info->um_credit_void);
// Current user
$current_user = wp_get_current_user();
// Myself?
$oneself = $current_user->ID==$curauth->ID || current_user_can('edit_users') ? 1 : 0;
// Admin ?
$admin = $current_user->ID==$curauth->ID&&current_user_can('edit_users') ? 1 : 0;
// Tabs
$top_tabs = array(
	'index' => __('控制板','um'),
	'post' => __('个人专栏','um')."($posts_count)",
	'comment' => __('评论','um')."($comments_count)",
	'collect' => __('收藏','um')."($collects_count)",
	'credit' => __('牛币','um')."($credit)",
	'message' => __('消息','um')
);

$manage_tabs = array(
	'profile' => __('个人资料','um')
);
if($oneself){$manage_tabs['membership']='会员信息';}
if($oneself)$manage_tabs['orders']='站内订单';
if($admin)$manage_tabs['siteorders']='订单管理';
$manage_tabs['affiliate']='我的推广';
if($admin)$manage_tabs['coupon']='优惠码';

$other_tabs = array(
	'following' => __('关注','um'),
	'follower' => __('粉丝','um')
);

$tabs = array_merge($top_tabs,$manage_tabs,$other_tabs);
foreach( $tabs as $tab_key=>$tab_value ){
	if( $tab_key ) $tab_array[] = $tab_key;
}

// Current tab
$get_tab = isset($_GET['tab']) && in_array($_GET['tab'], $tab_array) ? $_GET['tab'] : 'index';

// 提示
$message = $pages = '';

$item_html = '<li class="tip">'.__('没有找到记录','um').'</li>';

?>
<!-- Header -->
<?php get_header(); ?>

<!-- Main Wrap -->

<div id="main-wrap" class="content page dashboard space centralnav">
  <div id="author-page" class="primary bd clx" role="main">
	<!-- Cover -->

	<!-- Cover change -->

	<!-- Author info -->

	<!-- Main content -->

	<!-- Aside -->
	<?php include('u/aside.php'); ?>
	<!-- Content -->
		<!-- Tab-index -->
		<?php 
			if( $get_tab=='index' ) {include('u/index.php');}
		?>
		<!-- End Tab-index -->
		<!-- Tab-post -->
		<?php if( $get_tab=='post' ) {
			if(isset($_GET['action'])&&in_array($_GET['action'],array('new','edit')))include('u/newpost.php');
			else include('u/post.php');
		} ?>
		<!-- End Tab-post -->
		<!-- Tab-comment -->
		<?php 
			if( $get_tab=='comment' ) {include('u/comment.php');}
		?>
		<!-- End Tab-comment -->
		<!-- Tab-collect -->
		<?php 
			if( $get_tab=='collect'){include('u/collect.php');}
		?>
		<!-- End Tab-collect -->
		<!-- Tab-message -->
		<?php
			if( $get_tab=='message' ) {include('u/message.php');}
		?>
		<!-- End Tab-message -->
		<!-- Tab-credit -->
		<?php
			if( $get_tab=='credit' ) {include('u/credit.php');}
		?>
		<!-- End Tab-credit -->
		<!-- Tab-profile -->
		<?php
			if( $get_tab=='profile' ) {include('u/profile.php');}
		?>
		<!-- End Tab-profile -->
		<!-- Tab-orders -->
		<?php
			if( $get_tab=='orders' ) {include('u/order.php');}
		?>
		<!-- End Tab-orders -->
		<!-- Tab-siteorders -->
		<?php
			if( $get_tab=='siteorders' ) {include('u/siteorder.php');}
		?>
		<!-- End Tab-siteorders -->
		<!-- Tab-coupon -->
		<?php
			if( $get_tab=='coupon' ) {include('u/coupon.php');}
		?>
		<!-- End Tab-coupon -->
		<!-- Tab-following -->
		
		<!-- End Tab-following -->
		<!-- Tab-follower -->

		<!-- End Tab-follower -->
		<!-- Tab-affiliate -->
		<?php
			if( $get_tab=='affiliate' ) {include('u/affiliate.php');}
		?>
		<!-- End Tab-affiliate -->
	<!-- End Right Content -->
	</div><!-- End #author-page -->
</div><!-- End #main-wrap -->

<!-- Footer -->
<?php get_footer(); ?>