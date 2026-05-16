$(function() {

    var browser = {
        versions: function() {
            var e = navigator.userAgent;
            navigator.appVersion;
            return {
                trident: e.indexOf("Trident") > -1,
                presto: e.indexOf("Presto") > -1,
                webKit: e.indexOf("AppleWebKit") > -1,
                gecko: e.indexOf("Gecko") > -1 && -1 == e.indexOf("KHTML"),
                mobile: !!e.match(/AppleWebKit.*Mobile.*/),
                ios: !!e.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
                android: e.indexOf("Android") > -1 || e.indexOf("Linux") > -1,
                iPhone: e.indexOf("iPhone") > -1,
                iPad: e.indexOf("iPad") > -1,
                webApp: -1 == e.indexOf("Safari")
            }
        }(),
        language: (navigator.browserLanguage || navigator.language).toLowerCase()
    };

    function post_video() {
        if (browser.versions.mobile)
            return !1;
        $(document).on("mouseenter mouseleave", ".width-1-4", function(t) {
            var e = $(this).find(".show-image")
                , i = $(this).find("video");
            i.hide();
            if ("mouseenter" == t.type) {
                $(this).find(".play-icon").css('opacity', 0);
                if (i.trigger("pause"),
                i.length <= 0)
                    return;
                e.hide(),
                    i.show()
                var a = i.attr("data-src");
                i.attr("src", a)
            } else
                "mouseleave" == t.type && i.trigger("pause"),
                    i.hide(),
                    e.show(),
                    $(this).find(".play-icon").css('opacity', '1')
        })
    }
    function post_music() {
        if (browser.versions.mobile)
            return !1;
        var url1 = window.location.href;
        var url2 = document.domain;
        var xyt = window.location.protocol + '//';
        var url = xyt + url2;
        $(document).on("mouseenter mouseleave", ".post-audio", function(t) {
            var i = $(this).find("video");
            if ("mouseenter" == t.type) {
                var id = $(this).attr('.audio-play');
                var src = $(this).attr('video-data');
                $(this).find('.play-icon').attr('src', '/wp-content/themes/b2Jitheme/Center/Assets/images/mp3/pause.svg');
                $(this).find('.play-pan').css({
                    "-webkit-animation": "z 5s linear 0s infinite",
                    "-moz-animation": "z 5s linear 0s infinite",
                    "-ms-animation": "z 5s linear 0s infinite",
                    "animation": "z 5s linear 0s infinite",
                })
                $(this).find('.play-zhen').css({
                    "transform": "rotate(15deg)"
                })
                $(this).find('.audio-play').attr('src', src);

                if (i.trigger("pause"),
                i.length <= 0)
                    return;
                e.hide(),
                    i.show();
                var a = i.attr("data-src");
                i.attr("src", a)
            } else
                "mouseleave" == t.type && i.trigger("pause"),
                    i.hide();
            var id = $(this).attr('post-id');
            var src = $(this).attr('video-data');
            $(this).find('.play-icon').attr('src', '/wp-content/themes/b2Jitheme/Center/Assets/images/mp3/play.svg');
            $(this).find('.play-zhen').css({
                "transform": "rotate(-9deg)"
            })
            $(this).find('.play-pan').css({
                "animation": "none"
            })
            $(this).find('#player-' + id).attr('src', '');
        })
    }
    post_video()
    post_music()
    if (jQuery('.ckplayer-video').length) {
        jQuery('.ckplayer-video').each(function() {
            jQuery(this).height(jQuery(this).width() * 0.5678);
        });
    }
    if (jQuery(".ckplayer-video-real").length) {
        jQuery(".ckplayer-video-real").bind('contextmenu', function() {
            return false;
        });
        jQuery(".ckplayer-video-real").each(function() {
            var conv = jQuery(this).data("video")
            conn = jQuery(this).data("nonce");
            new ckplayer({
                container: "#ckplayer-video-" + conn,
                variable: "player",
                autoplay: false,
                video: conv
            });
        });
    }
    //dplayer
    if (jQuery(".dplayer-video-real").length) {
        jQuery(".dplayer-video-real").bind('contextmenu', function() {
            return false;
        });
        jQuery(".dplayer-video-real").each(function() {
            var conv = jQuery(this).data("video")
            conn = jQuery(this).data("nonce");
            if (jQuery(this).hasClass("video-blob")) {
                jQuery(".article-video").append("<div class='article-video-loading' style='z-index: 9;position: absolute;top: calc(50% - 12px);left: 0;right: 0;color: #fff;font-size: 16px;text-align: center;'>视频加载中...</div>");
                window.URL = window.URL || window.webkitURL;
                var xhr = new XMLHttpRequest();
                // xhr.open("GET", Base64.decode(conv), true);
                xhr.open("GET", conv, true);
                xhr.responseType = "blob";
                xhr.timeout = 1000 * 60 * 1;
                xhr.onload = function(e) {
                    if (this.status == 200) {
                        var blob = this.response;
                        jQuery(".article-video-loading").remove();
                        new DPlayer({
                            container: document.getElementById("dplayer-video-" + conn),
                            screenshot: true,
                            autoplay: false,
                            video: {
                                url: window.URL.createObjectURL(blob),
                                pic: '',
                                thumbnails: '',
                                type: 'auto'
                            }
                        });
                    }
                }
                xhr.send();
            } else {
                new DPlayer({
                    container: document.getElementById("dplayer-video-" + conn),
                    screenshot: true,
                    autoplay: false,
                    video: {
                        url: conv,
                        pic: '',
                        thumbnails: '',
                        type: 'auto'
                    }
                });
            }
        });
    }
    //播放
    $("li a.video_link_play,.ceo-video-list-liebiao-s a").on("click", function(event) {
        // event.preventDefault()
        console.log($(this).attr("data-video_link"))
        var video_link = $(this).attr("data-video_link")
        if (video_link) {
            if ($(".single-video iframe").length) {
                $(".single-video iframe").attr('src', $(this).attr("data-video_link"))
                $(".ceo-video-list-liebiao-box li div").removeClass('ceo-video-list-liebiao-s')
                $(".ceo-video-list-liebiao-box li div a").removeClass('ceo-video-list-liebiao-d')
                $(this).prev().addClass('ceo-video-list-liebiao-d')
                $(this).addClass('ceo-video-list-liebiao-d')
                return false;
            }
            if ($(".single-video video").length) {
                if(typeof DPlayer != "undefined" && DPlayer){
                    // debugger
                    new DPlayer({
                        container: document.getElementById("dplayer-video-" + conn),
                        screenshot: true,
                        autoplay: false,
                        video: {
                            // url: conv,
                            url: video_link,
                            pic: '',
                            thumbnails: '',
                            type: 'auto'
                        }
                    });
                }else{
                    $(".single-video video").attr('src', $(this).attr("data-video_link"))
                }
                $(this).parent().parent().find('.ceo-shop5-liebiao-d').removeClass('ceo-shop5-liebiao-d')
                $(this).prev().addClass('ceo-shop5-liebiao-d');//标题文字
                $(this).addClass('ceo-shop5-liebiao-d');//播放
                return false;
            }

        }
    })
})
// $(function(){
//     //获取要定位元素距离浏览器顶部的距离
//     if ($(".ask-cat-list").length) {
//         var navH = $(".ask-cat-list").offset().top;
//     }
//     //
//     // console.log(navH);
//     //滚动条事件
//     $(window).scroll(function(){
//         //获取滚动条的滑动距离
//         var toggle = document.querySelector('.ask-cat-list');
//         var scroH = $(this).scrollTop();
//         // console.log(scroH);
//         //滚动条的滑动距离大于等于定位元素距离浏览器顶部的距离，就固定，反之就不固定
//         if(scroH>=navH){
//             toggle.classList.add("guding");
//             toggle.classList.remove('talk-list-fixed');
//         }else if(scroH<navH){
//             toggle.classList.remove('guding');
//             toggle.classList.add("talk-list-fixed");
//         }
//      })
// });
$(function() {
/*消息列表-----start*/
 var swiper = new Swiper("#Jitheme_message", {
    direction: 'vertical',  // 垂直轮播
    loop : true,    //切换效果 循环
    slidesPerView: 5,
    spaceBetween: 20,
    autoplay: {
        delay: 1000,
        stopOnLastSlide: false,
        disableOnInteraction: true,
    },
    autoplayPauseOnMouseEnter: true,
    autoplayDisableOnInteraction: false,
  });
});
