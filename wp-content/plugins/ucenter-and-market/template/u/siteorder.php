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

// Item html
$item_html = '<li class="tip">'.__('没有找到记录','um').'</li>';

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">站点订单</p>
				<p class="tip">提示：管理员管理站内所有订单，仅管理员可见</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-siteorder">
                <div id="siteorder">
<?php if(current_user_can('edit_users')){ ?>
					<?php
						$oall = get_um_orders(0, 'count');
						$pages = ceil($oall/$number);
						$oLog = get_um_orders(0, '', '', $number,$offset);
						if($oLog){
							$item_html = '<li class="contextual" style="background:#f2dede;color:#a94442;">' . sprintf(__('全站共有 %1$s 条订单记录（该栏目仅管理员可见）。','um'), $oall) . '</li>';
							$item_html .= '<div class="site-orders">
								<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
									<thead>
										<tr>
											<th scope="col" style="width:20%;">'.__('商品名','um').'</th>
											<th scope="col">'.__('订单号','um').'</th>
											<th scope="col">'.__('买家','um').'</th>
											<th scope="col">'.__('购买时间','um').'</th>
											<th scope="col">'.__('总价','um').'</th>
											<th scope="col">'.__('交易状态','um').'</th>
											<th scope="col">'.__('操作','um').'</th>
										</tr>
									</thead>
									<tbody class="the-list">';
									foreach($oLog as $Log){
										$item_html .= '
										<tr>
											<td>'.$Log->product_name.'</td>
											<td>'.$Log->order_id.'</td>
											<td>'.$Log->user_name.'</td>
											<td>'.$Log->order_time.'</td>
											<td>'.$Log->order_total_price.'</td>
											<td>';
										if($Log->order_status){$item_html .= output_order_status($Log->order_status);}
										$item_html .= '</td><td>';
										if($Log->order_status==1)$item_html .= '<a class="close-order" href="javascript:" title="关闭过期交易" data="'.$Log->id.'">关闭</a>';
										$item_html .= '</td></tr>';
									}
									$item_html .= '</tbody>
								</table>
							</div>';
							if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
?>
					<ul class="site-order-list">
					<?php echo $item_html; ?>
					</ul>
<?php	}else{echo '<p>没有发现任何订单记录</p>'; }	?>
<?php echo um_pager($paged, $pages); ?>
<?php }	?>				
				</div>
            </div>
        </div>
    </div>
</div>