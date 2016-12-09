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
	'post' => __('文章','um')."($posts_count)",
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

// 页码start
$paged = max( 1, get_query_var('page') );
$number = get_option('posts_per_page', 10);
$offset = ($paged-1)*$number;

// Item html
$item_html = '<li class="tip">'.__('没有找到记录','um').'</li>';

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">您已发表<span><?php echo $comments_count; ?></span>条评论。</p>
				<p class="tip">提示：认真填写的点评都有可能被推荐为精彩评论哦。</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-comment">
<?php if(!$comments_count>0) { ?>
				<p>您还没发表过任何的评论。我们期待您的精彩点评。</p>
<?php }else{ ?>			
<?php
	$comments_status = $oneself ? '' : 'approve';
	$all = get_comments( array('status' => '', 'user_id'=>$curauth->ID, 'count' => true) );
	$approve = get_comments( array('status' => '1', 'user_id'=>$curauth->ID, 'count' => true) );
	$pages = $oneself ? ceil($all/$number) : ceil($approve/$number);
	$comments = get_comments(array('status' => $comments_status,'order' => 'DESC','number' => $number,'offset' => $offset,'user_id' => $curauth->ID));
	if($comments){
		$item_html = '<li class="tip">' . sprintf(__('共有 %1$s 条评论，其中 %2$s 条已获准， %3$s 条正等待审核。','um'),$all, $approve, $all-$approve) . '</li>';
		foreach( $comments as $comment ){
			$item_html .= ' <li>';
			if($comment->comment_approved!=1) $item_html .= '<small class="text-danger">'.__( '这条评论正在等待审核','um' ).'</small>';
			$item_html .= '<div class="message-content">'.$comment->comment_content .'</div>';
			$item_html .= '<a class="info" href="'.htmlspecialchars( get_comment_link( $comment->comment_ID) ).'">'.sprintf(__('%1$s  发表在  %2$s','um'),$comment->comment_date,get_the_title($comment->comment_post_ID)).'</a>';
			$item_html .= '</li>';
		}
		if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
	}
	echo '<ul class="user-msg">'.$item_html.'</ul>';
	echo um_pager($paged, $pages);
} ?>
			</div>
        </div>
    </div>
</div>