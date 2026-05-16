<!-- b2Jitheme_2.7.9_381_fa3795 重要注释，请勿删除 https://www.jitheme.com '2024-06-11 15:43:16' -->

<?php 
    //统一
    $ds_off=b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_off');
    //背景颜色
    $footer_color=b2_get_option('Jitheme_footer_main','footer_color');
    //字体颜色
    $footer_color_a=b2_get_option('Jitheme_footer_main','footer_text_color');
    //字体H颜色
    $footer_h_color=b2_get_option('Jitheme_footer_main','footer_h_color');
    //背景图片
    $footer_img = b2_get_option('Jitheme_footer_main','footer_img');
    //样式1
    $text_footer_title_ysa=b2_get_option('Jitheme_footer_tab2','text_footer_title_ysa');
    $text_footer_desc_ysa=b2_get_option('Jitheme_footer_tab2','text_footer_desc_ysa');
    $footer_fav_color=b2_get_option('Jitheme_footer_tab2','footer_fav_color');
    //样式2
    $yl_off=b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_yl');
    $link_cats = b2_get_option('template_footer','link_cat');
	$beian = b2_get_option('template_footer','footer_beian');
	$gongan = b2_get_option('template_footer','footer_gongan');
	$gongan_code = (int) filter_var($gongan, FILTER_SANITIZE_NUMBER_INT);
	$mobile_show_link =  b2_get_option('template_footer','footer_mobile_show_links');
	//简洁
	$fot_title=b2_get_option('Jitheme_footer_jj','fot_title');
	$desc_title=b2_get_option('Jitheme_footer_jj','desc_title');
	$fot_img=b2_get_option('Jitheme_footer_jj','fot_img');
	if($fot_img == 'none'){
	    $fot_img='';
	}
	$ewm=b2_get_option('Jitheme_footer_jj','ewm');
	$about_title=b2_get_option('Jitheme_footer_jj','about_title');
	$jj_about=b2_get_option('Jitheme_footer_jj','jj_about');
	//20230922
	$footer_logo_img = b2_get_option('Jitheme_footer_tab2','footer_desc_ysa_kf_logo');
	$onecad_footer_ysb_jj=b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_jj');
	//获取代码
	if($onecad_footer_ysb_jj){
	    $none='style="display: none;"';
	}else{
	    $none;
	}
?>
</div>
	<style>
	    .footer-links ul li a {color:<?php echo $footer_color; ?>}
		.onecad_new_footer,#bigTriangleColor,#ji-footer-new{
			background-color:<?php echo $footer_color; ?>;
			color:<?php echo $footer_color_a; ?>!important;
		}
		.onecad_new_footer a,.footer-navi a ,.footer-navi .ewms .like h3,.footer-navi .about,.foot-copyright p a,.foot-copyright p,.footer-links ul li a,.footer-bottom{
        	color:<?php echo $footer_color_a; ?>;
        }
        #onecad_footer_ysa .footer-fav{background-color:<?php echo $footer_fav_color; ?>}
        #ji-footer-new .ji-footer .footer-h ,.footer-navi .title{
            color:<?php echo $footer_h_color ;?>
        }
		.site-footer-nav,.jiid<?php echo jithemeid()?>{
			background-color:<?php echo $footer_color; ?>
		}
	</style>
    <footer id="onecad_new_footer" class="onecad_new_footer bg_img"  style="background-image: url(<?php echo $footer_img ?>)">
    <?php if(b2_get_option('Jitheme_footer_main','onecad_footer_off') == 1) { ?>
    <div id="onecad_footer_ysa">
        <div id="colophon" class="footer">
    		<?php do_action('b2_footer_before'); ?>
    	<!--footer样式1开始-->
        <!--第一部分-->
        <?php if(!empty(b2_get_option('Jitheme_footer_tab2','Onecad_footer_off'))){ ?>
        	<div class="footer-fav">
        		<div class="wrapper footer-fav-top">
        			<div class="Onecad_fl site-info">
        				<h2> <a href="/about" target="_blank" se_prerender_url="complete"><?php  echo b2_get_option('Jitheme_footer_tab2','footer_desc_ysa_kf_title'); ?></a> </h2>
        				<div class="site-p">
        					<a href="/about" target="_blank">
                                <?php  echo b2_get_option('Jitheme_footer_tab2','footer_desc_ysa_kf_desc'); ?>
        					</a>
        				</div>
        			</div>
        			<div class="fr site-fav">
        				<a href="#" class="btn btn-fav btn-orange"> <i class="tubiao wei-shoucang1"></i>按Ctrl+D收藏本站 </a>    
        			</div>
        			<div class="site-girl">
        				<a href="#" target="_blank">
        					<!--客服背景图片-->
        				<div class="girl Onecad_fl"> <i class="thumb " style="background-image:url(<?php  echo b2_get_option('Jitheme_footer_tab2','footer_desc_ysa_kf_img'); ?>)"></i> </div>
        				<div class="girl-info hide_md">
        					<?php  echo b2_get_option('Jitheme_footer_tab2','footer_desc_ysa_kf_name'); ?>
        				</div>
        				</a>
        			</div>
        		</div>
        	</div>
    	<?php } ?>
        <!--第二部分-->
    	<div id="Onecad_footer_ys2" class="footer-navi">
    		<div class="wrapper">
    			<div class="about widget Onecad_fl aa">
    			  <a href="/"><img class="footer-logo" src="<?php echo $footer_logo_img ?>"></a>
    			  <div class="title"><?php  echo $text_footer_title_ysa ?></div>
    			  <p><?php  echo $text_footer_desc_ysa ?></p>
    			</div>
    			<div class="navis Onecad_fl hide_sm">
    			    <?php
                        $links_html=''; 
                        $links_ysa_a1 =b2_get_option('Jitheme_footer_tab2','one_ysa_links');
                            if(is_array($links_ysa_a1)){
                                foreach ($links_ysa_a1 as $k => $v) {
                                    $links_html .= '<div class="navi"><h2 class="title">'.$v['ysa_links_title'].'</h2><ul>';
                                    if($v['ysa_links_title']){
                                        $links_html .= $v['ysa_links_desc'];
                                    }
                                $links_html .= '</ul></div>';
                                }
                            echo $links_html;
                            }
                    ?>
    			</div>
    			<div class="ewms widget fr hide_sm">
    			  <ul class="footer_clearfix">
    			    <?php
    			        $kefu_html=''; 
    			        $kefu_ysa_a1 =b2_get_option('Jitheme_footer_tab2','one_ysa_kefu');
                            if(is_array($kefu_ysa_a1)){
                                foreach ($kefu_ysa_a1 as $k => $v) {
                                    if($v['ysa_kefu_title']){
                                        $kefu_html .= '
                                    <li>
                        			  <div>
                        			    <div class="Onecad_footer_ico"><i class="thumb " style="background-image:url('.$v['ysa_kefu_title_img'].');border-radius: 50px;"></i></div>
                        				<h2>'.$v['ysa_kefu_title'].'</h2>
                        			  </div>
                        			  <div class="ewm-content Onecad_hide ewm-weibo b2-radius">
                        				<div class="ewm-main">
                        				  <div class="thumb-div Onecad_fl"> <i class="thumb b2-radius" style="background-image:url('.$v['ysa_kefu_img'].');"></i> </div>
                        				  <div>
                            				  '.$v['ysa_kefu_desc'].'
                            				  <sapn>'.$v['ysa_kefu_url'].'</sapn>
                        				  </div>
                        				</div>
                        			  </div>
                        			</li>';
                                    }
                                }
                            echo $kefu_html;
                            }
    				?>
    			  </ul>
    			  <div class="like">
    				<strong><?php  echo b2_get_option('Jitheme_footer_tab2','text_footer_desc_ysa_sz'); ?></strong>
    				<h3><?php  echo b2_get_option('Jitheme_footer_tab2','text_footer_desc_ysa_ms'); ?></h3>
    			  </div>
    			</div>
    		</div>
    		<div class="wrapper wrapper-bq">
    			<div class="bands">
    				<ul>
    			    <?php
    			        $gg_html=''; 
    			        $gg_ysa_a1 =b2_get_option('Jitheme_footer_main','onecad_footer_ggz');
                            if(is_array($gg_ysa_a1)){
                                foreach ($gg_ysa_a1 as $k => $v) {
                                    if($v['onecad_footer_ggz_title']){
                                        $gg_html .= '<li> <a href="'.$v['onecad_footer_ggz_url'].'" class="thumb band" target="_blank" style="background-image: url('.$v['onecad_footer_ggz_img'].');background-size: 125px 40px;"> <span class="hidden">'.$v['onecad_footer_ggz_title'].'</span> </a> </li>';
                                    }
                                }
                            echo $gg_html;
                            }
    				?>
    				</ul>
        			<div class="qqgroup fr hide_sm" style="margin-left: 16px;margin-right: 30px;">
        				<a href="<?php  echo b2_get_option('Jitheme_footer_tab2','text_footer_desc_ysa_qqun'); ?>"><?php  echo b2_get_option('Jitheme_footer_tab2','text_footer_desc_ysa_qqm'); ?></a>
        			</div>
    			</div>
    		</div>
    	</div>
        <!--第三部分-->
        <div class="footer_hx">
    		<div class="footer-colors"></div>
    		
    	</div>
    	<!--footer样式1结束-->
    	<?php 
    		$ids = array();
    		$bookmarks = array();
    		if($link_cats){
    			foreach($link_cats as $v){
    				$links = get_term_by('slug', $v, 'link_category');
    					if($links){
    						$ids[] = $links->term_id;
    					}
    			}
    		}
    		$ids = implode(",", $ids);
    			if($ids){
    				$bookmarks = get_bookmarks(array(
    				'category'=>$ids,
    				'orderby'=>'link_rating',
    				'order'=>'DESC'
    			));
    		}
    	?>
    	<?php if((is_home() || is_front_page()) && !empty($link_cats) && !empty($bookmarks)){ ?>
    	<div class="footer-links <?php echo (int)$mobile_show_link === 0 ? 'mobile-hidden' : ''; ?>">
    							<?php
    								echo '<ul>';
    								    echo '<li><p target="_blank" href="#">友情链接：</p></li>';
    									foreach ($bookmarks as $bookmark) {
    										echo '<li><a target="_blank" href="' . $bookmark->link_url . '">' . $bookmark->link_name . '</a></li>';
    									}
    					echo '</ul>';
    				unset($bookmarks);
    			?>
    	</div>
    	<?php } ?>
    	<div class="foot-copyright"> 
    		<div class="wrapper"> 
    		<p class="foot-copyright-fl fla">版权所有<?php echo 'Copyright &copy; '.date('Y').'<a href="'.B2_HOME_URI.'" rel="home">&nbsp;'.get_bloginfo('name').'</a>'; ?>保留资源解释权，如有侵权，请联系我及时处理。<?php if($beian){
								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="https://beian.miit.gov.cn">'.$beian.'</a>';
							} ?>
							<?php if($gongan){
								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode='.$gongan_code.'"><img src="'.B2_THEME_URI.'/Assets/fontend/images/beian-ico.png">'.b2_get_option('template_footer','footer_gongan').'</a>';
							}?>
        								<br>
    		                    <?php  get_template_part ( 'text_logo' , 'b2_myclass_login_options_page' ); ?>
    							<?php
    								echo sprintf(__('查询 %s 次，','b2'),get_num_queries());
    								echo sprintf(__('耗时 %s 秒','b2'),timer_stop(0,4));
    		                    ?>
    		</p>
    		</div>
    	</div>
    </div>
    </div>
    <?php } elseif(b2_get_option('Jitheme_footer_main','onecad_footer_off') == 0) { ?>
    <div id="onecad_footer_ysb">
        <?php if(!empty($ds_off)) {?>
        <div id="onecad_footer_ht" <?php echo $none ?> >
            <div class="Onecad-action-panel" style="background-image:url(<?php echo b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_bg')?>)">
                <div class="Onecad-action-panel__inner wrapper">
                    <main class="Onecad-action-panel__main wrapper">
                        <div class="Onecad-action-panel__desc">
                            <div class="tpm1-action-panel-text-tit">
                                <span>
                                <?php echo b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_htsmz')?>
                                </span>
                            </div>
                            <div class="tpm1-action-panel-text-subtit">
                                <p>
                                <?php echo b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_htsmf')?>
                                </p>
                            </div>
                        </div>
                    </main>
                    <aside class="Onecad-action-panel__side">
                        <div class="Onecad-action-panel__qrcodes">
                            <div class="tpm1-qrcode">
                                <div class="tpm1-qrcode__img">
                                    <img class="b2-radius" title="群二维码" src="<?php echo b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_qewm')?>">
                                </div>
                                <div class="tpm1-qrcode__text">
                                    <span>
                                        粉丝群
                                    </span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
        <?php } ?>
        <div id="onecad_footer_db" class="Onecad-new-footer jinsom-footer ">
            <?php if(!empty($yl_off))  { ?>
            <div class="Onecad-wrap" <?php echo $none ?> >
                <div class="partner wrapper">
                    <h2 class="title">合作伙伴</h2>
                    <div class="partner-first">
        			    <?php $gg_html='';  $gg_ysa_a1 =b2_get_option('Jitheme_footer_main','onecad_footer_ggz');
                                if(is_array($gg_ysa_a1)){
                                    foreach ($gg_ysa_a1 as $k => $v) {
                                        if($v['onecad_footer_ggz_title']){
                                            $gg_html .= '<a href="'.$v['onecad_footer_ggz_url'].'" target="_blank"><img src="'.$v['onecad_footer_ggz_img'].'"></a>';
                                        }
                                    }
                                echo $gg_html;
                                }
        				?>
                    </div>
                    <div class="partner-second">
                    	<?php 
                		$ids = array();
                		$bookmarks = array();
                		if($link_cats){
                			foreach($link_cats as $v){
                				$links = get_term_by('slug', $v, 'link_category');
                					if($links){
                						$ids[] = $links->term_id;
                					}
                			}
                		}
                		$ids = implode(",", $ids);
                			if($ids){
                				$bookmarks = get_bookmarks(array(
                				'category'=>$ids,
                				'orderby'=>'link_rating',
                				'order'=>'DESC'
                			));
                		}
                	?>
                    <?php if(!empty($link_cats) && !empty($bookmarks)){ ?>
    							<?php
    									foreach ($bookmarks as $bookmark) {
    										echo '<a target="_blank" href="' . $bookmark->link_url . '">' . $bookmark->link_name . '</a>';
    									}
    				unset($bookmarks);
    			    ?>
                	<?php } ?>
                        <h2 class="hyth-link-apply"><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_qq') ?>&site=qq&menu=yes">申请友链</a></h2>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="partlink" <?php echo $none ?> >
                <div class="Onecad-wrap wrapper">
                    <div class="mail">
                        <div class="title">联系我们</div>
                        <div class="hyth-contact-mail"><i class="jitheme ji-phone-line"></i>电话:<a href="<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_kefu') ?>"><?php echo b2_get_option('Jitheme_footer_main','onecad_footer_kefu') ?></a></div>
                        <div class="hyth-contact-mail"><i class="jitheme ji-mail-send-line"></i>邮箱:<a href="<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_email') ?>"><?php echo b2_get_option('Jitheme_footer_main','onecad_footer_email') ?></a></div>
                    </div>
                    <!--<div class="help-btn">-->
                    <!--    <div class="title">帮助我们</div>-->
                    <!--    <div class="hyth-contact-mail"><i class="jitheme ji-phone-line"></i>电话:<a href="<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_kefu') ?>"><?php echo b2_get_option('Jitheme_footer_main','onecad_footer_kefu') ?></a></div>-->
                    <!--    <div class="hyth-contact-mail"><i class="jitheme ji-mail-send-line"></i>服务时间：9:00-18:00</a></div>-->
                    <!--</div>-->
                    <div class="foot-cell">
                        <div class="tit">客服</div>
                        
                        <div class="im"><span><i class="b2font b2-qq"></i><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_qq') ?>&site=qq&menu=yes">在线客服</a></span></div>
                        <div class="email"><i class="jitheme ji-mail-send-line"></i>客服邮箱:<a href="<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_email') ?>"><?php echo b2_get_option('Jitheme_footer_main','onecad_footer_email') ?></a></div>
                    </div>
                    <div class="foot-cell">
                        <div class="tit">举报</div>
                        <div class="im"><i class="jitheme ji-phone-line"></i>举报电话:<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_jiandu') ?></div>
                        <div class="email"><i class="jitheme ji-mail-send-line"></i>举报邮箱:<a href="<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_jiandu_yx') ?>"><?php echo b2_get_option('Jitheme_footer_main','onecad_footer_jiandu_yx') ?></a></div>
                    </div>
                    <?php
                        $ewm_html=''; 
                        $links_ysa_a1 =b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_ewm');
                            if(is_array($links_ysa_a1)){
                                foreach ($links_ysa_a1 as $k => $v) {
                                        $ewm_html.= '
                                        <div class="foot-blank">
                                            <span class="qrcode"><img border="0" src="'.$v['onecad_footer_ysb_ewm_img'].'"></span>
                                            <div class="imgbox">
                                                <img border="0" src="'.$v['onecad_footer_ysb_ewm_ico'].'">
                                            </div>
                                            <p>'.$v['onecad_footer_ysb_ewm_title'].'</p>
                                        </div>';
                                }
                            echo $ewm_html;
                            }
                    ?>
                </div>
            </div>
            <div class="copyright">
                <div class="links">
                    <?php
                        $links_html=''; 
                        $links_ysa_a1 =b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_links');
                            if(is_array($links_ysa_a1)){
                                foreach ($links_ysa_a1 as $k => $v) {
                                    if(!empty($v['onecad_footer_ysb_links_desc'])){
                                        $links_html.= $v['onecad_footer_ysb_links_desc'];
                                    }
                                }
                            echo $links_html;
                            }
                    ?>
                </div>
                <?php echo b2_get_option('Jitheme_footer_tab3','onecad_footer_ysb_sm');  ?>
            </div>
        </div>
        <div class="onecad_new_footer_sj">
                <p>版权所有<?php echo 'Copyright &copy; '.date('Y').'<a href="'.B2_HOME_URI.'" rel="home">&nbsp;'.get_bloginfo('name').'</a>'; ?>保留资源解释权，如有侵权，请联系我及时处理<?php if($beian){
								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="https://beian.miit.gov.cn">'.$beian.'</a>';
							} ?>
							<?php if($gongan){
								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode='.$gongan_code.'"><img src="'.B2_THEME_URI.'/Assets/fontend/images/beian-ico.png">'.b2_get_option('template_footer','footer_gongan').'</a>';
							}?>
        								<br>
        		                    <?php  get_template_part ( 'text_logo' , 'b2_myclass_login_options_page' ); ?>
        							<?php
        								echo sprintf(__('查询 %s 次，','b2'),get_num_queries());
        								echo sprintf(__('耗时 %s 秒','b2'),timer_stop(0,4));
        		                    ?>
        		</p>
        		
        </div>
    </div>
    <?php }else{ ?>
    <div id="ji-footer-new" class="footer">
        <?php if(b2_get_option('Jitheme_footer_jj','jj_ds_offa')){ ?>
        <div class="footer-top">
            <div class="wrapper">
                <div class="callto-action">
                    <h3 class="call-to-title"><?php echo $fot_title ?></h3>
                    <div class="wx-qr">
                            <img width="160" height="160" src="<?php echo $ewm ?>" alt="微信扫一扫联系我们" title="微信扫一扫联系我们" data-lazy-src="<?php echo $ewm ?>" data-ll-status="loaded" class="entered lazyloaded">
                    </div>
                    <p class="more-services"><a href="/services/"><?php echo $desc_title ?></a></p>
                </div>
            </div>
        </div>
        <?php } ?>
        <div style="background-image:url(<?php echo $footer_img  ?>)">
            <div class="ji-footer">
                <div class="wrapper">
                    <div class="ji-footer-widget-in">
                        <section id="text-3" class="widget widget_text mg-b b2-radius">
                            <h2 class="widget-title footer-h"><?php echo $about_title ?></h2>
                            <div class="textwidget">
                                <p><?php echo $jj_about ?></p>
                            </div>
                        </section>
                            <?php
                                $jj_html=''; 
                                $jj_links =b2_get_option('Jitheme_footer_jj','jj_new_links');
                                    if(is_array($jj_links)){
                                        foreach ($jj_links as $k => $v) {
                                            if($v['links']){
                                                $str = trim($v['links'], "\t\n\r\0\x0B\xC2\xA0");
                                                $more=array(
                                                'more' => explode(PHP_EOL, $str),
                                            	); 
                                            }
                                            $vvv="nav_menu-2";
                                            $count = count($more['more']);
                                            if($count > 5){
                                                $vvv="nav_menu-1";
                                            }
                                            $jj_html .= '<section id="'.$vvv.'" class="widget widget_nav_menu mg-b b2-radius">
                                                            <h2 class="widget-title footer-h">'.$v['title'].'</h2>
                                                            <div class="menu-footer-container">
                                                                <ul id="menu-footer" class="menu">';
                                            if($v['title']){
                                                $jj_html .=$v['links'];
                                            }
                                        $jj_html .= '</ul></div></section>';
                                        }
                                    echo $jj_html;
                                    }
                            ?>
                        <section id="custom_html-3" class="widget_text widget widget_custom_html mg-b b2-radius">
                            <h2 class="widget-title  footer-h">关注交流</h2>
                                <div class="textwidget custom-html-widget">
                                    <div class="row">
                            
                            <?php
                                $ewm_html=''; 
                                $ewm_links =b2_get_option('Jitheme_footer_jj','jj_new_ewm');
                                if(is_array($ewm_links)){
                                    foreach ($ewm_links as $k => $v) {
                                        $ewm_html .= '<div class="col-md-4"><p>
                                        <img width="75" height="75" alt="'.$v['title'].'" src="'.$v['ewm'].'" data-lazy-src="'.$v['ewm'].'" data-ll-status="loaded" class="entered lazyloaded">
                                        </p>';
                                        $ewm_html .= '<p class="qr-tips">'.$v['title'].'</p>';
                                        $ewm_html .= '</div>';
                                    }
                                echo $ewm_html;
                                }
                            ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <div class="ji-footer-nav">
                <div class="wrapper">
				<?php 
					$link_cats = b2_get_option('template_footer','link_cat');
					$beian = b2_get_option('template_footer','footer_beian');
					$gongan = b2_get_option('template_footer','footer_gongan');
					$gongan_code = (int) filter_var($gongan, FILTER_SANITIZE_NUMBER_INT);
					$mobile_show_link =  b2_get_option('template_footer','footer_mobile_show_links');
					$ids = array();
					$bookmarks = array();
					if($link_cats){
						foreach($link_cats as $v){
							$links = get_term_by('slug', $v, 'link_category');
							if($links){
								$ids[] = $links->term_id;
							}
						}
					}
					$ids = implode(",", $ids);
					if($ids){
						$bookmarks = get_bookmarks(array(
							'category'=>$ids,
							'orderby'=>'link_rating',
							'order'=>'DESC'
						));
					}
				?>
				<?php if((is_home() || is_front_page()) && !empty($link_cats) && !empty($bookmarks)){ ?>
					<div class="footer-links <?php echo (int)$mobile_show_link === 0 ? 'mobile-hidden' : ''; ?>">
						<?php
							echo '<ul><li><span target="_blank" href="https://www.cadsee.cn/">友情链接：</span></li>';
								foreach ($bookmarks as $bookmark) {
									echo '<li><a target="_blank" href="' . $bookmark->link_url . '">' . $bookmark->link_name . '</a></li>';
								}
							echo '</ul>';
							
						?>
					</div>
				<?php } ?>
                    <div class="footer-bottom">
                        <div class="footer-bottom-left">
         		<p class="foot-copyright-fl fla">版权所有<?php echo 'Copyright &copy; '.date('Y').'<a href="'.B2_HOME_URI.'" rel="home">&nbsp;'.get_bloginfo('name').'</a>'; ?>保留资源解释权，如有侵权，请联系我及时处理。<?php if($beian){
    								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="https://beian.miit.gov.cn">'.$beian.'</a>';
    							} ?>
    							<?php if($gongan){
    								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode='.$gongan_code.'"><img src="'.B2_THEME_URI.'/Assets/fontend/images/beian-ico.png">'.b2_get_option('template_footer','footer_gongan').'</a>';
    							}?>
            								<br>
        		                    <?php  get_template_part ( 'text_logo' , 'b2_myclass_login_options_page' ); ?>
        							<?php
        								echo sprintf(__('查询 %s 次，','b2'),get_num_queries());
        								echo sprintf(__('耗时 %s 秒','b2'),timer_stop(0,4));
        		                    ?>
        		</p>
                        </div>
                    </div>
        
                </div>
            </div>
        </div>
    </div>
    <?php  }?>
    <?php
    $celan=b2_get_option('Jitheme_footer_tab4','onecad_footer_celan_off');
    $wrapper_width = b2_get_option('template_main','wrapper_width');
    if($celan){ ?>
    <div id="jitheme_celan">
        <div class="fixed-right" style="right:16px;">
    <div class="drop">
        <a href="/vips" class="go_top item">
            <i class="b2font b2-vip-crown-2-line ">
            </i>
        </a>
    </div>
    <div class="menus">
        <span class="item ewm hide_sm">
            <i class="b2font b2-qr-code-fill ">
            </i>
            <div class="code-div">
                <div class="ewmDiv">
                    <?php
                        $kf_html=''; 
                        $kf_html_img =b2_get_option('Jitheme_footer_tab4','Jitheme_footer_celan');
                            if(is_array($kf_html_img)){
                                foreach ($kf_html_img as $k => $v) {
                                    $kf_html .='<div class="ewm-item">
                                        <a href="'.$v['celan_kf_link'].'" target="_blank">
                                            <div class="code-wrap">
                                                <div class="code" style="background-image:url('.$v['celan_kf_img'].');"></div>
                                            </div>
                                            <div class="ewm-main">
                                                <p>'.$v['celan_kf_desc'].'</p>
                                                <div class="wz">'.$v['celan_kf_title'].'</div>
                                            </div>
                                        </a>
                                    </div>'; 
                                }
                            }
                            echo $kf_html;
                    ?>
                </div>
            </div>
        </span>
    </div>
    <div class="drop">
        <a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo b2_get_option('Jitheme_footer_main','onecad_footer_qq') ?>&site=qq&menu=yes"
        class="go_top item">
            <i class="b2font b2-customer-service-2-line1 ">
            </i>
        </a>
    </div>
    <div class="gotop">
        <a href="#" class="go_top item">
            <i class="b2font b2-rocket-2-line ">
            </i>
        </a>
    </div>
</div>
    </div>
    <?php } ?>
</footer>
	<?php if(!is_audit_mode()) { ?>
	<div id="mobile-footer-menu" class="mobile-footer-menu mobile-show footer-fixed" ref="footerMenu" v-show="show">
		<div class="mobile-footer-left">
			<?php echo B2\Modules\Templates\Footer::footer_menu_left(); ?>
		</div>
		<div class="mobile-footer-center">
			<button @click="postPoBox.show = true"><span><?php echo b2_get_icon('b2-add-line b2-radius'); ?></span></button>
		</div>
		<div class="mobile-footer-right">
			<?php echo B2\Modules\Templates\Footer::footer_menu_right(); ?>
		</div>
	</div>
	<?php } ?>
	<?php 
		$allow_newsflashes = b2_get_option('newsflashes_main','newsflashes_open');
		$allow_document = b2_get_option('document_main','document_open');
		$allow_circle = b2_get_option('circle_main','circle_open');
        $allow_infomation = b2_get_option('infomation_main','infomation_open');
		$allow_ask = b2_get_option('ask_main','ask_open');
		$link_open = b2_get_option('links_main','link_open');
		$circle_sulg = b2_get_option('normal_custom','custom_circle_link');
		$circle_name = b2_get_option('normal_custom','custom_circle_name');
		$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
		$infomation_name = b2_get_option('normal_custom','custom_infomation_name');
		$ask_name = b2_get_option('normal_custom','custom_ask_name');
		$link_name = b2_get_option('normal_custom','custom_links_name');

		$infomation_slug = b2_get_option('normal_custom','custom_infomation_link');
		$ask_slug = b2_get_option('normal_custom','custom_ask_link');

		$link_slug = b2_get_option('normal_custom','custom_links_link');

		$open_write = b2_get_option('normal_write','write_allow');	
		
	?>
	<div id="post-po-box" class="post-po-box <?php echo jithemeid()?>">
		<div :class="['post-box-content',{'show':show}]" @click="show = false">
			<div class="po-post-in b2-radius" v-cloak>
				<div class="po-post-icons">
    				<?php if($open_write){ ?>
    					<div>
    						<button @click.stop="go('<?php echo b2_get_custom_page_url('write'); ?>','write')">
    							<span class="po-post-icon"><?php echo b2_get_icon('b2-quill-pen-line'); ?></span>
    							<span class="po-post-title"><?php echo __('发布文章','b2'); ?></span>
    						</button>
    					</div>
    				<?php } ?>
					<?php if($allow_newsflashes){ ?>
						<div>
							<button @click.stop="go('<?php echo get_post_type_archive_link('newsflashes'); ?>?action=showbox','newsflashes')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-flashlight-line'); ?></span>
								<span class="po-post-title"><?php echo sprintf(__('发布%s','b2'),$newsflashes_name); ?></span>
							</button>
						</div>
					<?php } ?>
					<?php if($allow_circle){ ?>
						<div>
							<button @click.stop="go('<?php echo b2_get_custom_page_url('create-circle'); ?>','create_circle')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-donut-chart-fill'); ?></span>
								<span class="po-post-title"><?php echo sprintf(__('创建%s','b2'),$circle_name); ?></span>
							</button>
						</div>
						<div>
							<button @click.stop="go('<?php echo home_url('/').$circle_sulg; ?>','create_topic')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-chat-smile-3-line'); ?></span>
								<span class="po-post-title"><?php echo __('发表话题','b2'); ?></span>
							</button>
						</div>
					<?php } ?>
					<?php if($allow_infomation){ ?>
						<div>
							<button @click.stop="go('<?php echo B2_HOME_URI.'/po-'.$infomation_slug; ?>','infomation')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-document1196064easyiconnet'); ?></span>
								<span class="po-post-title"><?php echo sprintf(__('发布%s','b2'),$infomation_name); ?></span>
							</button>
						</div>
					<?php } ?>
					<?php if($allow_ask){ ?>
						<div>
							<button @click.stop="go('<?php echo B2_HOME_URI.'/po-'.$ask_slug; ?>','ask')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-ask'); ?></span>
								<span class="po-post-title"><?php echo sprintf(__('发布%s','b2'),$ask_name); ?></span>
							</button>
						</div>
					<?php } ?>
					
					<?php if($link_open){ ?>
						<div>
							<button @click.stop="go('<?php echo B2_HOME_URI.'/link-register'; ?>','link')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-xitongdaohang'); ?></span>
								<span class="po-post-title"><?php echo sprintf(__('发布%s','b2'),$link_name); ?></span>
							</button>
						</div>
					<?php } ?>
					
					
					<?php if($allow_document){ ?>
						<div class="po-verify">
							<button @click.stop="go('<?php echo b2_get_custom_page_url('requests'); ?>','request')">
								<?php echo b2_get_icon('b2-clipboard-line'); ?><span><?php echo __('提交工单','b2'); ?></span>
							</button>
						</div>
					<?php } ?>
				</div>
				<div class="po-close-button">
					<button @click.stop="show = false"><?php echo b2_get_icon('b2-close-line'); ?></button>
				</div>
			</div>
		</div>
    </div>
	<form id="wechataction" name="wechataction" action method="post">
    	<input type="submit" value="ok" style="display:none;">
	</form>
</div>

<?php
	if ( is_front_page() ) {
		echo get_footer_tsk();
	}
?>
<script>
  var closeButtons = document.getElementsByClassName('close');

  for (var i = 0; i < closeButtons.length; i++) {
    closeButtons[i].addEventListener('click', function() {
      this.parentNode.style.display = 'none';
    });
  }
</script>
<?php wp_footer(); ?>
<!--分类切换-->
<script src="<?php echo B2_CHILD_URI ?>/Render/Js/audio.js"></script> 
<script src="<?php echo B2_CHILD_URI ?>/Render/Js/jitheme.js"></script> 
<!--头部搜索-->
<script type="text/javascript">
        var onecad_search = document.getElementById("onecad_search");
        var onecad_btna = document.getElementById("onecad_btna");
        var btnb = document.getElementById("onecad_btnb");
        var mobile_menu  = document.getElementById("mobile-menu");
    function testDisplay()
    {
    
        if(onecad_search.style.top="-70px")
        {
            onecad_search.style.display = "block";
            onecad_search.style.top = "0px";
            onecad_search.style.transition = "all 0.3s ease-in-out 0s";
            onecad_btnb.style.display = "block";
            onecad_btna.style.display = "none";
            mobile_menu.style.display = "none";
        }
    }
    function testnone()
    {
        if(onecad_search.style.display=="block")
        {
            onecad_btna.style.display = "block";
            onecad_search.style.transition = "all 0.3s ease-in-out 0s";
            onecad_search.style.top = "-70px";
            onecad_btnb.style.display = "none";
            mobile_menu.style.display = "block";
            
        }
    }
</script>
<?php
$qukuai_day=b2_get_option('Jitheme_index_main','time_data');
$text_date_h=b2_get_option('Jitheme_index_main','text_date_h');
$qukuai_data=$qukuai_day.' '.$text_date_h
?>
<script type="text/javascript">	
        //设置定时器容器
        var countDownTimer = null ;
        //获取元素
        var day =  document.getElementById("_d");
        var hour = document.getElementById("_h");
        var minute = document.getElementById("_m");
        var second = document.getElementById("_s");
	    //获取截止时间的时间戳（单位毫秒）
	    var str = "<?php echo $qukuai_data ?>"
        var inputTime = +new Date(str);
        //我们先调用countDown函数，可以避免在打开界面后停一秒后才开始倒计时
        if(day && hour && minute && second) {
            countDown();
        }
        //定时器 每隔一秒变化一次
        countDownTimer =  setInterval(countDown, 1000);
        function countDown() {
            //获取当前时间的时间戳（单位毫秒）
            var nowTime = +new Date();
            //把剩余时间毫秒数转化为秒
            var times = (inputTime - nowTime) / 1000;
            if(times > 0){
                 //计算天数
                var d = Math.floor(times/60/60/24)
                if(day){
                    day.innerHTML = d
                    //如果小时数小于 10，要变成 0 + 数字的形式 赋值给盒子
                    day.innerHTML = d < 10 ? "0" + d : d;
                }
                //计算小时数 转化为整数
                var h = parseInt(times / 60 / 60 % 24);
                //如果小时数小于 10，要变成 0 + 数字的形式 赋值给盒子
                if(hour){
                    hour.innerHTML = h < 10 ? "0" + h : h;
                }
                //计算分钟数 转化为整数
                var m = parseInt(times / 60 % 60);
                //如果分钟数小于 10，要变成 0 + 数字的形式 赋值给盒子
                if(minute){
                    minute.innerHTML = m < 10 ? "0" + m : m;
                }
                //计算描述 转化为整数
                var s = parseInt(times % 60);
                //如果秒钟数小于 10，要变成 0 + 数字的形式 赋值给盒子
                if(second){
                    second.innerHTML = s < 10 ? "0" + s : s;
                }
            }else{
                // 停止定时器，清空定时器
                clearInterval(countDownTimer)
            }
        }
</script>

</body>
</html>