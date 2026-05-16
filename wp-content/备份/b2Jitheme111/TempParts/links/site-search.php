
<?php 
$links_search_title = b2_get_option('Jitheme_link_main','links_search_title');
$links_search_image = b2_get_option('Jitheme_link_main','links_search_image');
?>
  <div class="site-warp  b2-radius" style="background-image: url(<?php echo $links_search_image;?>);">
    <div class="site-search wrapper">
        <h3><?php echo $links_search_title;?></h3>
        <ul class="search-tmenu">
            <li class="active"><span>常用</span></li>
            <li><span>工具</span></li>
            <li><span>社区</span></li>
            <li><span>灵感</span></li>
        </ul>
        <div class="sousk">
            <form id="searchForm" class="shadow b2-radius" action="<?php echo esc_url( home_url( '' ) ); ?>/?s=" method="get" target="_blank">
                <input id="searchinput" class="" type="text" placeholder="站内搜索"  autocomplete="off">
                <button id="searc-submit" class="jitheme-site-search" type="submit">搜索</button>
            </form>
            <div class="b2-links-yl"><a href="./link-register" class="jitheme_search_add" target="_blank">申请入驻</a></div>
        </div>
        <div class="subnav">
            <div class="subnav-item active">
            	<ul class="search-bmenu uk-padding-remove">
            	    <li class="search-item on" url="<?php echo esc_url( home_url( '' ) ); ?>/?s=">站内</li>
            		<li class="search-item" url="https://www.baidu.com/s?wd=">百度</li>
            		<li class="search-item" url="https://www.google.com/search?q=">Google</li>
            		<li class="search-item" url="https://www.so.com/s?q=">360</li>
            		<li class="search-item" url="https://www.sogou.com/web?query=">搜狗</li>
            		<li class="search-item" url="https://cn.bing.com/search?q=">必应</li>
            		<li class="search-item" url="https://yz.m.sm.cn/s?q=">神马</li>
            	</ul>
            </div>
            <div class="subnav-item">
            	<ul class="search-bmenu uk-padding-remove">
            		<li class="search-item on" url="http://rank.chinaz.com/all/">权重查询</li>
            		<li class="search-item" url="http://link.chinaz.com/">友链检测</li>
            		<li class="search-item" url="https://icp.aizhan.com/">备案查询</li>
            		<li class="search-item" url="http://ping.chinaz.com/">PING检测</li>
            		<li class="search-item" url="http://tool.chinaz.com/Links/?DAddress=">死链检测</li>
            	</ul>
            </div>
            <div class="subnav-item">
            	<ul class="search-bmenu uk-padding-remove">
            		<li class="search-item on" url="https://www.zhihu.com/search?type=content&q=">知乎</li>
            		<li class="search-item" url="http://weixin.sogou.com/weixin?type=2&query=">微信</li>
            		<li class="search-item" url="http://s.weibo.com/weibo/">微博</li>
            		<li class="search-item" url="https://www.douban.com/search?q=">豆瓣</li>
            	</ul>
            </div>
            <div class="subnav-item">
            	<ul class="search-bmenu uk-padding-remove">
            		<li class="search-item on" url="https://huaban.com/search/?q=">花瓣</li>
            		<li class="search-item" url="https://dribbble.com/search/">dribbble</li>
            		<li class="search-item" url="https://www.behance.net/search?search=">behance</li>
            		<li class="search-item" url="http://www.zcool.com.cn/search/content?&word=">站酷</li>
            	</ul>
            </div>
        </div>
    </div>
</div>
<script>
//首页搜索条
var jsonData = ["百度一下","权重查询(不带http/https)","知乎","花瓣"]
var jsonDataUrl = ["https://www.baidu.com/s?wd=","http://rank.chinaz.com/all/","https://www.zhihu.com/search?type=content&q=","https://www.huaban.com/search"]
$('.search-tmenu li').click(function(){
var _index = $(this).index();
$(this).addClass("active").siblings().removeClass("active");
$('.subnav').each(function(){
$(this).find('div').eq(_index).addClass("active").siblings().removeClass("active");
})
console.log(_index)
})
$(".subnav-item").on("shown",function(){
$("#searchForm").attr("action",jsonDataUrl[$(this).index()]);
$("#searchinput").attr("placeholder",jsonData[$(this).index()]);
})
$(".search-item").on("click",function(){
$("#searchForm").attr("action",$(this).attr("url"));
$("#searchinput").attr("placeholder",$(this).html());
$(this).addClass("on").siblings().removeClass("on");
})
 
$("#searc-submit").click(function(){
window.open($("#searchForm").attr("action")+$("#searchinput").val())
return false;
})
 
$('#searchForm').keydown(function(e) {
if (e.keyCode == 13) {
window.open($("#searchForm").attr("action")+$("#searchinput").val())
return false;
}
});
</script>