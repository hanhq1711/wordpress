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

// 牛币start
	
	if( isset($_POST['creditNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['creditNonce'], 'credit-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			$c_user_id =  $curauth->ID;
			if( isset($_POST['creditChange']) && sanitize_text_field($_POST['creditChange'])=='add' ){
				$c_do = 'add';
				$c_do_title = __('增加','um');
			}else{
				$c_do = 'cut';
				$c_do_title = __('减少','um');
			}

			$c_num =  intval($_POST['creditNum']);
			$c_desc =  sanitize_text_field($_POST['creditDesc']);
			
			$c_desc = empty($c_desc) ? '' : __('备注','um') . ' : '. $c_desc;

			update_um_credit( $c_user_id , $c_num , $c_do , 'um_credit' , sprintf(__('%1$s将你的牛币%2$s %3$s 分。%4$s','um') , $current_user->display_name, $c_do_title, $c_num, $c_desc) );
			
			$message = sprintf(__('操作成功！已将%1$s的牛币%2$s %3$s 分。','um'), $user_name, $c_do_title, $c_num);
		}
	}	
	
//~ 牛币end

// pages
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
				<p class="sub-title">牛币管理</p>
				<p class="tip">提示：投稿、评论、参与互动获取牛币</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-credit">
<?php if ( current_user_can('edit_users') ) { ?>
				<div class="panel panel-danger">
					<div class="panel-heading"><?php echo $curauth->display_name.__('牛币变更（仅管理员可见）','um');?></div>
					<div class="panel-body">
						<form id="creditform" role="form"  method="post">
							<input type="hidden" name="creditNonce" value="<?php echo  wp_create_nonce( 'credit-nonce' );?>" >
							<p>
								<label class="radio-inline"><input type="radio" name="creditChange" value="add" aria-required='true' required checked=""><?php _e('增加牛币','um');?></label>
								<label class="radio-inline"><input type="radio" name="creditChange" value="cut" aria-required='true' required><?php _e('减少牛币','um');?></label>
							</p>
							<div class="form-inline">
								<div class="form-group">
									<div class="input-group" style="width:220px;">
										<div class="input-group-addon"><?php _e('牛币','um');?></div>
										<input class="form-control" type="text" name="creditNum" aria-required='true' required>
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><?php _e('备注','um');?></div>
										<input class="form-control" type="text" name="creditDesc" aria-required='true' required>
									</div>
								</div>
								<button class="btn btn-default" type="submit"><?php _e('提交','um');?></button>
							</div>
							<p class="help-block"><?php _e('请谨慎操作！牛币数只能填写数字，备注将显示在用户的牛币记录中。','um');?></p>
						</form>
					</div>
				</div>
<?php } 				
	//~ 牛币充值
if ( $current_user->ID==$curauth->ID ) { ?>
				<div class="panel panel-success">
					<div class="panel-heading"><?php echo __('牛币充值（仅自己可见）','um');?></div>
					<div class="panel-body">
						<form id="creditrechargeform" role="form"  method="post" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" onsubmit="return false;">
							<input type="hidden" name="creditrechargeNonce" value="<?php echo  wp_create_nonce( 'creditrecharge-nonce' );?>" >
							<input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
							<input type = "hidden" id="product_id" name="product_id" readonly="" value="-5">
							<p>
								<label><?php echo sprintf(__('当前牛币兑换比率为：1元 = %1$s 牛币','um'),um_get_setting('um_cash_credit_ratio',50));?></label>
							</p>
							<div class="form-inline">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><?php _e('牛币*100','um');?></div>
										<input class="form-control" type="text" name="creditrechargeNum" value="10" aria-required='true' required>
									</div>
								</div>
								<button class="btn btn-default" type="submit" id="creditrechargesubmit"><?php _e('充值','um');?></button>
							</div>
							<p class="help-block"><?php _e('牛币数以100为单位起计算,请填写整数数值，如填1即表明充值100牛币，所需现金根据具体兑换比率计算。','um');?></p>
						</form>
					</div>
				</div>
<?php } 
				$item_html = '<li class="tip">' . sprintf(__('共有 %1$s 个牛币，其中 %2$s 个已消费， %3$s 个可用。','um'), ($credit+$credit_void), $credit_void, $credit) ;
				if($current_user->ID==$curauth->ID){$item_html .= '&nbsp;(&nbsp;每日签到：'.um_whether_signed($current_user->ID).'&nbsp;)';}
				$item_html .= '</li>';

				if($oneself){
					$all = get_um_message($curauth->ID, 'count', "msg_type='credit'");
					$pages = ceil($all/$number);
					
					$creditLog = get_um_credit_message($curauth->ID, $number,$offset);

					if($creditLog){
						foreach( $creditLog as $log ){
							$item_html .= '<li>'.$log->msg_date.' <span class="message-content" style="background:transparent;">'.$log->msg_title.'</span></li>';
						}
						if($pages>1) $item_html .= '<li class="tip">' . sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number). '</li>';
					}
				}
				echo '<ul class="user-msg">'.$item_html.'</ul>';
				if($oneself) echo um_pager($paged, $pages);
?>
				<table class="table table-bordered credit-table">
				  <thead>
					<tr class="active">
					  <th><?php _e('牛币方法','um');?></th>
					  <th><?php _e('一次得分','um');?></th>
					  <th><?php _e('可用次数','um');?></th>
					</tr>
				  </thead>
				  <tbody>
					<tr>
					  <td><?php _e('注册奖励','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('new_reg_credit','50'));?></td>
					  <td><?php _e('只有 1 次','um');?></td>
					</tr>
					<tr>
					  <td><?php _e('文章投稿','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('contribute_credit','50'));?></td>
					  <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('contribute_credit_times','5'));?></td>
					</tr>
					<tr>
					  <td><?php _e('评论回复','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('comment_credit','5'));?></td>
					  <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('comment_credit_times','50'));?></td>
					</tr>
					<tr>
					  <td><?php _e('访问推广','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('aff_visit_credit','5'));?></td>
					  <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('aff_visit_credit_times','50'));?></td>
					</tr>
					<tr>
					  <td><?php _e('注册推广','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('aff_reg_credit','50'));?></td>
					  <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('aff_reg_credit_times','5'));?></td>
					</tr>
					<tr>
					  <td><?php _e('每日签到','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('daily_sign_credit','10'));?></td>
					  <td><?php _e('每天 1 次','um');?></td>
					</tr>
					<tr>
					  <td><?php _e('文章互动','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('like_article_credit','10'));?></td>
					  <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('like_article_credit_times','5'));?></td>
					</tr>
					<tr>
					  <td><?php _e('发布资源','um');?></td>
					  <td><?php printf( __('%1$s 分','um'), um_get_setting('source_download_credit','5'));?></td>
					  <td><?php _e('不限次数,收费资源额外返还价格100%牛币','um');?></td>
					</tr>
					<tr>
					  <td><?php _e('牛币兑换','um');?></td>
					  <td colspan="2"><?php printf( __('兑换比率：1 元 = %1$s 牛币','um'), um_get_setting('exchange_ratio','100'));?></td>
					</tr>
				  </tbody>
				</table>			
            </div>
        </div>
    </div>
</div>