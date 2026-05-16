<?php 
    $home_search_1_title =b2_get_option('jitheme_home_search','home_search_1_title');
    $home_search_2_title =b2_get_option('jitheme_home_search','home_search_2_title');
    $home_search_3_title =b2_get_option('jitheme_home_search','home_search_3_title');
    $home_search_desc =b2_get_option('jitheme_home_search','home_search_desc');
    $home_search_text =b2_get_option('jitheme_home_search','home_search_text');
    $home_search_btn =b2_get_option('jitheme_home_search','home_search_btn');
    $home_search_btn_link =b2_get_option('jitheme_home_search','home_search_btn_link');
    $home_search_img =b2_get_option('jitheme_home_search','home_search_img');
    $Search_key =b2_get_option('jitheme_home_search','home_search_key');
    $search = new B2\Modules\Templates\Modules\Search();
    $key = $search->str_to_array($Search_key);
    if($Search_key){
        $_key='';
        foreach ($key as $k => $v) {
            $v = trim($v, " \t\n\r\0\x0B\xC2\xA0");
            $_key .='<a href="'.B2_HOME_URI.'/?s='.$v.'" class="tag-cloud-link">'.$v.'</a>';
        }
    } 
?>
<div id="Jitheme-home-search" class="mini-home-search" style="background-image: url(<?php echo $home_search_img; ?>);">
    <div class="mini-banner-mask"></div>
    <div class="mini-text-center">
        <div class="slide1-title">
            <p class="big_title slide_text"><?php echo $home_search_1_title; ?></p>
            <script type="text/javascript">
                var jump_pram = "eyJyb3V0ZV9pZCI6IjE2MDUyNjA0OTU0MzA5Iiwicm91dGUiOiIzLDEsIiwiYWZ0ZXJfcm91dGUiOiIzLDEiLCJyZWZlcmVyIjoiJTJGJTNGcm91dGVfaWQlM0QxNjA1MjYwNDk1NDMwOSUyNnJvdXRlJTNEMyUyQzElMkMlMjZhZnRlcl9yb3V0ZSUzRDMlMkMxIn0=";
                var isGuest = '1';
                var is_login = "";
                var phoneBind = "";
                var origin = "index_recommend";
                var index_player = "";
                var invite_user = "";
                var invite_uid = "";
                var is_new_user = "";
                var templ_count = "792,291";
                var isClientSide = "";
                var templ_num = "792,291";
                setInterval(function() {
                    if ($('.slide1-title .big_title').text() == "<?php echo $home_search_1_title; ?>") {
                         $('.slide1-title .big_title').text('<?php echo $home_search_2_title; ?>')
                    } else if ($('.slide1-title .big_title').text() == "<?php echo $home_search_2_title; ?>") {
                        $('.slide1-title .big_title').text('<?php echo $home_search_3_title; ?>')
                    } else {
                        $('.slide1-title .big_title').text('<?php echo $home_search_1_title; ?>')
                    }
                }, 2000);
            </script>
        </div>
        <div class="slide1-desc"><?php echo $home_search_desc; ?></div>
        <div class="slidebox">
    	    <div class="search">
    			<form method="get" class="mini-form mini-overflow-hidden mini-position-relative" action="<?php bloginfo('url'); ?>">
    				<input type="search" placeholder="<?php echo $home_search_text; ?>" autocomplete="off" value="" name="s" required="required" class="mini-input">
    				<button type="submit"><i class="b2font b2-search-line "></i>搜索</button>
    			</form>
                <div class="sean mini-visible">
    			    <span>或</span>
    				<a class="b2-radius" href="<?php echo $home_search_btn_link; ?>"><p class="mini-text-truncate"><?php echo $home_search_btn; ?></p></a>
    			</div>
    		</div>
            <ul class="tag mini-visible">
                <li class="k1"><?php echo jitheme_get_icon('Jifont-fire') ?>热门搜索：</li>
                <?php echo $_key ?>
            </ul>
    	</div>
    </div>
</div>
<style>

    
    
</style>