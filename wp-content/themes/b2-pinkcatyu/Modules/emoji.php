<?php
function acn01(){
    $acn01=array(
"眞白花音_谢谢.gif",
"眞白花音_认真学习.gif",
"眞白花音_蛤.gif",
"眞白花音_菜.gif",
"眞白花音_笑脸.gif",
"眞白花音_白菜价.gif",
"眞白花音_生气.gif",
"眞白花音_现在是什么感觉啊.gif",
"眞白花音_晚安.gif",
"眞白花音_星星眼.gif",
"眞白花音_打call.gif",
"眞白花音_我是天才吧.gif",
"眞白花音_恭喜.gif",
"眞白花音_对对对.gif",
"眞白花音_对不起.gif",
"眞白花音_好耶.gif",
"眞白花音_大头.gif",
"眞白花音_好吃.gif",
"眞白花音_哭哭.gif",
"眞白花音_加油.gif",
"眞白花音_你好.gif",
"眞白花音_亲亲.gif",
"眞白花音_乾杯.gif",
"眞白花音_举拳攻击.gif",
"眞白花音_下次还敢.gif",
);
    return $acn01;
}
function acn02(){
    $acn02=array(
"枕边童话_晚安.gif",
"枕边童话_贴贴.gif",
"枕边童话_探头.gif",
"枕边童话_抱抱.gif",
"枕边童话_rua.gif",
"呜米_歪头.gif",
"呜米_敲打.gif",
"呜米_嘿嘿嘿.gif",
"呜米_哈哈哈.gif",
"呜米_果咩捏.gif",
"呜米_戳.gif",
"呜米_吃瓜.gif",
"呜米_比心.gif",
"呜米_ok.gif",
"咩栗_呜呜.gif",
"咩栗_委屈巴巴.gif",
"咩栗_歪脑.gif",
"咩栗_气气.gif",
"咩栗_戳.gif",
"咩栗_比心.gif",
"咩栗_拜拜.gif",
"咩栗_欸.gif",
"咩栗_啊这.gif",
"咩栗_小棉花.gif",
);
    return $acn02;
}
function acn03(){
    $acn03=array(
"嘉然-赞.gif",
"嘉然-一米八.gif",
"嘉然-想吃糖.gif",
"嘉然-吐口水.gif",
"嘉然-喵喵.gif",
"嘉然-卖萌.gif",
"嘉然-啦啦队.gif",
"嘉然-看铁锤.gif",
"嘉然-惊讶.gif",
"嘉然-剪刀手.gif",
"嘉然-嘉人们.gif",
"嘉然-番茄炒蛋拳.gif",
"嘉然-馋.gif",
"嘉然-绷不住了.gif",
"嘉然-安详.gif",
"嘉然-爱你哦.gif",
"嘉然-mua.gif",
"嘉然-emo.gif",
"嘉然-Ding.gif",
"嘉然-biu.gif",
);
    return $acn03;
}
function acn04(){
    $acn04=array(
"雫るる-爱.png", "雫るる-不想努力.png", "雫るる-馋馋.png", "雫るる-吃桃.png", "雫るる-盯.png", "雫るる-好耶.png", "雫るる-理解不能.png", "雫るる-理解理解.png", "雫るる-没救了.png", "雫るる-那没事了.png", "雫るる-清楚.png", "雫るる-生气.png", "雫るる-酸.png", "雫るる-贴贴.png", "雫るる-嘻嘻.png", "雫るる-喜欢.png", "雫るる-嚣张.png", "雫るる-震惊.png", "雫るる-指指点点.png", "雫るる-忠诚.png",
);
    return $acn04;
}
function emojidizhi(){ $dizhi='/wp-content/themes/b2-pinkcatyu/images/smilies/'; return $dizhi; }
function inlojv_custom_smilies($img_src, $img) {    return emojidizhi().$img;}
if ( !isset( $wpsmiliestrans ) ) {    $b1 =acn01();    $b2 =acn02();    $b3 =acn03();$b4 =acn04();
        $xk=array();$xv=array();
        foreach ($b1 as $k=>$v){
        $k='a'.$k;
        $xk[]=':'.$k.':';
        $xv[]='1/'.$v;
        }
        $xk2=array();
        $xv2=array();
        foreach ($b2 as $k=>$v){
        $k='b'.$k;
        $xk2[]=':'.$k.':';
        $xv2[]='2/'.$v;
        }
        $xk3=array();
        $xv3=array();
        foreach ($b3 as $k=>$v){
        $k='c'.$k;
        $xk3[]=':'.$k.':';
        $xv3[]='3/'.$v;
        }
        $xk4=array();
        $xv4=array();
        foreach ($b4 as $k=>$v){
        $k='d'.$k;
        $xk4[]=':'.$k.':';
        $xv4[]='4/'.$v;
        }
        $acn01=array_combine($xk,$xv);
        $acn02=array_combine($xk2,$xv2);
        $acn03=array_combine($xk3,$xv3);
        $acn04=array_combine($xk4,$xv4);
        $acnarr=array_merge_recursive($acn01, $acn02);
        $acnarr2=array_merge_recursive($acnarr,$acn03);
        $wpsmiliestrans=array_merge_recursive($acnarr2, $acn04);    }
function inlo_smilies(){
$b1 =acn01();
$b2 =acn02();
$b3 =acn03();
$b4 =acn04();
$emojidizhi=emojidizhi(); ?>
		<ul><li class="hit">眞白花音</li><li>枕边童话</li><li>嘉然</li><li>雫るる</li></ul>
<div class="panes">    
<div class="pane" style="display:block;">    
    <?php     foreach ($b1 as $k=>$v){$k='a'.$k;
    echo '<a class="bq-clos" href="javascript:mrxubq('."' :".$k.": '".')" ><img src="'.$emojidizhi.'1/'.$v.'"></a>';}?></div>
<div class="pane">    
    <?php     foreach ($b2 as $k=>$v){$k='b'.$k;
    echo '<a class="bq-clos" href="javascript:mrxubq('."' :".$k.": '".')" ><img src="'.$emojidizhi.'2/'.$v.'"></a>';}?></div>
<div class="pane">    
    <?php     foreach ($b3 as $k=>$v){$k='c'.$k;
    echo '<a class="bq-clos" href="javascript:mrxubq('."' :".$k.": '".')" ><img src="'.$emojidizhi.'3/'.$v.'"></a>';}?></div>
<div class="pane">    
    <?php     foreach ($b4 as $k=>$v){$k='d'.$k;
    echo '<a class="bq-clos" href="javascript:mrxubq('."' :".$k.": '".')" ><img src="'.$emojidizhi.'4/'.$v.'"></a>';}?>
</div>
</div><div class="bq-close"></div>
<?php }
add_filter( 'smilies_src', 'inlojv_custom_smilies', 10, 3 );