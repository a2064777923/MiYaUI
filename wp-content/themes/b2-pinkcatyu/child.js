//复制成功弹窗
document.body.oncopy = function() {
Qmsg['success']('复制成功', {
html: true
});
};

$(function(){
    /*弹窗登录效果*/
    $("#login-box .login-box-content").addClass("b2-radius");
    $('.login-box-content').prepend('<div class="aibk_com_login">'+
        // '<div class="wxlogin-sidebar">'+
        // '<div style="'+
        //     'text-align: center; /* 文字水平居中 */'+
        //     'background: rgb(255 255 255 / 0%); /* 半透明白色背景，增强虚化层次感 */'+
        //     'backdrop-filter: blur(10px); /* 背景虚化（毛玻璃核心） */'+
        //     '-webkit-backdrop-filter: blur(10px); /* 兼容Safari/Chrome */'+
        //     'border-radius: 12px; /* 圆角矩形，数值越大圆角越圆润 */'+
        //     'padding: 15px 25px; /* 内边距，让文字和边框保持舒适间距 */'+
        //     'margin: 10px auto; /* 水平居中，适配不同宽度容器 */'+
        //     'max-width: 90%; /* 限制最大宽度，避免拉伸变形 */'+
        //     'color: #333; /* 文字颜色，保证可读性 */'+
        //     'box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); /* 轻微阴影，增强立体感 */'+
        //     'border: 1px solid rgba(255, 255, 255, 0.2); /* 浅边框，优化圆角边缘 */'+
        // '">'+
        //     '<h3 style="margin: 0 0 8px 0; font-weight: normal;">WWW.MIYAUI.COM</h3>'+
        //     '<p style="margin: 0; font-size: 14px; color: #c3c3c3;">为设计师打造的设计干货知识平台</br>优质设计资源免费下载</p>'+
        // '</div>'+
        // '</div>'+
    '</div>');
})

$(function(){$('.tabPanel ul li').click(function(){$(this).addClass('hit').siblings().removeClass('hit');
$('.panes>div:eq('+$(this).index()+')').show().siblings().hide();})})
$(function(){ $(".bq-close").click(function(){ $('.comt-smilies').hide(); });});
$(function(){ $(".comt-addsmilies").click(function(){ $('.comt-smilies').toggle(); });});
$(function(){ $("textarea").click(function(){ $('.comt-smilies').hide(); });});
$(function(){ $(".bq-clos").click(function(){ $('.comt-smilies').hide(); });});
function mrxubq(mrxu){var content = $('#textarea').val();var xu=content+mrxu;$('#textarea').val(xu);}


/*夜间模式开始*/
(function(){
    if(document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") === ''){
        if(new Date().getHours() > 23 || new Date().getHours() < 5){
            document.body.classList.add('night');
            document.cookie = "night=1;path=/";
        }else{
            document.body.classList.remove('night');
            document.cookie = "night=0;path=/";
        }
    }else{
        var night = document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
    if(night == '0'){
        document.body.classList.remove('night');
    }else if(night == '1'){
        document.body.classList.add('night');
        }
    }
    })();
    function switchNightMode(){
        var night = document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
        if(night == '0'){
            document.body.classList.add('night');
            document.cookie = "night=1;path=/"
        }else{
            document.body.classList.remove('night');
            document.cookie = "night=0;path=/"
        }
    }
    // 页面宽度按钮
    (function(){
    if(document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") === ''){
        if(new Date().getHours() > 20 || new Date().getHours() < 0.1){
            document.body.classList.add('night');
            document.cookie = "night=1;path=/";
        }else{
            document.body.classList.remove('night');
            document.cookie = "night=0;path=/";
        }
    }else{
        var night = document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
    if(night == '0'){
        document.body.classList.remove('night');
    }else if(night == '1'){
        document.body.classList.add('night');
        }
    }
    })();
    function switchwidth(){
        var night = document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
        if(night == '0'){
            document.body.classList.add('night');
            document.cookie = "night=1;path=/"
        }else{
            document.body.classList.remove('night');
            document.cookie = "night=0;path=/"
        }
    }
//添加夜间模式按钮
//  $('.change-theme').append('<div data-title="昼夜切换" class="user-tips"><a href="javascript:switchNightMode()">'+
//     '<svg class="icon-small" aria-hidden="true"><use xlink:href="#icon-yejianqing" style="font-size: 18px;left: -1px;&gt;&lt;/use&gt; &lt;/svg&gt;&lt;span class=" bar-item-desc"="">昼夜切换</use></svg>'
//     );
// $(function(){
//     $('.bar-footer').prepend('<a href="javascript:switchNightMode()"><div class="bar-item">'+
//     '<svg class="icon-small" aria-hidden="true"><use xlink:href="#icon-yejianqing" style="font-size: 18px;left: -1px;></use> </svg>'+
//     '<span class="bar-item-desc">昼夜切换</span></a>'
//     );
// })
/*夜间模式结束*/
