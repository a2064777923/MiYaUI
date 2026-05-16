<?php
/**
 * Template Name: 用户展示
 */
?>
<?php
$count = 4; //展示用户数量
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$offset = ($paged -1)*$count;
$users = new WP_User_Query( array(
    'search_columns' => array(
        'display_name',
        'user_id'
    ),
    'number'=>$count,
    'offset'=>$offset
) );
$users_found = $users->get_results();
$total = $users->get_total();
$pages = ceil($total/$count);
?>
<?php $users = get_random_users(); ?>
<?php if ( ! empty( $users ) ) : ?>
    <?php do_action('b2_normal_archive_before'); ?>
    <div class="mi-users wrapper">
        <div class="mi-user-title"><? echo get_option('Micnt_Modular_Index')['Micnt_user_title'] ?></div>
        <div class="archive-row">
            <div id="user-list" class="mi-user">
                <?php $ids = array(); if($users_found){
                    echo '<div class="hidden-line"><ul class="user-search-list" ref="searchUser">';
                    foreach ($users_found as $user) {
                        $user_data = B2\Modules\Common\User::get_user_public_data($user->ID);
                        $user_lv = B2\Modules\Common\User::get_user_lv($user->ID);
                        $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                        $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
                        //$tips = B2\Modules\Common\Comment::get_tips();
                        //$tips = $tips['title'] ? $tips['title'] : __('这家伙很懒，什么都没留下','b2');
                        $following = get_user_meta($user->ID,'zrz_follow',true);
                        $following = is_array($following) ? count($following) : 0;
                        $followers = get_user_meta($user->ID,'zrz_followed',true);
                        $followers = is_array($followers) ? count($followers) : 0;
                        $ids[] = $user->ID;
                        // 获取用户最近发布的两篇文章
                        $args = array(
                            'author' => $user->ID,
                            'post_type' => 'post',
                            'posts_per_page' => 2, //展示用户文章数量
                            'orderby' => 'date',
                            'order' => 'DESC'
                        );
                        $latest_posts = new WP_Query($args);
                        // 获取用户最近发布的评论
                        $args = array(
                            'user_id' => $user->ID,
                            'number' => 6, //展示用户评论文章数量
                            'status' => 'approve',
                            'type' => 'comment',
                            'orderby' => 'comment_date',
                            'order' => 'DESC',
                        );
                        $latest_comments = get_comments($args);
                        $background_image = get_random_background_image();
                        //如需调用B2用户中心背景图，请将下面的$background_image修改成$user_data['cover']
                        echo '<li>
                            <div style="background-image: url('.$background_image.'" class="mi-userbg">
                                <div class="user-s-follow">
                                    <button class="author-has-follow" v-if="follow['.$user->ID.'] === true" v-cloak @click="followAc('.$user->ID.')">'.__('已关注','b2').'</button>
                                    <button class="empty" v-else v-cloak @click="followAc('.$user->ID.')">'.__('关注','b2').'</button>
                                    <button @click="dmsg('.$user->ID.')">'.__('私信','b2').'</button>
                                </div>
                                <div class="user-s-info">
                                <a href="'.$user_data['link'].'" class="link-block">
                                    <div class="user-s-info-avatar avatar-parent">
                                        <img src="'.$user_data['avatar'].'" class="avatar b2-radius" />
                                        '.($user_data['user_title'] ? $user_data['verify_icon'] : '').'
                                    </div>
                                    <div class="user-s-info-name">
                                        <h2>'.$user_data['name'].'</h2>
                                        <p>'.$user_vip.$user_lv.'</p>
                                    </div>
                                </a>
                                </div>
                                <div class="user-s-data">
                                    <div class="">
                                        <span>文章</span>
                                        <p>'.count_user_posts($user->ID,'post').'</p>
                                    </div>
                                    <div class="">
                                        <span>评论</span>
                                        <p>'.B2\Modules\Common\Comment::get_user_comment_count($user->ID).'</p>
                                    </div>
                                    <div class="">
                                        <span>粉丝</span>
                                        <p>'.$followers.'</p>
                                    </div>
                                    <div class="">
                                        <span>关注</span>
                                        <p>'.$following.'</p>
                                    </div>
                                </div>
                                <div class="user-s-info-desc">
                                
                                <div class="user-s-latest latest-posts">
                                <h3 class="latest-posts-title">TA的动态</h3>';
                                    if ($latest_posts->have_posts()) {
                                        while ($latest_posts->have_posts()) {
                                            $latest_posts->the_post();
                                            
                                            $post_thumbnail = get_the_post_thumbnail( $post->ID, 'thumbnail' );
                                            if (!$post_thumbnail) {
                                                $post_thumbnail = '<img src="/wp-content/themes/b2micnt/images/nopic.png" class="mi-user-wis">';
                                            }
                                            $post_categories = get_the_category();
                                            echo '<div class="user-s-latest-post">
                                                <a href="'.get_permalink().'">'.$post_thumbnail.'<h3 class="mi-item-title">'.get_the_title().'</h3></a>
                                                <div class="mi-itemss">';
                                                    foreach($post_categories as $category) {
                                                        echo '<h4 class="mi-item-meta"><a href="'.get_category_link($category->term_id).'">'.$category->name.'</a></h4>';
                                                    }
                                                echo '</div>
                                            </div>';
                                        }
                                    } elseif ($latest_comments) {
                                        echo '<div class="user-s-latest-comment">';
                                        foreach ($latest_comments as $comment) {
                                        $comment_post_id = $comment->comment_post_ID;
                                        $comment_mi = get_comment_link($comment->comment_ID);
                                        echo '<div class="user-s-latest-comments"><a href="'.$comment_mi.'">[评论]  '.get_the_title($comment_post_id).'</a></div>';
                                        }
                                        echo '</div>';
                                    }else {
                                        echo '<div class="user-s-latest-none">'.__('暂无动态','b2').'</div>';
                                    }
                                echo '
                            </div>
                                </div>
                            </div>
                        </li>';
                    }
                    echo '</ul></div>';  
                ?>
                
                <?php }else{
                    echo B2_EMPTY;
                } 
                unset($users_found);
                unset($users);
                wp_localize_script( 'b2-js-main', 'b2_search_data', array(
                    'users'=>$ids
                ))
                ?>
            </div>
            <?php do_action('b2_normal_archive_after'); ?>
        </div>
    </div>
<?php endif; ?>