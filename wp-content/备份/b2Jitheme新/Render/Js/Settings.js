//文章页面视频选择
var Pages = jQuery('#b2_Child').find(':selected').val();
if(Pages == true){
 jQuery('#b2_Child_waka_ok').show()
}else{
 jQuery('#b2_Child_waka_ok').hide()
}
jQuery('#b2_Child').on('change', function() {
 let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('#b2_Child_make_ok').show()
 
}else{
 jQuery('#b2_Child_make_ok').hide()
}
})