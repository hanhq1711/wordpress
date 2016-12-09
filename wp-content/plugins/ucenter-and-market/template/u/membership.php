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

// pages
$paged = max( 1, get_query_var('page') );
$number = get_option('posts_per_page', 10);
$offset = ($paged-1)*$number;

// 会员start
	if( isset($_POST['promoteVipNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['promoteVipNonce'], 'promotevip-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			if( isset($_POST['promotevip_type']) && sanitize_text_field($_POST['promotevip_type'])=='4' ){
				$pv_type = 4;
				$pv_type_title = __('终身会员','um');
			}elseif( isset($_POST['promotevip_type']) && sanitize_text_field($_POST['promotevip_type'])=='3' ){
				$pv_type = 3;
				$pv_type_title = __('年费会员','um');
			}elseif(isset($_POST['promotevip_type']) && sanitize_text_field($_POST['promotevip_type'])=='2'){
				$pv_type = 2;
				$pv_type_title = __('季费会员','um');
			}else{
				$pv_type = 1;
				$pv_type_title = __('月费会员','um');
			}
			$pv_expire_date =  sanitize_text_field($_POST['vip_expire_date']);

			um_manual_promotevip($curauth->ID,$curauth->display_name,$curauth->user_email,$pv_type,$pv_expire_date);
			
			$message = sprintf(__('操作成功！已成功将%1$s提升至%2$s，有效期至 %3$s。','um'), $curauth->display_name, $pv_type_title, date('Y年m月d日 H时i分s秒',strtotime($pv_expire_date)));
			$message .= ' <a href="'.um_get_current_page_url().'">'.__('点击刷新','um').'</a>';
		}
	}
	
//~ 会员end

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">会员中心</p>
				<p class="tip">提示：加入网站会员，获取资源优惠</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-membership">
                <div id="membership">
<?php if($oneself){ ?>
<?php $member = getUserMemberInfo($curauth->ID); $member_info = array('会员类型'=>$member['user_type'],'会员状态'=>$member['user_status'],'开通时间'=>$member['startTime'],'到期时间'=>$member['endTime']);$member_info_output='';foreach($member_info as $member_info_name=>$member_info_content) {$member_info_output .='<label class="col-sm-3 control-label">'.$member_info_name.'</label><div class="col-sm-9"><p class="form-control-static">'.$member_info_content.'</p></div>'; }?>
					<div class="page-header">
						<h3 id="membership-info"><?php _e('会员信息','um'); ?></h3>
					</div>
					<div class="form-horizontal">
						<div class="form-group">
						<?php echo $member_info_output; ?>
						</div>
					</div>

<?php } ?>

<?php if(current_user_can('edit_users')){ ?>
					<div class="panel panel-danger">
						<div class="panel-heading"><?php echo __('会员操作（本选项卡及内容仅管理员可见）','um');?></div>
						<div class="panel-body">
							<form id="promotevipform" role="form"  method="post">
								<input type="hidden" name="promoteVipNonce" value="<?php echo  wp_create_nonce( 'promotevip-nonce' );?>" >
								<p>
									<label class="radio-inline"><input type="radio" name="promotevip_type" value="1" aria-required='true' required checked><?php _e('月费会员','um');?></label>
									<label class="radio-inline"><input type="radio" name="promotevip_type" value="2" aria-required='true' required><?php _e('季费会员','um');?></label>
									<label class="radio-inline"><input type="radio" name="promotevip_type" value="3" aria-required='true' required><?php _e('年费会员','um');?></label>
									<label class="radio-inline"><input type="radio" name="promotevip_type" value="4" aria-required='true' required><?php _e('终身会员','um');?></label>
								</p>
								<div class="form-inline">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon"><?php _e('会员截止有效期','um');?></div>
											<input class="form-control" type="date" name="vip_expire_date" aria-required='true' required>
										</div>
									</div>
									<button class="btn btn-default" id="promotevipform-submit" type="submit"><?php _e('确认操作','um');?></button>
								</div>
								<p class="help-block"><?php _e('请谨慎操作！会员截止有效期格式2015-01-01','um');?></p>
							</form>
						</div>
					</div>
<?php } ?>

					<div class="page-header">
						<h3 id="membership-join"><?php _e('加入会员','um'); ?> <small><?php _e('加入、续费','um'); ?></small></h3>
					</div>
					<div class="panel">
						<div class="panel-body">
							<form id="joinvip" role="form" method="post" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" onsubmit="return false;">
								<p>
									<input type="hidden" name="vipNonce" value="<?php echo wp_create_nonce( 'vip-nonce' );?>" >
									<input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
									<label class="radio-inline"><input type="radio" name="product_id" value="-1" aria-required="true" required="" checked=""><?php _e('月费会员','um'); ?>(<?php echo um_get_setting('monthly_mb_price',5).'元/月'; ?>)</label>
									<label class="radio-inline"><input type="radio" name="product_id" value="-2" aria-required="true" required=""><?php _e('季费会员','um'); ?>(<?php echo um_get_setting('quarterly_mb_price',12).'元/季'; ?>)</label>
									<label class="radio-inline"><input type="radio" name="product_id" value="-3" aria-required="true" required=""><?php _e('年费会员','um'); ?>(<?php echo um_get_setting('annual_mb_price',45).'元/年'; ?>)</label>
									<label class="radio-inline"><input type="radio" name="product_id" value="-4" aria-required="true" required=""><?php _e('终身会员','um'); ?>(<?php echo um_get_setting('life_mb_price',120).'元/年'; ?>)</label>
									<button class="btn btn-primary" id="joinvip-submit" type=""><?php _e('确认开通','um'); ?></button>
								</p>
								<p class="help-block" style="font-size:12px;"><?php _e('提示:若已开通会员则按照选择开通的类型自动续费,若会员已到期,则按重新开通计算有效期','um'); ?></p>
							</form>
						</div>

					</div>

<?php if($oneself){ $vip_orders = getUserMemberOrders($curauth->ID); ?>
					<div class="page-header">
						<h3 id="membership-records"><?php _e('会员记录','um'); ?> <small><?php _e('会员订单','um'); ?></small></h3>
					</div>
					<div class="wrapbox">
						<div class="membership-history order-history">
							<table width="100%" border="0" cellspacing="0">
								<thead>
									<tr>
										<th scope="col"><?php _e('订单号','um'); ?></th>
										<th scope="col"><?php _e('支付时间','um'); ?></th>
										<th scope="col"><?php _e('支付金额','um'); ?></th>
										<th scope="col"><?php _e('开通类型','um'); ?></th>	
										<th scope="col"><?php _e('交易状态','um'); ?></th>
									</tr>
								</thead>
								<tbody class="the-list">
								<?php foreach($vip_orders as $vip_order){ ?>
									<tr>
										<td><?php echo $vip_order['order_id']; ?></td>
										<td><?php echo $vip_order['order_success_time']; ?></td>
										<td><?php echo $vip_order['order_total_price']; ?></td>
										<td><?php echo output_order_vipType($vip_order['product_id']*(-1)); ?></td>
										<td><?php echo output_order_status($vip_order['order_status']); ?></td>
										</tr>
								<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
<?php } ?>
				</div>
            </div>
        </div>
    </div>
</div>