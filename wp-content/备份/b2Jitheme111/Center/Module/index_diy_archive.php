<?php
$index_fl_width=b2_get_option('Jitheme_index_tab4','index_fl_width');
if(empty($index_fl_width)){
    $width="jitheme_wrapper";
}else{
    $width="wrapper";
}
?>
<div class="home_row">
    <div id="Jitheme_diy_fla" class="<?php echo $width ?> ">
    <?php
        $diy_fl_zs=b2_get_option('Jitheme_index_tab4','diy_fl_zs');
        if(!empty($diy_fl_zs)){
            $diy_data = $diy_fl_zs;
        }else{
            $diy_data = 6;
        }
        $diy_fl_jj=b2_get_option('Jitheme_index_tab4','diy_fl_jj');
        if(!empty($diy_fl_jj)){
            $diy_jj_data = $diy_fl_jj;
        }else{
            $diy_jj_data = B2_GAP;
        }
        $djitheme_diy_flqh=b2_get_option('Jitheme_index_tab4','jitheme_diy_flqh');
        if($djitheme_diy_flqh){
          echo index_archive_qhaa();
        }else{
          echo index_archive_qha();
        }
    ?>
</div>
</div>
<script>
  var swiper1 = new Swiper("#swiper1", {
    slidesPerView: <?php echo $diy_data ?>,
    slidesPerColumn: 1,
    spaceBetween: <?php echo $diy_jj_data ?>,
    autoplay: {
        delay: 3500,//1秒切换一次
    },
    loop: true,
    navigation: {
      nextEl: ".sence-list-next",
      prevEl: ".sence-list-prev",
    },
    on: {
      init: function (swiper) {
        var _this = this;
        this.navigation.onNextClick = function () {
          _this.slideTo(_this.activeIndex + 4, 300, true);
        };
        this.navigation.onPrevClick = function () {
          if (_this.navigation.nextEl.getAttribute("aria-disabled") == "true") {
            _this.slideTo(_this.activeIndex - 3, 300, true);
          } else {
            _this.slideTo(_this.activeIndex - 4, 300, true);
          }
        };
      },
    },
  });
  </script>