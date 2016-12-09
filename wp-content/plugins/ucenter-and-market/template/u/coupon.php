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

// 优惠码start
	
	if( isset($_POST['couponNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['couponNonce'], 'coupon-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			if( isset($_POST['coupon_type']) && sanitize_text_field($_POST['coupon_type'])=='once' ){
				$p_type = 'once';
				$p_type_title = __('一次性','um');
			}else{
				$p_type = 'multi';
				$p_type_title = __('可重复使用','um');
			}
			$p_discount =  sprintf('%0.2f',intval($_POST['discount_value']*100)/100);
			$p_expire_date =  sanitize_text_field($_POST['expire_date']);
			$p_code = sanitize_text_field($_POST['coupon_code']);

			add_um_couponcode($p_code,$p_type,$p_discount,$p_expire_date);
			
			$message = sprintf(__('操作成功！已成功添加优惠码%1$s，类型：%2$s 折扣：%3$s 有效期至：%4$s。','um'), $p_code, $p_type_title, $p_discount, date('Y年m月d日 H时i分s秒',strtotime($p_expire_date)));
		}
	}
	
	if( isset($_POST['dcouponNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['dcouponNonce'], 'dcoupon-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			$coupon_id = intval($_POST['coupon_id']);
			delete_um_couponcode($coupon_id);
			$message = __('操作成功！已成功删除指定优惠码','um');
		}		
	}
//~ 优惠码end

// pages
$paged = max( 1, get_query_var('page') );
$number = get_option('posts_per_page', 10);
$offset = ($paged-1)*$number;

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">优惠码</p>
				<p class="tip">优惠码管理-添加/删除优惠码，仅管理员可见</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-coupon">
                <div id="coupon">
<?php if ( current_user_can('edit_users') ) { ?>
					<div class="panel panel-danger">
						<div class="panel-heading"><?php echo __('添加优惠码（本选项卡及内容仅管理员可见）','um');?></div>
						<div class="panel-body">
							<form id="couponform" role="form"  method="post">
								<input type="hidden" name="couponNonce" value="<?php echo  wp_create_nonce( 'coupon-nonce' );?>" >
								<p>
									<label class="radio-inline"><input type="radio" name="coupon_type" value="once" aria-required='true' required checked><?php _e('一次性','um');?></label>
									<label class="radio-inline"><input type="radio" name="coupon_type" value="multi" aria-required='true' required><?php _e('重复使用','um');?></label>
								</p>
								<div class="form-inline">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon"><?php _e('优惠码','um');?></div>
											<input class="form-control" type="text" name="coupon_code" aria-required='true' required>
										</div>
									</div>
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon"><?php _e('折扣','um');?></div>
											<input class="form-control" type="text" name="discount_value" aria-required='true' required>
										</div>
									</div>
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon"><?php _e('截止有效期','um');?></div>
											<input class="form-control" type="text" name="expire_date" aria-required='true' required>
										</div>
									</div>
									<button class="btn btn-default" type="submit"><?php _e('添加','um');?></button>
								</div>
								<p class="help-block"><?php _e('请谨慎操作！折扣只能填写0~1之间的数字并精确到2位小数点，有效期格式2015-01-01 10:20:30。','um');?></p>
							</form>
						</div>
					</div>

					<table class="table table-bordered coupon-table">
					  <input type="hidden" name="dcouponNonce" value="<?php echo  wp_create_nonce( 'dcoupon-nonce' );?>" >
					  <thead>
						<tr class="active">
						  <th><?php _e('优惠码','um');?></th>
						  <th><?php _e('类型','um');?></th>
						  <th><?php _e('折扣','um');?></th>
						  <th><?php _e('截止有效期','um');?></th>
						  <th><?php _e('操作','um');?></th>
						</tr>
					  </thead>
					  <tbody>
					  <?php $pcodes=output_um_couponcode(); 
						foreach($pcodes as $pcode){
					  ?>
						<tr>
						  <input type="hidden" name="coupon_id" value="<?php echo $pcode['id']; ?>" >
							<td><?php echo $pcode['coupon_code'];?></td>
							<td><?php if($pcode['coupon_type']=='once')echo '一次性'; else echo '可重复'; ?></td>
							<td><?php echo $pcode['discount_value'];?></td>
							<td><?php echo date('Y年m月d日 H时i分s秒',strtotime($pcode['expire_date'])) ;?></td>
							<td class="delete_couponcode"><a><?php _e('删除','um');?></a></td>
						</tr>
					  <?php	}  ?>
					  </tbody>
					</table>	
<?php } ?>			
				</div>
            </div>
        </div>
    </div>
</div>