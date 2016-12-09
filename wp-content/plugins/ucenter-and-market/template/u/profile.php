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

//~ 个人资料
if( $oneself ){
	$user_id = $curauth->ID;
	$avatar = $user_info->um_avatar;
	$qq = um_is_open_qq();
	$weibo = um_is_open_weibo();
	if( isset($_POST['update']) && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) {
		$message = __('没有发生变化','um');	
		$update = sanitize_text_field($_POST['update']);
		if($update=='info'){
			$update_user_id = wp_update_user( array(
				'ID' => $user_id, 
				'nickname' => sanitize_text_field($_POST['display_name']),
				'display_name' => sanitize_text_field($_POST['display_name']),
				'user_url' => esc_url($_POST['url']),
				'description' => $_POST['description'],
				'um_gender' => $_POST['um_gender']
			 ) );
			if (($_FILES['file']['error'])==0&&!empty($_FILES['file'])) {
				define( 'AVATARS_PATH', ABSPATH.'/wp-content/uploads/avatars/' );
				$filetype=array("jpg","gif","bmp","jpeg","png");
    			$ext = pathinfo($_FILES['file']['name']);
    			$ext = strtolower($ext['extension']);
    			$tempFile = $_FILES['file']['tmp_name'];
    			$targetPath   = AVATARS_PATH;
    			if( !is_dir($targetPath) ){
        			mkdir($targetPath,0755,true);
    			}
    			$new_file_name = 'avatar-'.$user_id.'.'.$ext;
    			$targetFile = $targetPath . $new_file_name;
    			if(!in_array($ext, $filetype)){
    				$message = __('仅允许上传JPG、GIF、BMP、PNG图片','um');
    			}else{
    				move_uploaded_file($tempFile,$targetFile);
    				if( !file_exists( $targetFile ) ){
	        			$message = __('图片上传失败','um');
    				} elseif( !$imginfo=um_getImageInfo($targetFile) ) {
        				$message = __('图片不存在','um');
    				} else {
        				$img = $new_file_name;
        				um_resize($img);
        				$message = __('头像上传成功','um');
        				$update_user_avatar = update_user_meta( $user_id , 'um_avatar', 'customize');
						$update_user_avatar_img = update_user_meta( $user_id , 'um_customize_avatar', $img);
   	 				}
   	 			}
			} else {
	    		$update_user_avatar = update_user_meta( $user_id , 'um_avatar', sanitize_text_field($_POST['avatar']) );
				if ( ! is_wp_error( $update_user_id ) || $update_user_avatar ) $message = __('基本信息已更新','um');	
			}
		}
		if($update=='info-more'){
			$update_user_id = wp_update_user( array(
				'ID' => $user_id, 
				'um_sina_weibo' => $_POST['um_sina_weibo'],
				'um_qq_weibo' => $_POST['um_qq_weibo'],
				'um_twitter' => $_POST['um_twitter'],
				'um_googleplus' => $_POST['um_googleplus'],
				'um_weixin' => $_POST['um_weixin'],
				'um_donate' => $_POST['um_donate'],
				'um_qq' => $_POST['um_qq'],
				'um_alipay_email' => $_POST['um_alipay_email']
			 ) );
			if ( ! is_wp_error( $update_user_id ) ) $message = __('扩展资料已更新','um');
		}	
		if($update=='pass'){
			$data = array();
			$data['ID'] = $user_id;
			$data['user_email'] = sanitize_text_field($_POST['email']);
			if( !empty($_POST['pass1']) && !empty($_POST['pass2']) && $_POST['pass1']===$_POST['pass2'] ) $data['user_pass'] = sanitize_text_field($_POST['pass1']);
			$user_id = wp_update_user( $data );
			if ( ! is_wp_error( $user_id ) ) $message = __('安全信息已更新','um');
		}
		
		$message .= ' <a href="'.um_get_current_page_url().'">'.__('点击刷新','um').'</a>';
		
		$user_info = get_userdata($curauth->ID);
	}
}
//~ 个人资料end

if($get_tab=='profile' && ($current_user->ID!=$curauth->ID && current_user_can('edit_users')) ) $message = sprintf(__('你正在查看的是%s的资料，修改请慎重！', 'um'), $curauth->display_name);

?>
<div class="area">
    <div class="page-wrapper">
        <div class="dashboard-main">
            <div class="dashboard-header">
				<p class="sub-title">个人资料</p>
				<p class="tip">Hi，<a title="我的主页" href="<?php echo get_author_posts_url($curauth->ID); ?>"><?php echo $curauth->display_name; ?></a>，请如实填写以下内容，让大家更好的交流互动。</p>
				<!-- Page global message -->
				<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
			</div>
            <div class="dashboard-wrapper select-profile">
                <div id="profile">
<?php				
		$avatar_type = array(
			'default' => __('默认头像', 'um'),
			'qq' => __('腾讯QQ头像', 'um'),
			'weibo' => __('新浪微博头像', 'um'),
			'customize' => __('自定义头像', 'um'),
		);
		
		$author_profile = array(
			__('头像来源:','um') => $avatar_type[um_get_avatar_type($user_info->ID)],
			__('昵称:','um') => $user_info->display_name,
			__('站点:','um') => $user_info->user_url,
			__('个人说明:','um') => $user_info->description
		);
		
		$profile_output = '';
		foreach( $author_profile as $pro_name=>$pro_content ){
			$profile_output .= '<tr><td class="title">'.$pro_name.'</td><td>'.$pro_content.'</td></tr>';
		}
		
		$days_num = round(( strtotime(date('Y-m-d')) - strtotime( $user_info->user_registered ) ) /3600/24);
		
		echo '<ul class="user-msg"><li class="tip">'.sprintf(__('%s来%s已经%s天了', 'um') , $user_info->display_name, get_bloginfo('name'), ( $days_num>1 ? $days_num : 1 ) ).'</li></ul>'.'<table id="author-profile"><tbody>'.$profile_output.'</tbody></table>';
		
	if( $oneself ){	?>

					<form id="info-form" class="form-horizontal" role="form" method="POST" action="">
						<input type="hidden" name="update" value="info">
						<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
								<div class="page-header">
									<h3 id="info"><?php _e('基本信息','um');?> <small><?php _e('公开资料','um');?></small></h>
								</div>

						<div class="form-group">
							<label class="col-sm-3 control-label"><?php _e('头像','um');?></label>
							<div class="col-sm-9">

					<div class="radio">
					<?php echo um_get_avatar( $user_info->ID , '40' , um_get_avatar_type($user_info->ID) ); ?>
					  <label>
						<input type="radio" name="avatar"  value="default" <?php if( ($avatar!='qq' || um_is_open_qq($user_info->ID)===false) && ($avatar!='weibo' || um_is_open_weibo($user_info->ID)===false) ) echo 'checked';?>><?php _e('默认头像','um'); ?>
					  </label>
					  <label id="edit-umavatar"><?php _e('(上传头像)','um'); ?></label>
					</div>

					<div id="upload-input">    
						<input name="file" type="file"  value="<?php _e('浏览','um'); ?>" >              
						<span id="upload-umavatar"><?php _e('上传','um'); ?></span>   
					</div>
					<p id="upload-avatar-msg"></p>

					<?php if(um_is_open_qq($user_info->ID)){ ?>
					<div class="radio">
					<?php echo um_get_avatar( $user_info->ID , '40' , 'qq' ); ?>
					  <label>
						<input type="radio" name="avatar" value="qq" <?php if($avatar=='qq') echo 'checked';?>> <?php _e('QQ头像', 'um');?>
					  </label>
					</div>
					<?php } ?>

					<?php if(um_is_open_weibo($user_info->ID)){ ?>
					<div class="radio">
					<?php echo um_get_avatar( $user_info->ID , '40' , 'weibo' ); ?>
					  <label>
						<input type="radio" name="avatar" value="weibo" <?php if($avatar=='weibo') echo 'checked';?>> <?php _e('微博头像', 'um');?>
					  </label>
					</div>
					<?php } ?>
							</div>
						</div>						
						<div class="form-group">
							<label for="display_name" class="col-sm-3 control-label"><?php _e('性别','um');?></label>
							<div class="col-sm-9">
								<select name="um_gender">
									<option value ="male" <?php if($user_info->um_gender=='male') echo 'selected = "selected"'; ?>><?php _e('男','um');?></option>
									<option value ="female" <?php if($user_info->um_gender=='female') echo 'selected = "selected"'; ?>><?php _e('女','um');?></option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="display_name" class="col-sm-3 control-label"><?php _e('昵称','um');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo $user_info->display_name;?>">
							</div>
						</div>
						<div class="form-group">
							<label for="url" class="col-sm-3 control-label"><?php _e('站点','um');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="url" name="url" value="<?php echo $user_info->user_url;?>">
							</div>
						</div>					
						<div class="form-group">
							<label for="description" class="col-sm-3 control-label"><?php _e('个人说明','um');?></label>
							<div class="col-sm-9">
								<textarea class="form-control" rows="3" name="description" id="description"><?php echo $user_info->description;?></textarea>
							</div>
						</div>						
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-9">
								<button type="submit" class="btn btn-primary"><?php _e('保存更改','um');?></button>
							</div>
						</div>						
					</form>
					<form id="info-more-form" class="form-horizontal" role="form" method="post">
						<input type="hidden" name="update" value="info-more">
						<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
								<div class="page-header">
									<h3 id="info"><?php _e('扩展资料','tin');?> <small><?php _e('社会化信息等','tin');?></small></h>
								</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"><?php _e('新浪微博','tin');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="um_sina_weibo" name="um_sina_weibo" value="<?php echo $user_info->um_sina_weibo;?>">
								<span class="help-block"><?php _e('请填写新浪微博账号','tin');?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"><?php _e('腾讯微博','tin');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="um_qq_weibo" name="um_qq_weibo" value="<?php echo $user_info->um_qq_weibo;?>">
								<span class="help-block"><?php _e('请填写腾讯微博账号','tin');?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"><?php _e('腾讯QQ','tin');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="um_qq" name="um_qq" value="<?php echo $user_info->um_qq;?>">
								<span class="help-block"><?php _e('请填写腾讯QQ账号，方便发起在线会话','tin');?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"><?php _e('Twitter','tin');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="um_twitter" name="um_twitter" value="<?php echo $user_info->um_twitter;?>">
								<span class="help-block"><?php _e('请填写Twitter账号','tin');?></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"><?php _e('Google +','tin');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="um_googleplus" name="um_googleplus" value="<?php echo $user_info->um_googleplus;?>">
								<span class="help-block"><?php _e('请填写Google+主页的完整Url','tin');?></span>
							</div>
						</div>					
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-9">
								<button type="submit" class="btn btn-primary"><?php _e('提交资料','tin');?></button>
							</div>
						</div>					
					</form>
					<?php if( $qq || $weibo ) { ?>
					<form id="open-form" class="form-horizontal" role="form" method="post">
								<div class="page-header">
									<h3 id="open"><?php _e('绑定账号','tin');?> <small><?php _e('可用于直接登录','tin');?></small></h>
								</div>
								
						<?php if($qq){ ?>
							<div class="form-group">
								<label class="col-sm-3 control-label"><?php _e('QQ账号','tin');?></label>
								<div class="col-sm-9">
							<?php  if(um_is_open_qq($user_info->ID)) { ?>
								<span class="help-block"><?php _e('已绑定','tin');?> <a href="<?php echo home_url('/?connect=qq&action=logout'); ?>"><?php _e('点击解绑','tin');?></a></span>
								<?php echo um_get_avatar( $user_info->ID , '100' , 'qq' ); ?>
							<?php }else{ ?>
								<a class="btn btn-primary" href="<?php echo home_url('/?connect=qq&action=login&redirect='.urlencode(get_edit_profile_url())); ?>"><?php _e('绑定QQ账号','tin');?></a>
							<?php } ?>
								</div>
							</div>
						<?php } ?>

						<?php if($weibo){ ?>
							<div class="form-group">
								<label class="col-sm-3 control-label"><?php _e('微博账号','tin');?></label>
								<div class="col-sm-9">
							<?php if(um_is_open_weibo($user_info->ID)) { ?>
								<span class="help-block"><?php _e('已绑定','tin');?> <a href="<?php echo home_url('/?connect=weibo&action=logout'); ?>"><?php _e('点击解绑','tin');?></a></span>
								<?php echo um_get_avatar( $user_info->ID , '100' , 'weibo' ); ?>
							<?php }else{ ?>
								<a class="btn btn-danger" href="<?php echo home_url('/?connect=weibo&action=login&redirect='.urlencode(get_edit_profile_url())); ?>"><?php _e('绑定微博账号','tin');?></a>
							<?php } ?>
								</div>
							</div>
						<?php } ?>
					</form>
					<?php } ?>
					<form id="pass-form" class="form-horizontal" role="form" method="post">
						<input type="hidden" name="update" value="pass">
						<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
								<div class="page-header">
									<h3 id="pass"><?php _e('账号安全','tin');?> <small><?php _e('仅自己可见','tin');?></small></h>
								</div>
						<div class="form-group">
							<label for="email" class="col-sm-3 control-label"><?php _e('电子邮件 (必填)','tin');?></label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="email" name="email" value="<?php echo $user_info->user_email;?>" aria-required='true' required>
							</div>
						</div>
						<div class="form-group">
							<label for="pass1" class="col-sm-3 control-label"><?php _e('新密码','tin');?></label>
							<div class="col-sm-9">
								<input type="password" class="form-control" id="pass1" name="pass1" >
								<span class="help-block"><?php _e('如果您想修改您的密码，请在此输入新密码。不然请留空。','tin');?></span>
							</div>
						</div>
						<div class="form-group">
							<label for="pass2" class="col-sm-3 control-label"><?php _e('重复新密码','tin');?></label>
							<div class="col-sm-9">
								<input type="password" class="form-control" id="pass2" name="pass2" >
								<span class="help-block"><?php _e('再输入一遍新密码。 提示：您的密码最好至少包含7个字符。为了保证密码强度，使用大小写字母、数字和符号（例如! " ? $ % ^ & )）。','tin');?></span>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-9">
								<button type="submit" class="btn btn-primary"><?php _e('保存更改','tin');?></button>
							</div>
						</div>
					</form>
<?php } ?>				
				</div>
            </div>
        </div>
    </div>
</div>