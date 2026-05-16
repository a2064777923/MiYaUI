<div id="Jitheme_diy_flb" class="home_row">
    <div class="wrapper">
        <?php 
          $jitheme_padding=b2_get_option('Jitheme_main_tab1','jitheme_padding');
          $html_data=b2_get_option('Jitheme_index_tab8','i7zimg_zs');
              if(!empty($html_data)){
                  $list_data = $html_data;
                  $jianju = substr_replace($jitheme_padding,"",-2,2);
              }else{
                  $list_data = 4;
              }
          echo index_archive_qhb();
        ?>
    </div>
</div>
<script>
    var swiper2 = new Swiper("#swiper2", {
    slidesPerView: <?php echo $list_data ?>,
    spaceBetween: <?php echo $jianju ?>,
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