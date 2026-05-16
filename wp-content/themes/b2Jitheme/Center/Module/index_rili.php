<div id="Jitheme_index_rili" class="wrapper ">
    <div class="home_row">
<?php 
// //农历
// $lunar=new Jitheme_lunar();
// $today = $lunar->convertSolarToLunar(date('Y'), date('m'), date('d'));

//阳历
$dataList = Jitheme_Rili::get_data_list();
$List = $dataList["rili_List"];
$fenjie = $dataList["fenjie"];
    $diy_fla_html='';
    $diy_fla_html .='<section id="jitheme_rili_swiper">
        <div class="calendar-title"><span class="title">
                热点日历
            </span>
            <div class="right box b2-radius">
            <div class="tian">
                <span class="day">
                    '.$fenjie['day'].'
                </span>
                <div class="time"><span>
                        '.$fenjie['year'].'.'.$fenjie['month'].'
                    </span> <span> '.$fenjie['xq'].'</span>
                </div></div>
                <div class="label">今天</div>
            </div>
        </div>
        <div id="sence-list-box">
            <div id="rili_swiper" class="swiper-container swiper-container-initialized swiper-container-horizontal">
                <div class="swiper-wrapper">';
                    $i=0;
                    foreach ($List as $v) { 
                        if ($i >= 18) {
                                break; // 跳出循环
                            }
                        $clsss = 'koo';
                        if($i < 4){
                            $clsss = 'hot';
                        }
                        $daytitle='';
                        $daysDiff = $v['xq'][0];
                        if ($daysDiff < 0) {
                            continue; // 执行下一条循环
                        }elseif ($daysDiff == 0) {
                            $daytitle ='<div class="label">今天</div>';                                    
                        }else{
                            $daytitle ='<div class="festival-time festival-nottoday '.$clsss.'">
                                        <div class="festival-time-day">'.$daysDiff.'</div>
                                        <div class="festival-time-ps">天后</div>
                                    </div>'; 
                        }
                        $diy_fla_html.='<div class="swiper-slide box  b2-radius">
                            <a href="'.$v['link'].'" target="_blank" class="b2-radius">
                                <div class="swiper-slide-text">
                                    <div class="home-sence-name">
                                        <h6>'.$v['name'].'</h6>
                                        <span>'.$v['date'].' '.$v['xq'][1].'</span>
                                    </div>
                                    '.$daytitle.'
                                </div>
                                <div class="sence-diagram-image">
                                    '.b2_get_img(array(
                                        'class'=>array('sort-config-icon'),
                                        'src'=>$v['image'],
                                    )).'
                                </div>
                            </a>
                        </div>';
                        $i++;
                    }
                $diy_fla_html.='</div>
                </div>
                <div class="swiper-button-next rili-list-next">
                    <span class="jitheme_swiper_jt jitheme_swiper_jt_r">
                        <div><span class="b2font b2-arrow-right-s-line"></span></div>
                    </span>
                </div>
                <div class="swiper-button-prev rili-list-prev">
                    <span class="jitheme_swiper_jt jitheme_swiper_jt_l">
                        <div><span class="b2font b2-arrow-left-s-line"></span></div>
                    </span>
                </div>
            </div>
    </section>';
    echo  $diy_fla_html;
   ?>
</div></div>
<script>
    var swiper1 = new Swiper("#rili_swiper", {
    slidesPerView: 5,
    slidesPerColumn: 1,
    spaceBetween:16,
    // autoplay: {
    //     delay: 3500,//1秒切换一次
    // },
    navigation: {
      nextEl: ".rili-list-next",
      prevEl: ".rili-list-prev",
      disabledClass: 'my-button-disabled',
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