<?php
use B2\Modules\Templates\Collection;
use B2\Modules\Common\Post;
/**
 * 专题聚合页面
 */
get_header();

$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;
$pageda=50;
$data = Collection::get_collection_list(array('paged'=>$paged));
$_pages = $data['pages'];
$data = $data['data'];

$img = b2_get_option('col_main','collection_image');

$name = b2_get_option('col_main','collection_title');


$desc = b2_get_option('col_main','collection_desc');
$order = b2_get_option('col_main','collection_post_order');
$order = !empty($order) ? $order : 'asc';
$cats_tj = b2_get_option('col_main','collection_off');

?>
<?php if($cats_tj) { ?>
<div id="ji_coll">
<div class="ji-colltione-header">
    <div class="bg"><i class="thumb " style="background-image:url(<?php echo $img; ?>)"></i></div>
    <!--<div class="ji_jb_bg" style="background-image:url(<?php echo $img; ?>);"></div>-->
    <div class="wrapper">
        <div class="cont">
            <h2 class="archive-title"><?php echo $name; ?></h2>
            <h3 class="archive-subtitle"><?php echo $desc; ?></h3>
        </div>
    </div>
</div>
</div>
<?php }else{ ?>
    <div class="pianli">
        <h1><?php echo $name; ?></h1>
        <p><?php echo $desc; ?></p>
    </div>
<?php } ?>

<div id="primary-home" class="wrapper">
    <main class="site-main">
        <?php if($cats_tj){ ?>
        <div class="ji_zt ji-zt-soft b2-radius">    
            <ul class="ji-zt-config"> 
            <?php
            $cats = b2_get_option('col_main','collection');
            if(!empty($cats)){
                foreach ($cats as $k => $v) {
                    $t = get_term_by('id', $v, 'collection');
                    if(is_wp_error( $t ) || !isset($t->name)) continue;
                    $img = get_term_meta($v,'b2_tax_img',true);
                    $thumb = b2_get_img(array('class'=>array('sort-config-icon b2-radius'),'src'=>$img,))
                    ?>
                    
                    <li id="ji_coll_ls">
                        <div class="ji_coll_ls">
                            <div class="collection-zt-item jitheme-radius"><?php 
                                 echo collection_ico($v,$k);
                            ?> 
                            </div>
                            <a href="<?php echo get_term_link((int)$v); ?>" target="_blank">
                                <p class="zt-title"><?php echo $t->name; ?></p>
                                <span class="sort-config-desc"><?php echo $t->count; ?>篇文章</span>
                            </a>
                        </div>
                    </li>
                <?php } 
                
                }else{
                    ?>
                    <li id="ji_coll_ls">
                        <div class="ji_coll_ls">
                            <div class="collection-zt-item jitheme-radius"><span class="collection-zt-item jitheme_jb_6"><i class="jitheme ji-add-line"></i></span> 
                            </div>
                            <a href="/wp-admin/admin.php?page=b2_col_main" target="_blank">
                                <p class="zt-title  red">提示：后台专题列表未设置</p>
                                <span class="sort-config-desc red">点我去《极主题设置-专题页设置》进行设置</span>
                            </a>
                        </div>
                    </li>
                <?php 
                }?>
            </ul> 
            <div class="jitheme-zt-m"><input type="button" value="" id="zt_moves" class="zt_btn_img">
                <div id="zt_moves_hot" class="jitheme-zt-more b2-radius hidden">
                    <div class="zt-menus">
                        <?php 
                            $term_query = new WP_Term_Query( array( 
                                'taxonomy' => 'collection' ,
                                'field' => 'term_id',
                                ) );
                            $coll_move='';
                            if ( ! empty( $term_query->terms ) ) {
                                foreach ( $term_query ->terms as $term ) {
                                $collection_name = b2_get_option('normal_custom','custom_collection_name');
                                $qishu = get_term_meta($term->term_id,'b2_tax_index',true);
                                $coll_move .='<div class="sub-item '.$term->term_id .'">
                                                <a href="'.get_category_link($term->term_id).'" target="_blank">
                                                <div class="zt-move-thumb">
                                                '.collection_ico($term->term_id,1).'
                                                </div>
                                                <div>
                                                    <div class="tit">'.$term->name.'</div>
                                                    <div class="desc"><span>第'.$qishu.'期</span><span>'.$term->count.__('篇文章','b2').'</span></div> 
                                                </div>
                                                </a>
                                            </div>';
                                }
                            }
                        ?>
                        <?php echo $coll_move; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php }else{ 
        $pading='style="margin-top: 20px;"';
        }
        ?>
        <div class="jitheme-zt home-authors <?php echo empty($data) ? 'box' : ''; ?>">
            <div class="author-items">
            <?php 
                if(!empty($data)){
                foreach ($data as $k => $v) {
                $post_data = $v['posts']['data'];
                $q = get_term_meta($v['id'],'b2_tax_index',true);
                $collection_name = b2_get_option('normal_custom','custom_collection_name');
            ?>
                <div class="item item-author">
                        <div class="item-wrapa b2-radius">
                            <a class="item-thumb a_mask_light" href="<?php echo $v['link']; ?>" target="_blank">
                                <?php echo jithem_jianbian() ?>
                                <div class="thumb"> <?php echo b2_get_img(array(
                                        'src'=>$v['thumb'],
                                        'alt'=>$v['name']
                                    ));?></div>
                                <i class="thumb-tag">专题</i>
                            </a>
                            <div class="item-main">
                                <h3 class="item-title">
                                    <a class="title-a" href="<?php echo $v['link']; ?>" target="_blank"><?php echo $v['name'];?></a>
                                </h3>
                                <h4 class="item-desc">
                                <i class="thumb-views"><?php echo sprintf(__('%s：第%s%s%s期','b2'),$collection_name,'<b>',$q,'</b>'); ?></i>
                                <i class="thumb-count">
                                <?php
                                       $latest = false; 
                                       if( !empty($post_data) ) {
                                           $latest = ( $order == 'asc') ? end($post_data) : $post_data[0];
                                       }
                                       echo $latest ? Post::time_ago($latest['date']).__('更新','b2').' · ' : '';
                                       echo $v['posts']['count'].__('篇文章','b2');
                                    ?></i>
                                </h4>
                                <?php echo jitheme_zt($post_data,$v,$v['desc']) ?>
                                <div class="item-btns">
                                    <a class="jitheme-jb-btn" href="<?php echo $v['link']; ?>" target="_blank">查看专题</a>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php }
                }else{
                    echo B2_EMPTY;
                }
            ?>
            
            </div>
        </div>
        <?php 
            $pagenav = b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); 
            if($pagenav && !empty($data)){
                echo '<div class="b2-pagenav collection-nav post-nav box b2-radius mg-t">'.$pagenav.'</div>';
            }
        ?>
    </main>
</div>
<script>
// 获取元素
var btn = document.getElementById('zt_moves');
var box = document.getElementById('zt_moves_hot');
var isShow = false; // 默认div显示
// 给按钮注册事件
btn.onclick = function () {
    if (isShow) {
        box.className = 'jitheme-zt-more hidden';
        btn.className = 'zt_btn_img'
        // btn.value = '显示';
        //this.value = '...';
        isShow = false;
    } else {
        box.className = 'jitheme-zt-more show';
        btn.className = 'zt_btn_hot_img'
        // btn.value = '隐藏';
        //this.value = '...';
        isShow = true;
    }
}
</script>
<?php
get_footer();