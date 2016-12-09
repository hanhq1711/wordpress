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
	$curauth = $wp_query->get_queried_object();
	$current_user = wp_get_current_user();
?>
<div class="aside">
    <div class="user-avatar">
        <a href="<?php echo um_get_user_url('index',$curauth->ID); ?>"><?php echo um_get_avatar( $curauth->ID , '100' , um_get_avatar_type($curauth->ID) ); ?></a>
        <h2><?php echo $curauth->display_name; ?></h2>
		<div id="num-info">
			<div><span class="num"><?php echo um_following_count($curauth->ID); ?></span><span class="text">关注</span></div>
			<div><span class="num"><?php echo um_follower_count($curauth->ID); ?></span><span class="text">粉丝</span></div>
			<div><span class="num"><?php echo $posts_count; ?></span><span class="text">文章</span></div>
		</div>
		<?php if($curauth->ID!=$current_user->ID){ ?>
		<div class="fp-btns">
		<?php echo um_follow_button($curauth->ID); ?>
		<span class="pm-btn"><a href="<?php echo add_query_arg('tab', 'message', get_author_posts_url( $curauth->ID )); ?>" title="发送私信">私信</a></span>
		</div>
		<?php } ?>
		<div class="clear"></div>
    </div>
    <div class="menus">
        <ul>
			<li class="tab-index <?php if((isset($_GET['tab'])&&$_GET['tab']=='index')||!isset($_GET['tab'])) echo 'active'; ?>">
				<a href="<?php echo um_get_user_url('index',$curauth->ID); ?>"><i class="fa fa-tachometer"></i>首页中心</a>
			</li>
			<li class="tab-post <?php if(isset($_GET['tab'])&&$_GET['tab']=='post'&&(isset($_GET['action'])&&!in_array($_GET['action'],array('new','edit'))||!isset($_GET['action']))) echo 'active'; ?>">
				<a href="<?php echo um_get_user_url('post',$curauth->ID); ?>"><i class="fa fa-cube"></i>我的文章</a>
			</li>
            <?php if(um_get_setting('open_uctg')){?>
			<li class="tab-newpost <?php if(isset($_GET['tab'])&&$_GET['tab']=='post'&&isset($_GET['action'])&&in_array($_GET['action'],array('new','edit'))) echo 'active'; ?>">
			<?php if(is_user_logged_in()){ ?>
				<a href="<?php echo add_query_arg(array('tab'=>'post','action'=>'new'), get_author_posts_url($current_user->ID)); ?>">
			<?php }else{ ?>
				<a href="javascript:" class="user-login">
			<?php } ?>
				<i class="fa fa-pencil-square-o"></i>文章投稿</a>
			</li>
            <?php }?>
			<li class="tab-collect <?php if(isset($_GET['tab'])&&$_GET['tab']=='collect') echo 'active'; ?>">
				<a href="<?php echo um_get_user_url('collect',$curauth->ID); ?>"><i class="fa fa-star"></i>文章收藏</a>
			</li>
			<li class="tab-comment <?php if(isset($_GET['tab'])&&$_GET['tab']=='comment') echo 'active'; ?>">
				<a href="<?php echo um_get_user_url('comment',$curauth->ID); ?>"><i class="fa fa-comments"></i>评论留言</a>
			</li>
			<li class="tab-message <?php if(isset($_GET['tab'])&&$_GET['tab']=='message') echo 'active'; ?>">
				<a href="<?php echo um_get_user_url('message',$curauth->ID); ?>"><i class="fa fa-envelope"></i>站内消息</a>
			</li>
			<li class="tab-profile <?php if(isset($_GET['tab'])&&$_GET['tab']=='profile') echo 'active'; ?>">
				<a href="<?php echo um_get_user_url('profile',$curauth->ID); ?>"><i class="fa fa-cog"></i>编辑资料</a>
			</li>
		</ul>
    </div>
</div>