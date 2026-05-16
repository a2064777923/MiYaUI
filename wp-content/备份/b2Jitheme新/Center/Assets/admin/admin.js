//文章页面视频选择
var postStyle = jQuery('input[name=b2_single_post_style]:checked').val();
if(postStyle == 'post-style-7'){
 jQuery('#b2_post7_audio').show();
}else{
 jQuery('#b2_post7_audio').hide();
}
jQuery('input[name=b2_single_post_style]').on('change', function() {
  var val = jQuery(this).val();
  console.log(val);
  if(val == 'post-style-7'){
   jQuery('#b2_post7_audio').show();
  }else{
   jQuery('#b2_post7_audio').hide();
  }
})
if(postStyle == 'post-style-6'){
 jQuery('#b2_post_image_dz').show();
}else{
 jQuery('#b2_post_image_dz').hide();
}
jQuery('input[name=b2_single_post_style]').on('change', function() {
  var val = jQuery(this).val();
  console.log(val);
  if(val == 'post-style-6'){
   jQuery('#b2_post_image_dz').show();
  }else{
   jQuery('#b2_post_image_dz').hide();
  }
})


var catfliter =document.querySelectorAll('#jj_new_links_repeat .cmb-repeatable-grouping');
if(catfliter){
	for (let i = 0; i < catfliter.length; i++) {
		catfliter[i].querySelector('.cmb-group-title').innerText = catfliter[i].querySelector('#jj_new_links_'+i+'_title').value
		
		jQuery('#jj_new_links_'+i+'_title').on('input', function(val) {
			catfliter[i].querySelector('.cmb-group-title').innerText = val.target.value
		});
	}
}
var catfliter =document.querySelectorAll('#one_ysa_links_repeat .cmb-repeatable-grouping');
if(catfliter){
	for (let i = 0; i < catfliter.length; i++) {
		catfliter[i].querySelector('.cmb-group-title').innerText = catfliter[i].querySelector('#one_ysa_links_'+i+'_ysa_links_title').value
		
		jQuery('#one_ysa_links_'+i+'_ysa_links_title').on('input', function(val) {
			catfliter[i].querySelector('.cmb-group-title').innerText = val.target.value
		});
	}
}
var catfliter =document.querySelectorAll('#one_ysa_kefu_repeat .cmb-repeatable-grouping');
if(catfliter){
	for (let i = 0; i < catfliter.length; i++) {
		catfliter[i].querySelector('.cmb-group-title').innerText = catfliter[i].querySelector('#one_ysa_kefu_'+i+'_ysa_kefu_title').value
		
		jQuery('#one_ysa_kefu_'+i+'_ysa_kefu_title').on('input', function(val) {
			catfliter[i].querySelector('.cmb-group-title').innerText = val.target.value
		});
	}
}
var catfliter =document.querySelectorAll('#onecad_footer_ggz_repeat .cmb-repeatable-grouping');
if(catfliter){
	for (let i = 0; i < catfliter.length; i++) {
		catfliter[i].querySelector('.cmb-group-title').innerText = catfliter[i].querySelector('#onecad_footer_ggz_'+i+'_onecad_footer_ggz_title').value
		
		jQuery('#onecad_footer_ggz_'+i+'_onecad_footer_ggz_title').on('input', function(val) {
			catfliter[i].querySelector('.cmb-group-title').innerText = val.target.value
		});
	}
}
var catfliter =document.querySelectorAll('#jj_new_ewm_repeat .cmb-repeatable-grouping');
if(catfliter){
	for (let i = 0; i < catfliter.length; i++) {
		catfliter[i].querySelector('.cmb-group-title').innerText = catfliter[i].querySelector('#jj_new_ewm_'+i+'_title').value
		
		jQuery('#jj_new_ewm_'+i+'_title').on('input', function(val) {
			catfliter[i].querySelector('.cmb-group-title').innerText = val.target.value
		});
	}
}