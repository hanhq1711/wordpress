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

//~ 私信start
	$get_pm = isset($_POST['pm']) ? trim($_POST['pm']) : '';
	if( isset($_POST['pmNonce']) && $get_pm && is_user_logged_in() ){
		if ( ! wp_verify_nonce( $_POST['pmNonce'], 'pm-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			$pm_title = json_encode(array(
				'pm' => $curauth->ID,
				'from' => $current_user->ID
			));
			if( add_um_message( $curauth->ID, 'unrepm', '', $pm_title, $get_pm ) ) $message = __('发送成功！','um');
		}
	}
	
//~ 私信end


?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">站内消息</p>
				<p class="tip"></p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-message">
                <div id="message">
<?php
	if($current_user->ID==$curauth->ID){
		$all_sql = "( msg_type='read' OR msg_type='unread' OR msg_type='repm' OR msg_type='unrepm' )";
		$all = get_um_message($curauth->ID, 'count', $all_sql);	
		$pages = ceil($all/$number);	
		$mLog = get_um_message($curauth->ID, '', $all_sql, $number,$offset);
		$unread = intval(get_um_message($curauth->ID, 'count', "msg_type='unread' OR msg_type='unrepm'"));	
		if($mLog){
			$item_html = '<li class="tip">' . sprintf(__('共有 %1$s 条消息，其中 %2$s 条是新消息（绿色标注）。','um'), $all, $unread) . '</li>';
			foreach( $mLog as $log ){
				$unread_tip = $unread_class = '';
				if(in_array($log->msg_type, array('unread', 'unrepm'))){
					$unread_tip = '<span class="tag">'.__('新！', 'um').'</span>';
					$unread_class = ' class="unread"';
					update_um_message_type( $log->msg_id, $curauth->ID , ltrim($log->msg_type, 'un') );
				}
				$msg_title =  $log->msg_title;
				if(in_array($log->msg_type, array('repm', 'unrepm'))){
					$msg_title_data = json_decode($log->msg_title);
					$msg_title = get_the_author_meta('display_name', intval($msg_title_data->from));
					$msg_title = sprintf(__('%s发来的私信','um'), $msg_title).' <a href="'.add_query_arg('tab', 'message', get_author_posts_url(intval($msg_title_data->from))).'#'.$log->msg_id.'">'.__('查看对话','um').'</a>';
				}
				$item_html .= '<li'.$unread_class.'><div class="message-content">'.htmlspecialchars_decode($log->msg_content).' </div><p class="info">'.$unread_tip.'  '.$msg_title.'  '.$log->msg_date.'</p></li>';
			}
			if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
		}
		
	}else{
		if( is_user_logged_in() ){
			$item_html = '<li class="tip">'.sprintf(__('与 %s 对话','um'), $user_info->display_name).'</li><li><form id="pmform" role="form" method="post"><input type="hidden" name="pmNonce" value="'.wp_create_nonce( 'pm-nonce' ).'" ><p><textarea class="form-control" rows="3" name="pm" required></textarea></p><p class="clearfix"><a class="btn btn-link pull-left" href="'.add_query_arg('tab', 'message', get_author_posts_url($current_user->ID)).'">'.__('查看我的消息','um').'</a><button type="submit" class="btn btn-primary pull-right">'.__('确定发送','um').'</button></p></form></li>';
			$all = get_um_pm( $curauth->ID, $current_user->ID, true );
			$pages = ceil($all/$number);
			$pmLog = get_um_pm( $curauth->ID, $current_user->ID, false, false, $number, $offset );
			if($pmLog){
				foreach( $pmLog as $log ){
					$pm_data = json_decode($log->msg_title);
					if( $pm_data->from==$curauth->ID ){
						update_um_message_type( $log->msg_id, $curauth->ID , 'repm' );
					}
					$item_html .= '<li id="'.$log->msg_id.'"><div class="message-content clearfix"><a class="'.( $pm_data->from==$current_user->ID ? 'pull-right' : 'pull-left' ).'" href="'.get_author_posts_url($pm_data->from).'">'.um_get_avatar( $pm_data->from , '34' , um_get_avatar_type($pm_data->from), false ).'</a><div class="pm-box"><div class="pm-content'.( $pm_data->from==$current_user->ID ? '' : ' highlight' ).'">'.htmlspecialchars_decode($log->msg_content).'</div><p class="pm-date">'.date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), strtotime($log->msg_date)).'</p></div></div></li>';
				}
			}
			if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
		}else{
			$item_html = '<li class="tip">'.sprintf(__('私信功能需要<a href="%s">登录</a>才可使用！','um'), wp_login_url() ).'</li>';
		}
	}
	echo '<ul class="user-msg">'.$item_html.'</ul>'.um_pager($paged, $pages);
?>				
				</div>
            </div>
        </div>
    </div>
</div>