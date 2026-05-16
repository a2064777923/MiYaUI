/**
 *暗黑切换 bylitheme
*/
$('document').ready(function(){
    $(document).on('click', '.dark-style-toggle', function(event){
        let _this = $(this);
        let active = _this.hasClass('active'); // 状态
        window.localStorage && localStorage.setItem('darkStyle', active ? 0 : 1);
        if(active){
            $("body").removeClass("style-for-dark");
            setTimeout(function() {
                _this.removeClass("active");
            }, 580);
            Qmsg['success']('已切换“浅色模式”！', {
            html: true
        });
        }else{
            $("body").addClass("style-for-dark");
            setTimeout(function() {
                _this.addClass("active");
            }, 580);
             Qmsg['success']('已切换“深色模式”！', {
            html: true
        });
        }
    });
    var dark = localStorage.getItem('darkStyle');
    var toggle = document.querySelector('.dark-style-toggle');
    let hr = (new Date()).getHours();
    if(dark === null){
        if((21<hr && hr<24) || (0<hr && hr<5)){
            $("body").addClass("style-for-dark");
        }else{
            $("body").removeClass("style-for-dark");
        }
    }
});
function DoOne(key,method) {
	var v = getCookie(key);
	if (!v) {
	    var dark = localStorage.getItem(method);
		if(dark){
		    localStorage.removeItem(method);
		}
		//获取第二天凌晨到当前时间的秒数
		var tim_sec = 24 * 60 * 60 - (new Date().getHours() * 60 * 60 + new Date().getMinutes() * 60 + new Date().getSeconds());
		setCookie(key, "1", tim_sec);
	}
}
function dosome(msg){
	console.log(msg);
}
//写cookies 
function setCookie(name, value, second) {
	if (!second) {
		second = 7 * 24 * 60 * 60;//默认7天
	}
	var exp = new Date();
	exp.setTime(exp.getTime() + second * 1000);
	document.cookie = name + "=" + encodeURI(value) + ";expires=" + exp.toGMTString() + ";path=/";
}
//读取cookies 
function getCookie(name) {
	if (document.cookie.length > 0) {
		c_start = document.cookie.indexOf(name + "=");//获取字符串的起点
		if (c_start != -1) {
			c_start = c_start + name.length + 1;//获取值的起点
			c_end = document.cookie.indexOf(";", c_start);//获取结尾处
			if (c_end == -1) c_end = document.cookie.length;//如果是最后一个，结尾就是cookie字符串的结尾
			return decodeURI(document.cookie.substring(c_start, c_end));//截取字符串返回
		}
	}
	return "";
}
DoOne('darkStyle', 'darkStyle');