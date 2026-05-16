<?php 
    $html_data=b2_get_option('Jitheme_index_tab8','i7zimg_zs');
    $is_mobile = wp_is_mobile();
    if(!$is_mobile){
        if(!empty($html_data)){
          $list_data = $html_data;
        }else{
          $list_data = 4;
        }
    }else{
        $list_data = 2;
    }
  echo index_archive_qhb();
?>
<script>
    var swiper2 = new Swiper("#swiper2", {
    slidesPerView: <?php echo $list_data ?>,
    spaceBetween:16,
    loop: true,
    autoplay: {
        delay: 3500,//1秒切换一次
    },
    navigation: {
      nextEl: ".jitheme_next",
      prevEl: ".jitheme_prev",
    },
  });
</script>