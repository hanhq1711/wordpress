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
				<p class="sub-title">我的推广</p>
				<p class="tip">提示：加入网站推广，赢取丰厚回报。</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-affiliate">
                <div id="affiliate">
<?php if($oneself){ ?>
					<div class="affiliate-tab">
					<?php
						if(isset($_GET['currency'])&&in_array($_GET['currency'], array('cash','credit')))$currency=$_GET['currency']; else $currency =  'cash';
						$aLog = get_um_aff_sum_orders( $curauth->ID , $currency, $number, $offset );
						$aall = $aLog?count($aLog):0;
						$pages = ceil($aall/$number);
					?>
						<p id="aff">你的永久推广链接为 <input class="um_aff_url" type="text" name="spreadurl" style="min-width:240px;width:60%;" value="<?php echo get_bloginfo('url').'/?aff='.$current_user->ID; ?>" disabled="disabled"></p>
					<?php
						if($currency=='cash'){$type='现金';$otype='牛币';$sum_rewards=get_um_aff_sum_money($curauth->ID);$withdrawed = um_get_withdraw_sum($curauth->ID,'withdrawed');$withdrawing = um_get_withdraw_sum($curauth->ID,'withdrawing');$left = $sum_rewards-$withdrawed-$withdrawing;$link = um_get_user_url('affiliate',$curauth->ID).'&currency=credit';}else{$type='牛币';$otype='现金';$sum_rewards=get_um_aff_sum_money($curauth->ID,'credit');$link = um_get_user_url('affiliate',$curauth->ID).'&currency=cash';}
		$item_html = '<li class="contextual" style="background:#ceface;color:#44a042;">' . sprintf(__('全站共有 %1$s 条%2$s推广用户记录，点击查看<a href="%3$s">%4$s</a>推广用户记录。','um'), $aall,$type,$link,$otype) . '</li>';
							$item_html .= '<div class="site-orders">
								<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
									<thead>
										<tr>
											<th scope="col" style="width:20%;">'.__('用户ID','um').'</th>
											<th scope="col">'.__('注册时间','um').'</th>
											<th scope="col">'.__('消费金额','um').'</th>
											<th scope="col">'.__('推广提成','um').'</th>
										</tr>
									</thead>
									<tbody class="the-list">';
									if($aLog){$total_value=0;foreach($aLog as $Log){
										$total_value += $Log->total_cost;
										$item_html .= '
										<tr>
											<td>'.$Log->user_id.'</td>
											<td>'.date( 'Y年m月d日', strtotime( get_userdata($Log->user_id)->user_registered ) ).'</td>
											<td>'.$Log->total_cost.'</td>
											<td>'.$Log->total_rewards.'</td>
										</tr>';
									}
										$item_html .= '
										<tr>
											<td colspan=2 style="text-align:right;font-weight:bold;color:#000;">合计：</td>
											<td>'.sprintf('%0.2f',$total_value).'</td>
											<td>'.sprintf('%0.2f',$sum_rewards).'</td>
										</tr>';
										if($currency=='cash'):
										$item_html .= '
										<tr>
											<td colspan=3 style="text-align:right;font-weight:bold;color:#000;">提现中：</td>
											<td>-'.sprintf('%0.2f',$withdrawing).'</td>
										</tr>';
										$item_html .= '
										<tr>
											<td colspan=3 style="text-align:right;font-weight:bold;color:#000;">已提现：</td>
											<td>-'.sprintf('%0.2f',$withdrawed).'</td>
										</tr>';
										$item_html .= '
										<tr>
											<td colspan=3 style="text-align:right;font-weight:bold;color:#000;">推广余额：</td>
											<td>'.sprintf('%0.2f',$left).'</td>
											<input type="hidden" value="'.sprintf('%0.2f',$left).'" name="balance" id="balance">
										</tr>';
										endif;
									}else{$item_html .= '<tr><td colspan=4 style="text-align:left;">没有推广记录</td></tr>';}
									$item_html .= '</tbody>
								</table>';
								if($currency=='cash'){
									$item_html .= '<div id="withdraw-records"><p style="color:#000;">提现记录</p>';
									if(current_user_can('edit_users'))$th='<th scope="col">'.__("操作","um").'</th>';else $th='';
									$item_html .= '<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
									<thead>
										<tr>
											<th scope="col">'.__('申请时间','um').'</th>
											<th scope="col">'.__('金额','um').'</th>
											<th scope="col">'.__('余额','um').'</th>
											<th scope="col">'.__('状态','um').'</th>'.$th.'
										</tr>
									</thead>
									<tbody class="the-list">';
									$records = um_withdraw_records($curauth->ID);
									if($records){
										foreach($records as $record){
										$item_html .= '
										<tr>
											<td>'.$record->time.'</td>
											<td>'.$record->money.'</td>
											<td>'.$record->balance.'</td>
											<td>'.um_withdraw_status_output($record->status,$record->id).'</td>
										</tr>';
										}

									}else{
										$item_html .= '<tr><td colspan=4 style="text-align:left;">没有提现记录</td></tr>';
									}

									$item_html .= '</tbody></table></div>';
								}
								if($currency=='cash'&&$curauth->ID==$current_user->ID){
									$item_html .= '<div id="withdraw">';
									if($left<um_get_setting('aff_discharge_lowest',100)){
										$item_html .= '<p>'.$curauth->display_name.'，你当前账户推广余额低于'.um_get_setting('aff_discharge_lowest',100).'元最低提现值，暂不能申请提现</p>';
									}else{
										$item_html .= '<div class="form-inline"><div class="form-group"><div class="input-group"><div class="input-group-addon">提现数额</div><input class="form-control" type="text" name="withdrawNum" id="withdrawNum" value="'.sprintf('%0.2f',$left).'" aria-required="true" required=""></div></div><button class="btn btn-default" type="submit" id="withdrawSubmit" style="margin-left:10px;">申请提现</button></div>';
									}
									$item_html .= '</div>';
								}
							$item_html .= '</div>';
							if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
						echo $item_html;
?>
					</div>
<?php echo um_pager($paged, $pages); ?>
<?php }	?>				
				</div>
            </div>
        </div>
    </div>
</div>