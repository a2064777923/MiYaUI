<!-- 分类展示模块开始 -->   
<div id="home-row-fenlei"> 
    <div class="home-row-left content-area ">
            <?php   
                $catID =b2_get_option('Jitheme_index_tab4','one_index_fenlei_fl');
                $cat_title =b2_get_option('Jitheme_index_tab4','one_index_fenlei_title');
                $cat_desc =b2_get_option('Jitheme_index_tab4','one_index_fenlei_desc');
                $pieces = explode(",", $catID);
                $catID_ID = [$pieces[0],$pieces[1],$pieces[2],$pieces[3]];  //调用用户ID
                $xcatID =b2_get_option('Jitheme_index_tab4','one_index_fenlei_xfl');
                $piecesa = explode(",", $xcatID);
                $xcatID_ID = [$piecesa[0],$piecesa[1],$piecesa[2],$piecesa[3],$piecesa[4],$piecesa[5]];  //调用用户ID
            echo '<section class="puxin-widget-catGrid">
                <div class="title_puxin_center">
                    <div>'.$cat_title.'</div>
                    <div>'.$cat_desc.'</div>
                </div>
                <div class="topCat row">';
                    foreach ($catID_ID as $user) {
                        $title = get_cat_name($user);
                        $link = get_category_link($user);
                        $desc = category_description($user);
                        $img = get_term_meta($user, 'b2_tax_img', true);
                    echo '
                    <div class="col-px-1a">
                        <a class="item b2-radius" style="max-height: 450px;" href="'.$link.'" target="_blank">
                            <div class="item-bg">
                            '.b2_get_img(array(
                            	'src'=>$img,
                            )).'
                            </div>
                            <div class="item-warp">
                                <div class="datutext">'.$title.' </div>
                                <p>'.$desc.'</p>
                                '.jitheme_get_icon('Jifont-Jifont-2').'
                            </div>
                        </a>
                    </div>';
                    }
                echo '</div>
                <div class="bottomcat">
                        <div class="row" style="margin-top: 0px;">';
                        echo '
                            <div class="col-px-2">
                                <a class="small-item b2-radius" href="'.get_category_link($xcatID_ID[0]).'" target="_blank">
                                    <div class="title">'.get_cat_name($xcatID_ID[0]).'</div>
                                    <div class="item-bg">
                                        '.b2_get_img(array(
                                        	'src'=>get_term_meta($xcatID_ID[0], 'b2_tax_img', true),
                                        )).'
                                    </div>
                                    <div class="item-warp">
                                        <div class="datutext">'.get_cat_name($xcatID_ID[0]).'</div>
                                        <i class=""></i>
                                    </div>
                                </a>
                                <a class="small-item b2-radius" href="'.get_category_link($xcatID_ID[1]).'" target="_blank">
                                    <div class="title">'.get_cat_name($xcatID_ID[1]).'</div>
                                    <div class="item-bg">
                                        '.b2_get_img(array(
                                        	'src'=>get_term_meta($xcatID_ID[1], 'b2_tax_img', true),
                                        )).'
                                    </div>
                                    <div class="item-warp">
                                        <div class="datutext">'.get_cat_name($xcatID_ID[1]).'</div>
                                        <i class=""></i>
                                    </div>
                                </a>
                            </div>
                            <div class="col-px-1">
                                <a class="big-item b2-radius" href="'.get_category_link($xcatID_ID[2]).'" target="_blank">
                                    <div class="item-bg">
                                        '.b2_get_img(array(
                                        	'src'=>get_term_meta($xcatID_ID[2], 'b2_tax_img', true),
                                        )).'
                                    </div>
                                    <div class="item-warp">
                                        <div class="datutext">'.get_cat_name($xcatID_ID[2]).'</div>
                                        <div class="desc">'.category_description($xcatID_ID[2]).'</div>
                                    </div>
                                    <i class=""></i>
                                </a>
                            </div>
                            <div class="col-px-1">
                                <a class="big-item b2-radius" href="'.get_category_link($xcatID_ID[3]).'" target="_blank">
                                    <div class="item-bg">
                                        '.b2_get_img(array(
                                        	'src'=>get_term_meta($xcatID_ID[3], 'b2_tax_img', true),
                                        )).'
                                    </div>
                                    <div class="item-warp">
                                        <div class="datutext">'.get_cat_name($xcatID_ID[3]).'</div>
                                        <div class="desc">'.category_description($xcatID_ID[3]).'</div>
                                    </div>
                                    <i class=""></i>
                                </a>
                            </div>
                            <div class="col-px-2">
                                <a class="small-item b2-radius" href="'.get_category_link($xcatID_ID[4]).'" target="_blank">
                                    <div class="title">'.get_cat_name($xcatID_ID[4]).'</div>
                                    <div class="item-bg">
                                        '.b2_get_img(array(
                                        	'src'=>get_term_meta($xcatID_ID[4], 'b2_tax_img', true),
                                        )).'
                                    </div>
                                    <div class="item-warp">
                                        <div class="datutext">'.get_cat_name($xcatID_ID[4]).'</div>
                                       <i class=""></i>
                                    </div>
                                </a>
                                <a class="small-item b2-radius" href="'.get_category_link($xcatID_ID[5]).'" target="_blank">
                                    <div class="title">'.get_cat_name($xcatID_ID[5]).'</div>
                                    <div class="item-bg">
                                        '.b2_get_img(array(
                                        	'src'=>get_term_meta($xcatID_ID[5], 'b2_tax_img', true),
                                        )).'
                                    </div>
                                    <div class="item-warp">
                                        <div class="datutext">'.get_cat_name($xcatID_ID[5]).'</div>
                                        <i class=""></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
            </section>';
            ?>
        </div>
</div>
<!-- 分类展示模块结束 -->   