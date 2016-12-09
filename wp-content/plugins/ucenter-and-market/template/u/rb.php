					<div id="rb">
						<div id="rb-inner">
						<!-- Follow widget -->
							<div class="um-widget follow-widget">
								<div class="widget-header clx">
									<?php echo um_get_avatar( $curauth->ID , '40' , um_get_avatar_type($curauth->ID) ); ?>
									<h4>关注/粉丝</h4>
									<p class="widget-p">TA的关注和粉丝</p>
								</div>
								<div class="widget-body">
									<div class="item">
										<fieldset class="fieldset clx">
											<legend class="legend">关注<span>(<?php echo um_following_count($curauth->ID); ?>)</span></legend>
										</fieldset>
										<ul class="flowlist following-list clx">
											<?php echo um_follow_list($curauth->ID,20,'following'); ?>
										</ul>
									</div>
									<div class="item">
										<fieldset class="fieldset">
											<legend class="legend">粉丝<span>(<?php echo um_follower_count($curauth->ID); ?>)</span></legend>
										</fieldset>
										<ul class="flowlist followers-list clx">
											<?php echo um_follow_list($curauth->ID,20); ?>
										</ul>
									</div>
								</div>
							</div>
						<!-- Manage menu widget -->
							<div class="um-widget follow-widget">
								<div class="widget-header clx">
									<div class="icon"><i class="fa fa-globe"></i></div>
									<h4>名片</h4>
									<p class="widget-p">TA的个人信息</p>
								</div>
								<div class="widget-body">
									<div class="user-time">
										<?php $days_num = round(( strtotime(date('Y-m-d')) - strtotime( $user_info->user_registered ) ) /3600/24); $days_num = $days_num>1?$days_num:1;echo '<p><span>'.__('注册 :','um').'</span>'.date( 'Y年m月d日', strtotime( $user_info->user_registered ) ).' ( '.$days_num.'天 )</p>';
				 						if($current_user&&$current_user->ID==$curauth->ID&&!empty($user_info->um_latest_ip_before)) {echo '<p><span>'.__('上次登录 :','um').'</span>'.date( 'Y年m月d日 H时i分s秒', strtotime( $user_info->um_latest_login_before ) ).'</p>';/*.$user_info->um_latest_ip_before.' '.convertip($user_info->um_latest_ip_before).'<span>'.'&nbsp;IP&nbsp;'.'</span>';*/}else{
				 						if($user_info->um_latest_login) echo '<p><span>'.__('最后登录 :','um').'</span>'.date( 'Y年m月d日 H时i分s秒', strtotime( $user_info->um_latest_login ) ).'</p>';}
				 						?>
									</div>
									<div class="item">
										<fieldset class="fieldset">
											<legend class="legend">网络<span></span></legend>
										</fieldset>
										<ul class="sociallist clx">
											<?php if(!empty($user_info->user_url)){ ?>
											<span><a class="as-img as-home" href="<?php echo $user_info->user_url; ?>" title="<?php _e('用户主页','um'); ?>"><i class="fa fa-home"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_donate)){ ?>
											<span><a class="as-img as-donate" href="#" title="<?php _e('打赏TA','tinection'); ?>"><i class="fa fa-coffee"></i>
												<div id="as-donate-qr" class="as-qr"><img src="<?php echo $user_info->um_donate; ?>" title="<?php _e('手机支付宝扫一扫打赏TA','um'); ?>" /><div>手机支付宝扫一扫打赏TA</div></div></a><?php echo um_alipay_post_gather($user_info->um_alipay_email,10,1); ?></span>
											<?php } ?>
											<?php if(!empty($user_info->um_sina_weibo)){ ?>
											<span><a class="as-img as-sinawb" href="http://weibo.com/<?php echo $user_info->um_sina_weibo; ?>" title="<?php _e('微博','um'); ?>"><i class="fa fa-weibo"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_qq_weibo)){ ?>
											<span><a class="as-img as-qqwb" href="http://t.qq.com/<?php echo $user_info->um_qq_weibo; ?>" title="<?php _e('腾讯微博','um'); ?>"><i class="fa fa-tencent-weibo"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_twitter)){ ?>
											<span><a class="as-img as-twitter" href="https://twitter.com/<?php echo $user_info->um_twitter; ?>" title="Twitter"><i class="fa fa-twitter"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_googleplus)){ ?>
											<span><a class="as-img as-googleplus" href="<?php echo $user_info->um_googleplus; ?>" title="Google+"><i class="fa fa-google-plus"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_weixin)){ ?>
											<span><a class="as-img as-weixin" href="#" id="as-weixin-a" title="<?php _e('微信','tinection'); ?>"><i class="fa fa-weixin"></i>
												<div id="as-weixin-qr" class="as-qr"><img src="<?php echo $user_info->um_weixin; ?>" title="<?php _e('微信扫描二维码加我为好友并交谈','um'); ?>" /><div>微信扫描二维码加我为好友并交谈</div></div></a></span>		
											<?php } ?>
											<?php if(!empty($user_info->um_qq)){ ?>
											<span><a class="as-img as-qq" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $user_info->um_qq; ?>&site=qq&menu=yes" title="<?php _e('QQ交谈','um'); ?>"><i class="fa fa-qq"></i></a></span>
											<?php } ?>
											<span><a class="as-img as-email" href="mailto:<?php echo $user_info->user_email; ?>" title="<?php _e('给我写信','um'); ?>"><i class="fa fa-envelope"></i></a></span>
										</ul>
									</div>
								</div>
							</div>
						<!-- Manage menu widget -->
							<div class="um-widget manage-widget">
								<div class="widget-header clx">
									<div class="icon" style="font-size:32px;padding-top:3px;"><i class="fa fa-gears"></i></div>
									<h4>管理菜单</h4>
									<p class="widget-p">站内功能管理</p>
								</div>
								<div class="widget-body form-inline">
									<?php echo um_user_manage_widget(); ?>
								</div>
							</div>

						</div>
					</div>