<?php
use B2\Modules\Common\Post;
/**
 * 文章内容页样式 post-style-6
 */
$post_id = get_the_id();
$b2_post_image_dzs= get_post_meta($post_id,'b2_post_image_dzs',true);
//获取post meta
$post_meta = Post::post_meta($post_id);

//获取缩略图
$thumb = Post::get_post_thumb($post_id);

//计算缩略图宽高
$w = b2_get_option('template_main','wrapper_width');
$w = preg_replace('/\D/s','',$w);

$thumb = b2_get_thumb(array(
    'thumb'=>$thumb,
    'width'=>$w,
    'height'=>'100%'
));

$excerpt = get_post_field('post_excerpt');
$from_url = get_post_meta($post_id,'b2_post_from_url',true);
if(strpos($from_url,'http://') === false && strpos($from_url,'https://') === false){
    $from_url = 'https://'.$from_url;
}
$from_name = get_post_meta($post_id,'b2_post_from_name',true);
$down_open = get_post_meta($post_id,'b2_open_download',true);
$Single_down =b2_get_option('Jitheme_post_main','Single_down');
?>

<article class="single-article b2-radius box">
<div class="post-breadcrumb b2-hover mg-b b2-radius">
    <?php echo jithem_post_breadcrumb($post_id); ?>
</div>
<?php do_action('b2_single_article_before'); ?>
<header   id="post-meta" rel="nofollow" class="entry-header post-style-6">
    <div  class="ava-ava" >
        <div class="ava-left">
            <?php echo b2_get_img(array(
                'src'=>$thumb,
                'class'=>array('b2-radius'),
            )); ?>
        </div>
        <div class="hh-title">
            <div class="onecad_title ">
                <div class="title"><?php echo get_the_title(); ?></div>
                <?php 
                    if($down_open != 0 && $Single_down != 0){
                        $style='display: none;';
                    }else{
                        $style='display: block;';
                    }
                ?>
                    <div id="sucai_hot" title="素材热度" class="hot_num_pos" style="color: var(--b2color); <?php echo $style ?>">
                        <i class="b2font b2-blaze-line "></i>
                        <span><b v-text="postData.views"></b></span>
                        °
                    </div>
            </div>
                <div class="post-meta-row">
                <ul class="post-meta" style="padding: 5px 0px;">
                    <?php 
                        if($from_url && $from_name){
                    ?>
                        <li class="single-from"><span><?php echo __('来源：','b2'); ?><a href="<?php echo $from_url; ?>" target="_blank" rel="nofollow"><?php echo $from_name; ?></a></span></li>
                    <?php
                        }
                    ?>
                    <li class="single-date">
                        <span><?php echo $post_meta['date']; ?></span>
                    </li>
                    <li class="single-like">
                        <span><?php echo b2_get_icon('b2-heart-fill'); ?><b v-text="postData.up"></b></span>
                    </li>
                    <li class="single-eye">
                        <span><?php echo b2_get_icon('b2-eye-fill'); ?><b v-text="postData.views"></b></span>
                    </li>
                </ul>
            </div>
                <div class="post-tags-meat-onecad " >
                    <?php echo B2\Modules\Templates\Modules\Posts::get_post_cats('target="__blank"',$post_meta,array('cats'),'post_3'); ?>
                    <?php 
                        $getFile = @filesize(get_attached_file(get_the_ID()));
                        if (!empty($getFile) && is_numeric($getFile)) {
                            echo size_format($getFile);
                        }
                    ?>
                    <div  class="sub-nav">
                        <?php the_tags( '<span class="tag-img b2-radius"><i class="b2font b2-price-tag-3-line "></i>', '</span><span class="tag-img b2-radius"><i class="b2font b2-price-tag-3-line "></i>', '</span>' ); ?>
                        <span class="tag-img b2-radius" v-cloak v-if="userData.is_admin">
                            <i class="b2font b2-edit-2-line"></i>
                            <a href="<?php echo get_edit_post_link($post_id); ?>" target="_blank"><?php echo __('编辑','b2'); ?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php if(!is_audit_mode()){ ?>
            <div class="post-user-info">
                <div class="post-meta-left">
                    <a class="link-block" href="<?php echo $post_meta['user_link']; ?>"></a>
                    <div class="avatar-parent"><img class="avatar b2-radius" src="<?php echo $post_meta['user_avatar']; ?>" /><?php echo $post_meta['user_title'] ? $post_meta['verify_icon'] : ''; ?></div>
                    <div class="post-user-name"><b><?php echo $post_meta['user_name']; ?></b><span class="user-title"><?php echo $post_meta['user_title']; ?></span></div>
                </div>
                <div class="post-meta-right">
                    <div class="" v-if="self == false" v-cloak>
                        <button @click="followingAc" class="author-has-follow" v-if="following"><?php echo __('取消关注','b2'); ?></button>
                        <button @click="followingAc" v-else><?php echo b2_get_icon('b2-add-line').__('关注','b2'); ?></button>
                        <button class="empty" @click="dmsg()"><?php echo __('私信','b2'); ?></button>
                    </div>
                </div>
                
            </div>
        <?php } ?>
    <?php echo post_top_gg() ?>
    <div class="post-6-jiehsao">
        <ul class="info6fwq sucai-jianjie">
            <li><i class="b2font b2-landscape-line"></i>
                <span>素材类型：</span>
                <b><?php echo Psotjiaobiao($post_id);?>-素材</b>
            </li>
            <li><i class="b2font b2-arrow-left-right-fill"></i>
                <span>上传时间：</span>
                <b><?php echo $post_meta['date']; ?></b>
            </li>
            <?php
                $html='';
                $duotu_list=get_post_meta( $post_id, 'Onecad_post_duotu_list', true );
                if($duotu_list !=''){
                $str = trim($duotu_list, "\t\n\r\0\x0B\xC2\xA0");
                $more=array(
                'more' => explode(PHP_EOL, $str),
            	); //调用用户ID
                    foreach ($more['more'] as $user) {
                            $array=explode('|', $user);
                    $html.='<li><i class="jitheme '.$array[0].'"></i>
                        <span>'.$array[1].'：</span>
                        <b>'.$array[2].'</b>
                    </li>';
                    }
                }
                echo  $html;
            ?>
        </ul>
    </div>
</header>
<div class="entry-content">
    
  <?php
// 获取文章内容


function jitheme_post_images_linst($post_id) {
    // 获取文章内容
    $content = get_post_field('post_content', $post_id);
    // 使用正则表达式匹配 img 标签
    preg_match_all('/<img[^>]+src=["\']?([^"\'>]+)["\']?[^>]*>/i', $content, $matches);
    // 提取图片地址
    $image_urls = $matches[1]; // 匹配到的地址在 $matches[1] 中
    // 判断是否匹配到图片
    $html =  '<div class="content">
      <div id="device" class="gridalicious">
        <div class="galcolumn">';
    if (empty($image_urls)) {
        $b2_post_image_dzs= get_post_meta($post_id,'b2_post_image_dzs',true);
        $count = 0;
        $image_urls = []; // 用于存储图片地址的数组
            // 输出图片URL到页面
            if ( !empty($b2_post_image_dzs) ) {
                foreach ( $b2_post_image_dzs as $image_url ) {
                    $html .=   '<figure class="item wp-block-image size-full"><img decoding="async" class=" lazy" src="'.esc_url( $image_url ).'" data-src="'.esc_url( $image_url ).'" alt="从白天舒适咖啡馆无缝过渡到夜晚热闹鸡尾酒吧的空间设计" style="cursor: zoom-in;"></figure>';
                }
            }
            
            
    }else{
        if ( !empty($image_urls) ) {
                foreach ( $image_urls as $image_url ) {
                    $html .=   '<figure class="item wp-block-image size-full"><img decoding="async" class=" lazy" src="'.esc_url( $image_url ).'" data-src="'.esc_url( $image_url ).'" alt="从白天舒适咖啡馆无缝过渡到夜晚热闹鸡尾酒吧的空间设计" style="cursor: zoom-in;"></figure>';
                }
            }
    }
    $html .=   '</div></div></div>';
    return $html;
}
// $content = get_post_field( 'post_content',$post_id);

// // 使用正则表达式提取文章中的所有图片
// preg_match_all('/<img[^>]+src=["\']?([^"\'>]+)["\']?[^>]*>/i', $content, $matches);

// // 获取所有图片的URL
// $image_urls = $matches[1]; // $matches[1] 存储的是图片的 URL
// echo '<div class="content">
//   <div id="device" class="gridalicious">
//     <div class="galcolumn">';
// // 输出图片URL到页面
// if ( !empty($image_urls) ) {
//     foreach ( $image_urls as $image_url ) {
//         echo '<figure class="itemwp-block-image size-full"><img decoding="async" class=" lazy" src="'.esc_url( $image_url ).'" data-src="'.esc_url( $image_url ).'" alt="从白天舒适咖啡馆无缝过渡到夜晚热闹鸡尾酒吧的空间设计" style="cursor: zoom-in;"></figure>';
//     }
// }
// echo '</div></div></div>';
?>

    <?php do_action('b2_single_post_content_before'); ?>
    <?php if($excerpt){ ?>
        <div class="content-excerpt">
            <?php echo get_the_excerpt(); ?>
        </div>
    <?php } ?>
    
    <!--<?php echo Swiper_onecad_huandengp($post_id,'b2_post_image_dzs'); ?>   -->
    <?php echo(jitheme_post_images_linst($post_id)); ?>
    <?php the_content(); ?>
    <?php echo post_dow_gg() ?>
    <?php
        global $page, $numpages, $multipage, $more;
        echo b2_pagenav(array('pages'=>$numpages,'paged'=>$page),true);
    ?>
    <?php do_action('b2_single_post_content_after'); ?>
</div>
<?php do_action('b2_single_article_after'); ?>

</article>


  <script>
    var swiper = new Swiper(".mySwiper", {
      loop: true,
      spaceBetween: 10,
      slidesPerView: 4,
      freeMode: true,
      watchSlidesProgress: true,
    });
    var swiper2 = new Swiper(".mySwiper2", {
      loop: true,
      spaceBetween: 10,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      thumbs: {
        swiper: swiper,
      },
    });
  </script>
  <script type="text/javascript">
			//模拟滚动条滚动时随机添加内容
            makeboxes = function() {
                var boxes = new Array;   
                var randTxt = ['CADSEE - www.cadsee.cn','璞信设计'];
                var amount = Math.floor(Math.random()*10); 
                for(i=1;i<amount;i++){
                    num = Math.floor(Math.random()*randTxt.length); 
                    div = $('<div></div>').addClass('item'); 
                    content = "<img src='images/"+i+".jpg'/><p>"+randTxt[num]+"</p>";
                    div.append(content);
                    boxes.push(div);
                }
                return boxes;
            }
			//滚动条事件
			$(document).ready(function () {         
				$(window).scroll(function () {
					if(($(window).scrollTop() + $(window).height()) == $(document).height())
					{
						$("#device").gridalicious('append', makeboxes());
					}
			});
			//主要部分
            $("#device").gridalicious({
                gutter: 20,
                width: 300,
                animate: true,
                animationOptions: {
                        speed: 150,
                        duration: 400,
                        complete:function(data){
							console.log("璞信设计 - www.cadsee.cn");	
						}
                },
            });
        });
    </script>