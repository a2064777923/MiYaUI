<?php do_action('b2_normal_archive_before');
    $pizza =b2_get_option('Jitheme_index_tab5','one_index_user_id');
    $hotuser_title=b2_get_option('Jitheme_index_tab5','one_index_user_title');
    $hotuser_desc=b2_get_option('Jitheme_index_tab5','one_index_user_desc');   
    $hotuser_img=b2_get_option('Jitheme_index_tab5','one_index_user_img');  
    $default_image =B2_CHILD_URI.'/Center/Assets/images/wd.svg';
    $user_ID=array(
    'id' => explode(",", $pizza),
	); //调用用户ID
    $one_list=get_option('one_list');
?>
<div id="Onecad_hotuser" class="home_row module-posts ">
    <div id="user-list" ref="searchUser">
        <div class="shop-box-title jitheme-post-title">
            <div class="modules-title-box">
                <?php echo jithem_home_title($hotuser_title,$hotuser_desc,$hotuser_img,$default_image,'/ask') ?>
            </div>
        </div>
        <div class="hotuser-container">
            <div class="demo">
                    <?php 
                    $one_list=get_option('one_list');
                    $hotuser_a ='';
                    echo '<div class="home-section-designs" id="h_designer"> 
                         <div class="items"> ';
                    if (b2_get_option('Jitheme_index_tab5','one_index_user_ys')){
                        foreach ($user_ID['id'] as $user) {
                            $user_data = B2\Modules\Common\User::get_user_public_data($user);
                            $user_lv = B2\Modules\Common\User::get_user_lv($user);
                            $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                            $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                            $tips = __('这个人很懒，什么都没有留下！','b2');
                            $followers = get_user_meta($user,'zrz_followed',true);
                            $followers = is_array($followers) ? count($followers) : 0;
                            $following = get_user_meta($user,'zrz_follow',true);
                            $following = is_array($following) ? count($following) : 0;
                            $title = get_user_meta($user,'b2_title',true);
                            $desc = get_the_author_meta( 'description', $user );
                			$hotuser_a .= '<div class="box item-wrap b2-radius">
                							<div class="our-team b2-radius">
                								<div class="pic">
                                                    '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                								</div>
                								<div class="i-content">
                									<div class="user-s-info-name">
                										<h3 class="title">'.$user_data['name'].'
                										'.($user_data['user_title'] ? $user_data['verify_icon'] : '').'
                										</h3>
                										
                										<div>
                											<div class="topic-user-lv">
                												<p>'.$user_vip.'</p>
                												<p>'.$user_lv.'</p>
                											</div>
                											
                										</div>
                									</div>
                									<div class="user-s-data">
                										<div>
                                                        <span>'.__('文章','b2').'</span>
                                                        <p>'.count_user_posts($user,'post').'</p>
                										</div>
                										<div>
                											<span>'.__('评论','b2').'</span>
                											<p>'.B2\Modules\Common\Comment::get_user_comment_count($user).'</p>
                										</div>
                										<div>
                											<span>粉丝</span>
                											 <p>'.$followers.'</p>
                										</div>
                										<div>
                											<span>关注</span>
                											<p>'.$following.'</p>
                										</div>
                									</div>
                									<div class="user-s-info-desc">
                                                    '.($user_data['user_title'] ? $user_data['user_title'] : (isset($user_data['desc']) ? $user_data['desc'] : $tips)).'
                									</div>
                								</div>
                								<ul class="social">
                									<div class="user-s-follow">
                                            <a href="'.$user_data['link'].'" class="link-block">主页</a>
                									</div>
                								</ul>
                							</div>
                						</div>';
                    }
                    echo $hotuser_a;
                    }else {
                            foreach ($user_ID['id'] as $user) {
                                    $user_data = B2\Modules\Common\User::get_user_public_data($user);
                                    $user_lv = B2\Modules\Common\User::get_user_lv($user);
                                    $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                                    $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                                    $tips = __('这个人很懒，什么都没有留下！','b2');
                                    $followers = get_user_meta($user,'zrz_followed',true);
                                    $followers = is_array($followers) ? count($followers) : 0;
                                    $following = get_user_meta($user,'zrz_follow',true);
                                    $following = is_array($following) ? count($following) : 0;
                                    $title = get_user_meta($user,'b2_title',true);
                                    $desc = get_the_author_meta( 'description', $user );
                                    $hotuser_a .= '
                                          <div class="item-wrap  b2-radius"> 
                                           <div class="item   b2-radius"> 
                                            <a href="'.$user_data['link'].'" target="_blank"> 
                                             <div class="item-thumb "> 
                                              <i class="thumb " style="background-image:url('.$user_data['avatar'].')"></i> 
                                             </div> 
                                             <div class="item-main"> 
                                              <h2>'.$user_data['name'].''.$user_vip.''.$user_lv.'</h2> 
                                              <div class="one_list_a" >'.($user_data['user_title'] ? $user_data['user_title'] : ($user_data['desc'] ? $user_data['desc'] : $tips)).'</div> 
                                             </div></a> 
                                           </div> 
                                          </div> ';
                            }
                        echo $hotuser_a;
                    }
                    echo '</div></div>';
                    ?>
                </div>
            </div>
    </div>
</div>

