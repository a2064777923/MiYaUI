<?php do_action('b2_normal_archive_before');
$tj_user_id =b2_get_option('Jitheme_index_tab5','one_index_tj_user_id');
$tj_user_title =b2_get_option('Jitheme_index_tab5','one_index_tj_user_title');
$tj_user_desc =b2_get_option('Jitheme_index_tab5','one_index_tj_user_desc');
$one_index_tj_user_toub_title =b2_get_option('Jitheme_index_tab5','one_index_tj_user_toub_title');
$one_index_tj_user_toub_desc =b2_get_option('Jitheme_index_tab5','one_index_tj_user_toub_desc');
$one_index_tj_user_toub_user =b2_get_option('Jitheme_index_tab5','one_index_tj_user_toub_user');
?>
<style>

</style>
<div id="Onecad-tuijian" class="home_row module-posts ">
    <div id="user-list"  class="home-authors authors_jitheme" ref="searchUser">
        <div class="wrapper" >
            <div class="post-modules-top ">
                <div class="modules-title-box">
                    <div class="Onecad_title">
                        <div><?php echo $tj_user_title ?></div>
                        <div><?php echo $tj_user_desc ?></div>
                    </div>
                </div>
                <div class="post-list-cats post-list-cats-has-title">
                    <div class="post-carts-list-row">
                        <a href="../all_user" class="cat-list post-load-button picked">
                            <span data-type="cat">
                                全部
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <div  class="part-content">
                <div class="items author-items">
                    <?php 
                        $args=array(
                            'id' => explode(",", $tj_user_id),   // 分类ID
                        );
                        $i=0;
                        $tybj = b2_get_option('Jitheme_Archive_main','Onecad_tybj_off');
                        foreach ($args['id'] as $user) {
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
                        $post_user = count_user_posts($user,'post',true);
                        $ids[] = $user;
                        $i++;
                        echo '
                        <div id="item-author" class="item item-author" >
                            <div class="item-wrap box b2-radius">
                                '.jithem_jianbian().'
                                <div class="item-top b2-radius">
                                    <a class="author-intro" href="'.$user_data['link'].'" target="_blank">
                                        <div class="author-avatar">
                                        '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                        '.($user_data['user_title'] ? $user_data['verify_icon'] : '').'
                                        </div>
                                        <div class="author-main">
                                            <h2 class="author-name">
                                                <span class="uname" title="'.$user_data['name'].'">'.$user_data['name'].'</span><span class="long-label">'.$user_vip.$user_lv.'</span>
                                            </h2>
                                            <h3 class="author-meta">
                                                <span><i class="b2font b2-hearts-line"></i>
                                                    粉丝 '.$followers.'
                                                </span>
                                                <span><i class="b2font b2-article-line"></i>
                                                    文章 '.$post_user.'
                                                </span>
                                            </h3>
                                        </div>
                                    </a>
                                    <div class="author-info">
                                        <p><i class="b2font b2-file-list-2-line"></i>                                                        '.($user_data['user_title'] ? $user_data['user_title'] : ($user_data['desc'] ? $user_data['desc'] : $tips)).'</p>
                                        <div class="author-btn">
                                            <div class="btn btn-orange" data-component="follow" data-count="471" data-original-count="471"data-login="need" data-uid="21727">
                                                <div class="user-s-follow jitheme-button">
                                                <button class="author-has-follow red-color" v-if="follow['.$user.'] === true" v-cloak @click="followAc('.$user.')"><i class="b2font b2-heart-fill"></i>'.__('已关注','b2').'</button>
                                                <button class="empty  red-color" v-else v-cloak @click="followAc('.$user.')"><i class="b2font b2-add-line "></i>'.__('关注','b2').'</button>
                                                <button class="blue-color" @click="dmsg('.$user.')"><i class="b2font b2-mail-send-line "></i>'.__('私信','b2').'</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-bottom">
                                    <h3 class="item-bottom-title">最近更新</h3>
                                    <div class="item-bottom-cont">
                                        <div class="items">';
                                            $query = new WP_Query(
                                            array(
                                                'author' => $user,
                                                'posts_per_page' =>2,
                                            )
                                            );
                                            $posts = $query->posts;
                                            if(!empty($posts)){
                                                foreach($posts as $k => $p): 
                                                    $thumb_url = \B2\Modules\Common\Post::get_post_thumb($p->ID);
                                                    
                                                    $thumb = b2_get_img(array('src'=>$thumb_url));
                                                    foreach((get_the_category($p->ID)) as $category){
                                                        $wenzfl=$category->cat_name; 
                                                        }
                                                echo'
                                                <div class="ap-item">
                                                    <a class="ap-item-wrap has-thumb" href="'.get_permalink($p->ID).'"target="_blank">
                                                        <div class="ap-item-thumb  b2-radius">
                                                            '.$thumb.'
                                                        </div>
                                                        <div class="ap-item-main">
                                                            <h3 class="ap-item-title">'.$p->post_title.'</h3>
                                                            <h4 class="ap-item-meta">
                                                                <div class="jitheme_cat"><div class="post-list-cat">'.$wenzfl.'</div></div>
                                                            </h4>
                                                        </div>
                                                    </a>
                                                </div>';
                                                endforeach; 
                                            }else{
                                        	    echo '<div class="user-item">
                            	                    <div class="tobe-author-wrap">
                            	                        <div class="post-info  b2-radius">
                            	                            
                            	                            <div class="post-excerpt">
                            	                                <h2 class="title">暂无文章</h2> 
                            	                                <p>目前他暂无发布任何文章，了解更多，请前往个人中心查看。</p>
                            	                           </div> 
                            	                           <div class="item-btns"><a class="jitheme-jb-btn" href="'.$user_data['link'].'" target="_blank">个人中心</a></div>
                            	                       </div>
                            	                   </div>
                            	               </div>';
                                        	}
                                            echo'
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
                            }
                                unset($users_found);
                                unset($users);
                                wp_localize_script( 'b2-js-main', 'b2_search_data', array(
                                    'users'=>$ids
                                ))            
                    ?>
                    <div id="item-tobe-author" class="item item-tobe-author item-author" >
                        <div class="item-wrap box b2-radius">
                            <div class="item-wrapb b2-radius">
                                <?php echo jithem_jianbian() ?>
                                <div class="tobe-author-wrap">
                                    <div class="tobe-author b2-radius">
                                        <h2 class="item-title"><i class="ico icon-zuozhe"></i><?php echo $one_index_tj_user_toub_title ?></h2> 
                                        <div class="item-cont">
                                            <p><?php echo $one_index_tj_user_toub_desc ?></p> 
                                            <h4 class="lw-item-meta">
                                                <span class="meta-item meta-avatars">
                                                    <?php echo get_home_user() ?>      
                                                </span>
                                                <span class="meta-item meta-views">首推设计师</span></h4>
                                            <h3 class="count"><strong><?php echo $one_index_tj_user_toub_user ?></strong> <span>位作者加入</span></h3>
                                        </div> 
                                        <div class="item-btns">
                                           <a href="/write" class="listygo-btn listygo-btn-bj listygo-btn--style1 b2-radius">
                                                <span class="listygo-btn__icon">
                                                    <i class="jitheme ji-ball-pen-line"></i>
                                                </span>
                                                <span class="listygo-btn__text">我要投稿</span>
                                            </a>
                                            <a href="/ranks" class="listygo-btn listygo-btn-wbj listygo-btn--style1 b2-radius">
                                                <span class="listygo-btn__icon">
                                                    <i class="jitheme jitheme-huo"></i>
                                                </span>
                                                <span class="listygo-btn__text">查看榜单</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
        </div>
    </div>
</div>