/**
 *极主题JS文件
*/
//滚动
$(function () {
    setInterval(function () {
        $('.news-list').animate({
            marginTop: '0px'
        }, 2000, function () {
        $(this).css({ marginTop: "0px" });
            var li = $(".news-list").children().first().clone()
            $(".news-list li:last").after(li);
            $(".news-list li:first").remove();
        })
    }, 3000)
})
//导航
$(document).scroll(function(e) {
    $(window).scrollTop() > 150 ? $('#page').addClass('ji_haeder') : $('#page').removeClass('ji_haeder');
});
//统计
$('.toggle-input').on('change', function() {
    $('body').toggleClass('light-version');
});
//单行滚动弹幕
function noticeUp(obj,top,time) {
    $(obj).animate({
    marginTop: top
    }, time, function () {
    $(this).css({marginTop:"0"}).find(":first").appendTo(this);
    })
}
/*幻灯片菜单首页动态大图搜索开始*/
const liElements = document.querySelectorAll('.main-menus .li');
liElements.forEach(li => {
  li.addEventListener('mouseenter', () => {
    li.classList.add('show');
    const subElement = li.querySelector('.sub');
    subElement.style.display = 'block';
    subElement.classList.add('fade-in');
  });
  li.addEventListener('mouseleave', () => {
    li.classList.remove('show');
    const subElement = li.querySelector('.sub');
    subElement.classList.remove('fade-in');
    setTimeout(() => {
      subElement.style.display = 'none';
    }, 300);
  });
});
/*首页动态大图搜索开始*/
(function($){
  var m=$('.primary-menus');
  if(m.length<1) return;
  var ul=m.find('.selects');
  if(ul.length<1) return;
  var lis=ul.children('li');
  if(lis.length<1) return;
  var s=m.find('.search');
  var sVal=s.find('.s').val();
  lis.on('click',function () {
    var d=$(this).attr('data-target');
    if (d) {
      lis.removeClass('current');
      $(this).addClass('current');
      s.addClass('hidden');
      s.filter('#'+d).removeClass('hidden');
      //s.filter('#'+d).find('.s').val('');
      s.filter('#'+d).find('.s').trigger('focusin');
    }
  });
  s.find('.s').on('focusin',function () {
    if ($(this).val()==sVal) {
      $(this).val('');
    }
  })
  s.find('.s').on('focusout',function () {
    var v=$(this).val();
    if (orz.isEmpty(v)) {
      v=sVal;
    }
    s.find('.s').val(v);
  })
})(jQuery);
/*首页动态大图搜索结束*/
