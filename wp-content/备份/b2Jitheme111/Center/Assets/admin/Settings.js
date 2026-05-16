
//侧边栏
var Child_Modular_User_page = jQuery('#b2_Jitheme_footer_celan').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('#onecad_footer_celan').show()
}else{
 jQuery('#onecad_footer_celan').hide()
}
jQuery('#b2_Jitheme_footer_celan').on('change', function() {
 let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('#onecad_footer_celan').show()
 
}else{
 jQuery('#onecad_footer_celan').hide()
}
})
//网址导航搜索
var Child_Modular_User_page = jQuery('#links_search_off').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('#links_search').show()
}else{
 jQuery('#links_search').hide()
}
jQuery('#links_search_off').on('change', function() {
 let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('#links_search').show()
 
}else{
 jQuery('#links_search').hide()
}
})
//网址导航推荐
var Child_Modular_User_page = jQuery('#index_links_tj').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('#index_links_tj_title').show()
}else{
 jQuery('#index_links_tj_title').hide()
}
jQuery('#index_links_tj').on('change', function() {
 let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('#index_links_tj_title').show()
 
}else{
 jQuery('#index_links_tj_title').hide()
}
})
// 搜索模块/侧菜单栏
var Child_Modular_User_page = jQuery('#index_onecad_searchoff').find(':selected').val();
if(Child_Modular_User_page == 0){
    jQuery('#index_onecad_search_ss').show()
    jQuery('#index_onecad_search_cd').hide()
}if(Child_Modular_User_page == 1){
    jQuery('#index_onecad_search_ss').hide()
    jQuery('#index_onecad_search_cd').show()
}if(Child_Modular_User_page == 2){
    jQuery('#index_onecad_search_ss').hide()
    jQuery('#index_onecad_search_cd').hide()
}
jQuery('#index_onecad_searchoff').on('change', function() {
let val = jQuery(this).find(':selected').val();
 if(val == 0){
 jQuery('#index_onecad_search_ss').show()
 jQuery('#index_onecad_search_cd').hide()
 
} if(val == 1){
 jQuery('#index_onecad_search_ss').hide()
 jQuery('#index_onecad_search_cd').show()
 
} if(val == 2){
 jQuery('#index_onecad_search_ss').hide()
 jQuery('#index_onecad_search_cd').hide()
}
})
//网址导航推荐
var Child_Modular_User_page = jQuery('#index_onecad_search_header_off').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('#index_onecad_search_daoh').show()
}else{
 jQuery('#index_onecad_search_daoh').hide()
}
jQuery('#index_onecad_search_header_off').on('change', function() {
 let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('#index_onecad_search_daoh').show()
 
}else{
 jQuery('#index_onecad_search_daoh').hide()
}
})
//区块用户组及广告组
var Child_Modular_User_page = jQuery('#index_qukuai_gg_off').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('#qukuai_zu').show()

}else{
 jQuery('#qukuai_zu').hide()
}
jQuery('#index_qukuai_gg_off').on('change', function() {
 let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('#qukuai_zu').show()

 
}else{
 jQuery('#qukuai_zu').hide()
}
})
//区块VIP组与活动切换
var Child_Modular_User_page = jQuery('#qukuai_hd_off').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('#qukuai_vip').show()
 jQuery('#qukuai_hd').hide()
}else{
 jQuery('#qukuai_vip').hide()
 jQuery('#qukuai_hd').show()
}
jQuery('#qukuai_hd_off').on('change', function() {
let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('#qukuai_vip').show()
 jQuery('#qukuai_hd').hide()
 
}else{
 jQuery('#qukuai_vip').hide()
 jQuery('#qukuai_hd').show()
}
})
//分类图标选择
var Child_Modular_User_page = jQuery('#jitheme_archive_off').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('.cmb2-id-jitheme-tax-img').show()
 jQuery('.cmb2-id-ico-name').hide()
 jQuery('.cmb2-id-ico-font-family').hide()
}else{
 jQuery('.cmb2-id-jitheme-tax-img').hide()
 jQuery('.cmb2-id-ico-name').show()
 jQuery('.cmb2-id-ico-font-family').show()
}
jQuery('#jitheme_archive_off').on('change', function() {
let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('.cmb2-id-jitheme-tax-img').show()
 jQuery('.cmb2-id-ico-name').hide()
 jQuery('.cmb2-id-ico-font-family').hide() 
}else{
 jQuery('.cmb2-id-jitheme-tax-img').hide()
 jQuery('.cmb2-id-ico-name').show()
 jQuery('.cmb2-id-ico-font-family').show()
}
})

//自定义分类切换
var Child_Modular_User_page = jQuery('#jitheme_diy_flqh').find(':selected').val();
if(Child_Modular_User_page == true){
 jQuery('.cmb2-id-jitheme-diy-flqh-id').show()
 jQuery('.cmb2-id-diy-fl-zs-list').hide()
}else{
 jQuery('.cmb2-id-jitheme-diy-flqh-id').hide()
 jQuery('.cmb2-id-diy-fl-zs-list').show()
}
jQuery('#jitheme_diy_flqh').on('change', function() {
let val = jQuery(this).find(':selected').val();
 if(val == true){
 jQuery('.cmb2-id-jitheme-diy-flqh-id').show()
 jQuery('.cmb2-id-diy-fl-zs-list').hide()
 
}else{
 jQuery('.cmb2-id-jitheme-diy-flqh-id').hide()
 jQuery('.cmb2-id-diy-fl-zs-list').show()
}
})
//首页后台切换
function changeRaido(){
	jQuery('.model-picked').each(function(index,el){
		var picked = jQuery(this).find('input[type="radio"]:checked').val();

		jQuery(this).parents('.cmb-repeatable-grouping').find('#jitheme_module_type').hide();

		if(picked){
			jQuery(this).parents('.cmb-repeatable-grouping').find('.'+picked+'-module').show();
		}
	})	
}
// var Child_Modular_User_page = jQuery('#Child_Modular_User_Switch').find(':selected').val();
// if(Child_Modular_User_page == true){
//  jQuery('#Child_Modular_User_title').show()
//  jQuery('#Child_Modular_User_desc').show()
//  jQuery('#Child_Modular_User_id').show()
//  jQuery('#Child_Modular_User_color').show()
 
// }else{
//  jQuery('#Child_Modular_User_title').hide()
//  jQuery('#Child_Modular_User_desc').hide()
//  jQuery('#Child_Modular_User_id').hide()
//  jQuery('#Child_Modular_User_color').hide()
// }

// jQuery('#Child_Modular_User_Switch').on('change', function() {
//  let val = jQuery(this).find(':selected').val();

//  if(val == true){
//  jQuery('#Child_Modular_User_title').show()
//  jQuery('#Child_Modular_User_desc').show()
//  jQuery('#Child_Modular_User_id').show()
//  jQuery('#Child_Modular_User_color').show()
 
// }else{
//  jQuery('#Child_Modular_User_title').hide()
//  jQuery('#Child_Modular_User_desc').hide()
//  jQuery('#Child_Modular_User_id').hide()
//  jQuery('#Child_Modular_User_color').hide()
// }
// })
/////////////////////////////////////////






// var Pages = jQuery('#Child_Modular_User_Switch').find(':selected').val();
// if(Pages == true){
//  jQuery('#Child_Modular_User_title').show()
// }else{
//  jQuery('#Child_Modular_User_title').hide()
// }
// // jQuery('#b2_Child').on('change', function() {
// //  let val = jQuery(this).find(':selected').val();
// //  if(val == true){
// //  jQuery('#Child_Modular_User_title').show()
 
// // }else{
// //  jQuery('#Child_Modular_User_title').hide()
// // }
// })