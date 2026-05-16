<?php
$jithemeGg = new JithemeGg();
$Ji_Gg_index_list_01 = $jithemeGg->Ji_Gg_index_list_01(7242, 2);
$Ji_Gg_index_list_02 = $jithemeGg->Ji_Gg_index_list_02(7243, 8);

?>
<div class="jitheme_index_gg box b2-radius">
    <div class="card-head d-flex">
        <div class="text-sms">
            </i>广告展示(测试版)
        </div>
        <a href="#" target="_blank" class="btn vc-yellow" data-modal_type="overflow-hidden">
            投放规则</a>
    </div>
    <div class="row gutters-5">
        <?php echo $Ji_Gg_index_list_01; ?>
    </div>
    <div class="card-body row-xs">
        <?php echo $Ji_Gg_index_list_02; ?>
    </div>
</div>
