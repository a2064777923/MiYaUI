<?php 
$html='';
$Onecad_index_5zuimg_off=b2_get_option('Jitheme_index_tab7','Onecad_index_5zuimg_off');
$Onecad_index_5zuimg_list=b2_get_option('Jitheme_index_tab7','Onecad_index_5zuimg_list');
    if($Onecad_index_5zuimg_off){
        if(is_array($Onecad_index_5zuimg_list)){
            $html .='<div class="OneCAD_link newOneCAD_link">
                <dl>';
            foreach ($Onecad_index_5zuimg_list as $k => $v) {
                $html .='<dd class="xu_link"><a href="'.$v['Onecad_index_5zuimg_title_hz'].'"><i  style="background:url('.$v['Onecad_index_5zuimg_img'].') center no-repeat; background-size: 45px;"></i><p>'.$v['Onecad_index_5zuimg_title'].'</p></a></dd>';
            }   
        }
        $html .='</dl></div>';
    }
    echo $html;
?>
