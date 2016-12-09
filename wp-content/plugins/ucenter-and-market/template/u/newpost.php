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
if(!um_get_setting('open_uctg')){
	exit;	
}
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

//~ 投稿start

if( isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit')) && $oneself ){
	
	if( isset($_GET['id']) && is_numeric($_GET['id']) && get_post($_GET['id']) && intval(get_post($_GET['id'])->post_author) === get_current_user_id() ){
		$action = 'edit';
		$the_post = get_post($_GET['id']);
		$post_title = $the_post->post_title;
		$post_content = $the_post->post_content;
		foreach((get_the_category($_GET['id'])) as $category) { 
			$post_cat[] = $category->term_id; 
		}
	}else{
		$action = 'new';
		$post_title = !empty($_POST['post_title']) ? $_POST['post_title'] : '';
		$post_content = !empty($_POST['post_content']) ? $_POST['post_content'] : '';
		$post_cat = !empty($_POST['post_cat']) ? $_POST['post_cat'] : array();
	}

	if( isset($_POST['action']) && trim($_POST['action'])=='update' && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) {
		
		$title = sanitize_text_field($_POST['post_title']);
		$content = $_POST['post_content'];
		$cat = (!empty($_POST['post_cat'])) ? $_POST['post_cat'] : '';
		
		if( $title && $content ){
			
			if( mb_strlen($content,'utf8')<140 ){
				
				$message = __('提交失败，文章内容至少140字。','um');
				
			}else{
				
				$status = sanitize_text_field($_POST['post_status']);
				
				if( $action==='edit' ){

					$new_post = wp_update_post( array(
						'ID' => intval($_GET['id']),
						'post_title'    => $title,
						'post_content'  => $content,
						'post_status'   => ( $status==='pending' ? 'pending' : 'draft' ),
						'post_author'   => get_current_user_id(),
						'post_category' => $cat
					) );

				}else{

					$new_post = wp_insert_post( array(
						  'post_title'    => $title,
						  'post_content'  => $content,
						  'post_status'   => ( $status==='pending' ? 'pending' : 'draft' ),
						  'post_author'   => get_current_user_id(),
						  'post_category' => $cat
						) );

				}
				
				if( is_wp_error( $new_post ) ){
					$message = __('操作失败，请重试或联系管理员。','um');
				}else{
					
					//update_post_meta( $new_post, 'um_copyright_content', htmlspecialchars($_POST['post_copyright']) );
					
					wp_redirect(um_get_user_url('post'));
				}

			}
		}else{
			$message = __('投稿失败，标题和内容不能为空！','um');
		}
	}
}
//~ 投稿end

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">发表文章</p>
				<p class="tip">提示：文章作品需提交审核才能正式发布，请耐心等待</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-newpost">
                <div id="newpost">
<?php
	$can_post_cat = get_cat_ids()?get_cat_ids():0;
	$cat_count = $can_post_cat!=0?count($can_post_cat):0;
	$msg = '';
	if(!is_user_logged_in()){$msg='你必须<a href="javascript:" class="user-login">登录</a>才能够投稿';}
	if(!$cat_count){$msg='暂无可投稿分类，请等待管理员开放投稿';}
	if(!current_user_can('edit_posts')){$msg=__('遗憾的是，你现在登录的账号没有投稿权限！', 'um');}
?>
<?php
	if($msg) {
		echo '<p>'.$msg.'</p>';
	}else{
?>
					<article class="panel panel-default" role="main">
						<div class="panel-body" style="padding:0;">
						<h3 class="page-header"><?php _e('投稿','um');?> <small><?php _e('POST NEW','um');?></small></h3>
						<form role="form" method="post">
							<div class="form-group">
								<input type="text" class="form-control" name="post_title" placeholder="<?php _e('在此输入标题','um');?>" value="<?php echo $post_title;?>" aria-required='true' required>
							</div>
							<div class="form-group">
<?php wp_editor(  wpautop($post_content), 'post_content', array('media_buttons'=>true, 'quicktags'=>true, 'editor_class'=>'form-control', 'editor_css'=>'<style>.wp-editor-container{border:1px solid #ddd;}.switch-html, .switch-tmce{height:25px !important}</style>' ) ); ?>
							</div>
							<div class="form-group">
<?php
	if($can_post_cat){
		$post_cat_output = '<p class="help-block">'.__('选择文章分类', 'um').'</p>';
		$post_cat_output .= '<select name="post_cat[]" class="form-control">';
		foreach ( $can_post_cat as $term_id ) {
			$category = get_category( $term_id );
			//~ if( (!empty($post_cat)) && in_array($category->term_id,$post_cat)) 
			$post_cat_output .= '<option value="'.$category->term_id.'">'.$category->name.'</option>';
		}
		$post_cat_output .= '</select>';
		echo $post_cat_output;
	}
?>
							</div>
							<div class="form-group text-right">
								<select name="post_status">
									<option value ="pending"><?php _e('提交审核','um');?></option>
									<option value ="draft"><?php _e('保存草稿','um');?></option>
								</select>
								<input type="hidden" name="action" value="update">
								<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
								<button type="submit" class="btn btn-success" style="margin-top:5px;"><?php _e('确认操作','um');?></button>
							</div>	
						</form>
						</div>
			 		</article>
<?php } ?>				
				</div>
            </div>
        </div>
    </div>
</div>