//您自己的js代码写到下面
//评论栏快捷打卡/一言回复
//$(function(){$('.com-form-button-l').after('<p id="mrxu_daka" style="color:#65adff">#不想打字就点我#</p>');});$(function() {$("#mrxu_daka").click(function() { $.getJSON("https://api.uixsj.cn/hitokoto/get?type=hitokoto&code=json",function(xu){ $("#textarea").val(xu.content);});});});
//指定位置插入代码
//$(function(){$('.post-module-thumb').after('<div class="item item-author"><div class="item-bg"><i class="thumb thumb-c"></i></div></div>');});
//首页是否购买VIP判断开始
var qukuai = new Vue({
	el:'.sort-mine-wrap',
	data:{
	},
	computed:{
        userData(){
            return this.$store.state.userData;
        }
    }
})
//首页是否购买VIP判断结束
var ht = document.getElementsByTagName("body")[0];
ht.style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";
//问答小工具
var askWidget = new Vue({
    el:'.ji-widget-ask',
    data:{
        data:'',
        fliter:'last',
        paged:1,
        pages:0,
        count:0,
        cat:0,
        empty:false,
        locked:false,
        prev:true,
        next:false
    },
    mounted(){
        if(!this.$refs.askwidget) return
        this.count = this.$refs.askwidget.getAttribute('data-count')
        this.time = this.$refs.askwidget.getAttribute('data-time')
        const archive = document.querySelector('.ask-archive')
        if(archive){
            this.cat = archive.getAttribute('data-term')
        }

        const single = document.querySelector('.ask-single-top')

        if(single){
            this.cat = single.getAttribute('data-term')
        }

        this.getData()
    },
    watch:{
        fliter(val){
            this.prev = true
            this.next = false
            this.data = ''
            this.paged = 1
            this.empty = false
            this.$refs.askwidget.querySelector('.gujia').style.display = 'block'
            this.getData()
        }
    },
    methods:{
        nexAc(){
            if(this.paged >= this.data.pages) return
            if(this.locked) return
            this.next = true
            this.paged++
            this.getData()
        },
        prevAc(){
            if(this.paged < 1) return
            if(this.locked) return
            this.prev = true
            this.paged--
            this.getData()
        },
        getData(){
            if(this.locked) return
            this.locked = true
            this.$http.post(b2_rest_url+'getAskData','paged='+this.paged+'&type='+this.fliter+'&count='+this.count+'&cat='+this.cat).then(res=>{
                this.locked = false
                if(this.paged == 1){
                    if(res.data.data.length == 0){
                        this.empty = true
                        this.data.data = []
                    }else{
                        this.data = res.data
                    }
                }else{
                    this.data.data = res.data.data
                }
                if(this.data.pages > 1 && this.paged < this.data.pages){
                    this.next = false
                }
                if(this.paged > 1){
                    this.prev = false
                }
                this.$nextTick(()=>{
                    this.$refs.askwidget.querySelector('.gujia').style.display = 'none'
                })

                
            })
        }
    }
})
//作者信息
var postAuthor = new Vue({
    el:'.ji-widget-author',
    data:{
        following:false
    },
    methods:{
        followingAc(){
            
            if(!b2token){
                login.show = true
            }else{
                this.$http.post(b2_rest_url+'AuthorFollow','user_id='+b2_global.author_id).then(res=>{
                    this.following = !this.following
                }).catch(err=>{
                    Qmsg['warning'](err.response.data.message,{html:true});
                })
            }
        },
        dmsg(){
            
            if(!b2token){
                login.show = true
            }else{
                b2Dmsg.userid = b2_global.author_id
                b2Dmsg.show = true
            }
        },
    }
})
//用户面板
var B2UserWidget = new Vue({
    el:'.ji-widget-user',
    data:{
        show:false,
        b2token:false
    },
    mounted(){
        this.b2token = b2token
    },
    computed:{
        userData(){
            return this.$store.state.userData
        },
        oauth(){
            return this.$store.state.oauthLink
        },
        announcement(){
            return this.$store.state.announcement
        },
        openOauth(){
            return this.$store.state.openOauth
        }
    },
    watch:{
        announcement(val){
            if(val && !this.show){
                this.resize()
            }
        }
    },
    methods:{
        resize(){
            this.$nextTick(()=>{
                if(!this.$refs.userWidget) return
                if(this.$refs.gujia){
                    this.$refs.gujia.style.display = 'none'
                }
                setTimeout(() => {
                    b2tooltip('.user-w-tips')
                }, 300);

                this.show = true
            })
        },
        markHistory(type){
            if(this.oauth.weixin.mp && type === 'weixin'){
                mpCode.show = true
            }
            b2setCookie('b2_back_url',window.location.href)
        }
    }
})
//加载画廊开始-->
var swiper = new Swiper('.mySwiper2', {
    preloadImages:false,
    autoHeight: true,
    autoplay: {
    disableOnInteraction: false,
    pauseOnMouseEnter: true,
    },
    grabCursor : true,
	loop: true,
	spaceBetween:0,
	autoHeight: true,
	navigation: {
		nextEl: ".swiper-button-next",
		prevEl: ".swiper-button-prev",
	},
	thumbs: {
	   
	    grabCursor : true,
		swiper: {
			el: '.mySwiper', //注意此处的设置方式
			spaceBetween: 10,
			slidesPerView: 'auto',
			watchSlidesProgress: true,
		}
	}
});
// let doms = document.querySelectorAll('.Onecad_title');
// doms.forEach((dom, index)=>{
//     dom.style = `
//         background: url(https://www.miyaui.com/wp-content/themes/b2Jitheme/img/title.png) no-repeat;
//         background-position: 0 -${(index+1)*55}px;
//         position: relative;
//         margin-left: -10px;
//         margin-right: 20px;
//         height: 37px;`;
//     dom.querySelector('div').style = `padding-left: 45px;
//         padding-top: 2px;
//         font-size: 19px;
//         text-align: left;
//         font-weight: 600;`;
// });
$(function(){
/*弹窗登录效果*/$("#login-box .login-box-content").addClass("b2-radius");
$('.login-box-content').prepend('<div class="aibk_com_login">'+
'<div>'+
'</div>'+
'</div>'+
'</div>'
);
})