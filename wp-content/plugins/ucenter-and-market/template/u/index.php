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

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">用户中心</p>
				<p class="tip"><?php if($curauth->ID==$current_user->ID) echo '亲爱的 <a target="_blank" href="'.get_author_posts_url($curauth->ID).'">'.$curauth->display_name.'</a> 欢迎回来。'; else echo '欢迎，您正在查看'.$curauth->display_name.'的个人中心'; ?></p>
			</div>
            <div class="dashboard-wrapper select-index">
                <div class="briefly">
					<ul>
						<li class="post">
							<div class="visual"><i class="fa fa-tasks"></i></div>
							<div class="number"><?php echo $posts_count; ?><span>文章作品</span></div>
							<div class="more"><a href="<?php echo um_get_user_url('post',$curauth->ID); ?>">查看更多<i class="fa fa-arrow-circle-right"></i></a></div>
						</li>
						<li class="photo">
							<div class="visual"><i class="fa fa-heart"></i></div>
							<div class="number"><?php echo $collects_count; ?><span>我的收藏</span></div>
							<div class="more"><a href="<?php echo um_get_user_url('collect',$curauth->ID); ?>">查看更多<i class="fa fa-arrow-circle-right"></i></a></div>
						</li>
						<li class="comments">
						  <div class="visual"><i class="fa fa-comments"></i></div>
						  <div class="number"><?php echo $comments_count; ?><span>评论留言</span></div>
						  <div class="more"><a href="<?php echo um_get_user_url('comment',$curauth->ID); ?>">查看更多<i class="fa fa-arrow-circle-right"></i></a></div>
						</li>
					</ul>
				</div>
				<div class="summary">
					<div class="box">
						<div class="title">我的最近发布</div>
						<ul>
						<?php if(!$posts_count>0){ ?>
							<li>您还没发布过任何内容。</li>
						<?php }else{ ?>
						<?php
							$args = array('showposts'=>5,'orderby'=>'date','order'=>'DESC','post_type'=>'post','ignore_sticky_posts'=>1, 'author'=>get_the_author_ID());
							$latest = new wp_query($args);
							while ($latest->have_posts()){
								$latest->the_post();
								echo '<li><a href="'.get_permalink($post->ID).'" target="_blank">'.get_the_title($post->ID).'</a>';
								if($post->post_status!='publish')echo '<span>[审核中]</span>';
								echo '</li>';
							}
						?>
						<?php } ?>
						</ul>
					</div>
					<div class="box">
						<div class="title">我的最近评论</div>
						<ul>
						<?php if(!$comments_count>0){ ?>
							<li>暂无未发布任何评论。</li>
						<?php }else{ ?>
						<?php 
							$comments = get_comments(array('status' => 1,'order' => 'DESC','number' => 5,'offset' => 0,'user_id' => $curauth->ID));
							foreach($comments as $comment){
								echo '<li><a href="'.get_comment_link($comment).'" target="_blank">'.$comment->comment_content.'</a></li>';
							}
						?>
						<?php } ?>
	        	        </ul>
					</div>
				</div>
				<div style="clear: both"></div>
				<div class="fast-navigation">
					<div class="nav-title">快捷菜单</div>
					<ul>
						<li><a target="_blank" href="<?php echo get_author_posts_url($curauth->ID); ?>"><i class="fa fa-home"></i>我的主页</a></li>
						
						<li>
						<?php if(is_user_logged_in()){ ?>
						<a href="<?php echo add_query_arg(array('tab'=>'post','action'=>'new'), get_author_posts_url($current_user->ID)); ?>">
						<?php }else{ ?>
						<a href="javascript:" class="user-login">
						<?php } ?>
						<i class="fa fa-pencil-square-o"></i>发布文章</a></li>
						<li><a href="<?php echo um_get_user_url('profile',$curauth->ID); ?>"><i class="fa fa-cog"></i>修改资料</a></li>
						<?php if(is_user_logged_in()) { ?>
						<li><a href="<?php echo wp_logout_url(get_bloginfo('url')); ?>"><i class="fa fa-power-off"></i>注销登录</a></li>
						<?php }else{ ?>
						<li><a href="javascript:" class="user-login"><i class="fa fa-sign-in"></i>登录/注册</a></li>
						<?php } ?>
					</ul>
				</div>
            </div>
        </div>
	</div>
</div>





















