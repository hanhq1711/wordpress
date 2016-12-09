<?php
/**
 * Main Template of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.4.1
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

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">站内订单</p>
				<p class="tip">提示：如对订单有任何的疑问，请立即联系我们。</p>
			</div>
            <div class="dashboard-wrapper select-orders">
                <div id="orders">
<?php if($oneself){
		$oall = get_um_orders($curauth->ID, 'count');
		$pages = ceil($oall/$number);
		$oLog = get_um_orders($curauth->ID, '', '', $number,$offset);
		//$order_records = get_user_order_records(0,$curauth->ID);
?>
					<ul class="site-order-list">
					<div class="shop" style="margin-top: 50px;">
						<div id="history" class="wrapbox">
							<form id="continue-pay" name="continue-pay" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" method="post" style="height:0;">
								<input type = "hidden" id="product_id" name="product_id" readonly="" value="">
								<input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
								<input type = "hidden" id="order_name" name="order_name" readonly="" value="0">
							</form>
							<li class="contextual" style="background:#ceface;color:#44a042;"><?php echo sprintf(__('与 %1$s 相关订单记录（该栏目仅自己和管理员可见）。','um'), $curauth->display_name); ?></li>
							<div class="pay-history">
								<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
									<thead>
										<tr>
											<th scope="col" style="width:20%;"><?php _e('商品名','um'); ?></th>
											<th scope="col"><?php _e('订单号','um'); ?></th>
											<th scope="col"><?php _e('购买时间','um'); ?></th>
											<th scope="col"><?php _e('数量','um'); ?></th>
											<th scope="col"><?php _e('价格','um'); ?></th>
											<th scope="col"><?php _e('总价','um'); ?></th>
											<th scope="col"><?php _e('交易状态','um'); ?></th>
										</tr>
									</thead>
									<tbody class="the-list">
									<?php if($oLog)foreach($oLog as $order_record){ ?>
										<tr>
											<td><?php if($order_record->product_id>0){echo '<a href="'.get_permalink($order_record->product_id).'" target="_blank" title="'.$order_record->product_name.'">'.$order_record->product_name.'</a>';}else{echo $order_record->product_name;} ?></td>
											<td><?php echo $order_record->order_id; ?></td>
											<td><?php echo $order_record->order_time; ?></td>
											<td><?php echo $order_record->order_quantity; ?></td>
											<td><?php echo $order_record->order_price; ?></td>
											<td><?php echo $order_record->order_total_price; ?></td>
											<td><?php if($order_record->order_status==1){echo '<a href="javascript:" data-id="'.$order_record->id.'" class="continue-pay">继续付款</a>';}else{echo output_order_status($order_record->order_status);}; ?></td>
											</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
						</div>	
					</div>
					</ul>
<?php echo um_pager($paged, $pages); ?>
<?php	}	?>				
				</div>
            </div>
        </div>
    </div>
</div>