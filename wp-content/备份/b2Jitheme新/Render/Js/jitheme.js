/**
 *极主题JS文件
*/
// function b2prettyPrint() {
//     // 如果 prettify 尚未加载，直接返回
//     if (b2_global.prettify_load == '0') return;

//     let classArr = document.getElementsByTagName('pre'); // 获取所有 pre 标签

//     // 确保 class 是正确的
//     if (typeof(classArr) == "object") {
//         for (var i = 0; i < classArr.length; i++) {
//             if (classArr[i].className.indexOf('prettyprint') <= -1) {
//                 classArr[i].className = "prettyprint linenums";
//             }
//         }
//     }

//     // 执行 prettyPrint() 高亮代码
//     prettyPrint();

//     // 遍历所有 pre 标签，添加一键复制按钮
//     [...document.getElementsByTagName('pre')].forEach(item => {
//         // 确保 pre 标签包含子元素，并且代码内容不为空
//         if (item.children.length > 0 && item.innerText.trim() !== "") {
//             item.style.position = "relative"; // 设置 relative 位置
//             // 如果 pre 标签中没有复制按钮（class 为 copy-btn 的元素）
//             if (!item.querySelector('.copy-btn')) {
//                 // 创建一个父 div，用于包装复制按钮和额外的内容
//                 let parentDiv = document.createElement("div");
//                 parentDiv.className = "copy-btn-wrapper"; // 父级 div 可以给一个类名来便于样式控制
//                 // 创建额外的 div（放在父 div 的第一个位置）
//                 let newDiv1 = document.createElement("div");
//                 newDiv1.className = "extra-div-1";
//                 newDiv1.textContent = "极主题 Jitheme.com"; // 这里可以放任何自定义内容
            
//                 // 创建复制按钮 div
//                 let buttonDiv = document.createElement("div");
//                 buttonDiv.className = "copy-btn";
//                 buttonDiv.style.cursor = "pointer";  // 设置为可点击样式
            
//                 // 创建图标（使用自定义字体图标）
//                 let copyIcon = document.createElement("i");
//                 copyIcon.className = "b2font b2-article-line";  // 初始图标
            
//                 // 创建文字节点
//                 let textNode = document.createTextNode(" 复制代码");
            
//                 // 将图标和文字添加到复制按钮 div 中
//                 buttonDiv.appendChild(copyIcon);
//                 buttonDiv.appendChild(textNode);
            
//                 // 将额外的 div 和按钮添加到父级 div 中，确保额外的 div 在第一个
//                 parentDiv.appendChild(newDiv1);  // 将 extra-div-1 放在第一个位置
//                 parentDiv.appendChild(buttonDiv);  // 然后放复制按钮
            
//                 // 点击事件处理
//                 buttonDiv.onclick = function () {
//                     // 获取代码内容，确保从第一个子节点获取文本内容
//                     let copyData = item.innerText.trim();
            
//                     // 复制到剪贴板
//                     navigator.clipboard.writeText(copyData).then(() => {
//                         // 复制成功后，更新按钮显示
//                         copyIcon.className = "b2font b2-checkbox-circle-fill";  // 更改为成功图标
//                         textNode.textContent = " 已复制";  // 更改文字为 "已复制"
            
//                         // 1秒后恢复原始状态
//                         setTimeout(function () {
//                             copyIcon.className = "b2font b2-article-line";
//                             textNode.textContent = " 复制代码";
//                         }, 1000);
            
//                         // 调用 Qmsg 弹窗显示复制成功
//                         Qmsg['success']('复制成功', { html: true });
//                     }).catch((err) => {
//                         console.error('复制失败:', err);
//                     });
//                 };
            
//                 // 将包含复制按钮和额外内容的父级 div 插入到 pre 标签的第一个子元素位置
//                 item.insertBefore(parentDiv, item.firstChild);
//             }


//         }
//     });
// }
function b2prettyPrint() {
    // 如果 prettify 尚未加载，直接返回
    if (b2_global.prettify_load == '0') return;

    let classArr = document.getElementsByTagName('pre'); // 获取所有 pre 标签

    // 确保 class 是正确的
    if (typeof(classArr) == "object") {
        for (var i = 0; i < classArr.length; i++) {
            if (classArr[i].className.indexOf('prettyprint') <= -1) {
                classArr[i].className = "prettyprint linenums b2-radius";
            }
        }
    }

    // // 执行 prettyPrint() 高亮代码
    // prettyPrint();

    // 遍历所有 pre 标签，添加一键复制按钮
    [...document.getElementsByTagName('pre')].forEach(item => {
        // 确保 pre 标签包含子元素，并且代码内容不为空
        if (item.children.length > 0 && item.innerText.trim() !== "") {
            item.style.position = "relative"; // 设置 relative 位置
            // 如果 pre 标签中没有复制按钮（class 为 copy-btn 的元素）
            if (!item.querySelector('.copy-btn')) {
                // 创建一个父 div，用于包装复制按钮和额外的内容
                let parentDiv = document.createElement("div");
                parentDiv.className = "copy-btn-wrapper"; // 父级 div 可以给一个类名来便于样式控制
                // 创建额外的 div（放在父 div 的第一个位置）
                let newDiv1 = document.createElement("div");
                newDiv1.className = "extra-div-1";
                newDiv1.textContent = "极主题 Jitheme.com"; // 这里可以放任何自定义内容
            
                // 创建复制按钮 div
                let buttonDiv = document.createElement("div");
                buttonDiv.className = "copy-btn";
                buttonDiv.style.cursor = "pointer";  // 设置为可点击样式
            
                // 创建图标（使用自定义字体图标）
                let copyIcon = document.createElement("i");
                copyIcon.className = "b2font b2-article-line";  // 初始图标
            
                // 创建文字节点
                let textNode = document.createTextNode(" 复制代码");
            
                // 将图标和文字添加到复制按钮 div 中
                buttonDiv.appendChild(copyIcon);
                buttonDiv.appendChild(textNode);
            
                // 将额外的 div 和按钮添加到父级 div 中，确保额外的 div 在第一个
                parentDiv.appendChild(newDiv1);  // 将 extra-div-1 放在第一个位置
                parentDiv.appendChild(buttonDiv);  // 然后放复制按钮
            
                // 点击事件处理
                buttonDiv.onclick = function () {
                    // 获取代码内容，只获取包含 linenums 类的内容
                    let codeLines = item.querySelectorAll('.linenums');
                    let copyData = Array.from(codeLines).map(line => line.innerText).join('\n');
            
                    // 复制到剪贴板
                    navigator.clipboard.writeText(copyData).then(() => {
                        // 复制成功后，更新按钮显示
                        copyIcon.className = "b2font b2-checkbox-circle-fill";  // 更改为成功图标
                        textNode.textContent = " 已复制";  // 更改文字为 "已复制"
            
                        // 1秒后恢复原始状态
                        setTimeout(function () {
                            copyIcon.className = "b2font b2-article-line";
                            textNode.textContent = " 复制代码";
                        }, 1000);
            
                        // 调用 Qmsg 弹窗显示复制成功
                        Qmsg['success']('复制成功', { html: true });
                    }).catch((err) => {
                        console.error('复制失败:', err);
                    });
                };
            
                // 将包含复制按钮和额外内容的父级 div 插入到 pre 标签的第一个子元素位置
                item.insertBefore(parentDiv, item.firstChild);
            }
        }
    });
}
// 监听页面加载完成，确保所有元素都渲染好
window.onload = function() {
    b2prettyPrint();  // 页面加载完成后调用
};

// 如果页面是通过 AJAX 加载的内容更新，可能需要再次调用 b2prettyPrint
document.addEventListener('DOMContentLoaded', function() {
    b2prettyPrint();
});

//单行滚动弹幕
function Mini_msg(obj,top,time) {
    $(obj).animate({
    marginTop: top
    }, time, function () {
    $(this).css({marginTop:"0"}).find(":first").appendTo(this);
    })
}
//不同文章独立的隐私说明和权限
$(document).ready(function() {
    $('.qx').on('click', function() {
        $('.yszcbox').css('display', 'block');
    });
    $(".qxclose").click(function(){
        $(".yszcbox").hide();
    });

    $('.ys').on('click', function() {
        $('.gRule').css('display', 'block');
    });
    $(".ysclose").click(function(){
        $(".gRule").hide();
    });
});
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
//图片瀑布流
(function(a, b) {
	var c = function(a, b, c) {
		var d;
		return function() {
			function h() {
				if (!c) a.apply(f, g);
				d = null
			}
			var f = this,
			g = arguments;
			if (d) clearTimeout(d);
			else if (c) a.apply(f, g);
			d = setTimeout(h, b || 150)
		}
	};
	jQuery.fn[b] = function(a) {
		return a ? this.bind("resize", c(a)) : this.trigger(b)
	}
})(jQuery, "smartresize"); (function(a) {
	a.Gal = function(b, c) {
		this.element = a(c);
		this._init(b)
	};
	a.Gal.settings = {
		selector: ".item",
		width: 225,
		gutter: 20,
		animate: false,
		animationOptions: {
			speed: 200,
			duration: 300,
			effect: "fadeInOnAppear",
			queue: true,
			complete: function() {}
		}
	};
	a.Gal.prototype = {
		_init: function(b) {
			var c = this;
			this.name = this._setName(5);
			this.gridArr = [];
			this.gridArrAppend = [];
			this.gridArrPrepend = [];
			this.setArr = false;
			this.setGrid = false;
			this.setOptions;
			this.cols = 0;
			this.itemCount = 0;
			this.prependCount = 0;
			this.isPrepending = false;
			this.appendCount = 0;
			this.resetCount = true;
			this.ifCallback = true;
			this.box = this.element;
			this.options = a.extend(true, {},
			a.Gal.settings, b);
			this.gridArr = a.makeArray(this.box.find(this.options.selector));
			this.isResizing = false;
			this.w = 0;
			this.boxArr = [];
			this._setCols();
			this._renderGrid("append");
			a(this.box).addClass("gridalicious");
			a(window).smartresize(function() {
				c.resize()
			})
		},
		_setName: function(a, b) {
			b = b ? b: "";
			return a ? this._setName(--a, "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 60)) + b) : b
		},
		_setCols: function() {
			this.cols = Math.floor(this.box.width() / this.options.width);
			diff = (this.box.width() - this.cols * this.options.width - this.options.gutter) / this.cols;
			w = (this.options.width + diff) / this.box.width() * 100;
			this.w = w;
			for (var b = 0; b < this.cols; b++) {
				var c = a("<div></div>").addClass("galcolumn").attr("id", "item" + b + this.name).css({
					width: w + "%",
					paddingLeft: this.options.gutter,
					paddingBottom: this.options.gutter,
					"float": "left",
					"-webkit-box-sizing": "border-box",
					"-moz-box-sizing": "border-box",
					"-o-box-sizing": "border-box",
					"box-sizing": "border-box"
				});
				this.box.append(c)
			}
			this.box.find(a("#clear" + this.name)).remove();
			var d = a("<div></div>").css({
				clear: "both",
				height: "0",
				width: "0",
				display: "block"
			}).attr("id", "clear" + this.name);
			this.box.append(d)
		},
		_renderGrid: function(b, c, d, e) {
			var f = [];
			var g = [];
			var h = [];
			var i = 0;
			var j = this.prependCount;
			var k = this.appendCount;
			var l = this.options.gutter;
			var m = this.cols;
			var n = this.name;
			var o = 0;
			var p = a(".galcolumn").width();
			if (c) {
				g = c;
				if (b == "append") {
					k += d;
					i = this.appendCount
				}
				if (b == "prepend") {
					this.isPrepending = true;
					i = Math.round(d % m);
					if (i <= 0) i = m
				}
				if (b == "renderAfterPrepend") {
					k += d;
					i = d
				}
			} else {
				g = this.gridArr;
				k = a(this.gridArr).size()
			}
			a.each(g,
			function(c, d) {
				var e = a(d);
				var g = "100%";
				if (e.hasClass("not-responsive")) {
					g = "auto"
				}
				e.css({
					marginBottom: l,
					zoom: "1",
					filter: "alpha(opacity=0)",
					opacity: "0"
				}).find("img, object, embed, iframe").css({
					width: g,
					height: "auto",
					display: "block",
					"margin-left": "auto",
					"margin-right": "auto"
				});
				if (b == "prepend") {
					i--;
					a("#item" + i + n).prepend(e);
					f.push(e);
					if (i == 0) i = m
				} else {
					a("#item" + i + n).append(e);
					f.push(e);
					i++;
					if (i >= m) i = 0;
					if (k >= m) k = k - m
				}
			});
			this.appendCount = k;
			this.itemCount = i;
			if (b == "append" || b == "prepend") {
				if (b == "prepend") {
					this._updateAfterPrepend(this.gridArr, g)
				}
				this._renderItem(f);
				this.isPrepending = false
			} else {
				this._renderItem(this.gridArr)
			}
		},
		_collectItems: function() {
			var b = [];
			a(this.box).find(this.options.selector).each(function(c) {
				b.push(a(this))
			});
			return b
		},
		_renderItem: function(b) {
			var c = this.options.animationOptions.speed;
			var d = this.options.animationOptions.effect;
			var e = this.options.animationOptions.duration;
			var f = this.options.animationOptions.queue;
			var g = this.options.animate;
			var h = this.options.animationOptions.complete;
			var i = 0;
			var j = 0;
			if (g === true && !this.isResizing) {
				if (f === true && d == "fadeInOnAppear") {
					if (this.isPrepending) b.reverse();
					a.each(b,
					function(d, f) {
						setTimeout(function() {
							a(f).animate({
								opacity: "1.0"
							},
							e);
							j++;
							if (j == b.length) {
								h.call(undefined, b)
							}
						},
						i * c);
						i++
					})
				} else if (f === false && d == "fadeInOnAppear") {
					if (this.isPrepending) b.reverse();
					a.each(b,
					function(c, d) {
						a(d).animate({
							opacity: "1.0"
						},
						e);
						j++;
						if (j == b.length) {
							if (this.ifCallback) {
								h.call(undefined, b)
							}
						}
					})
				}
				if (f === true && !d) {
					a.each(b,
					function(c, d) {
						a(d).css({
							opacity: "1",
							filter: "alpha(opacity=1)"
						});
						j++;
						if (j == b.length) {
							if (this.ifCallback) {
								h.call(undefined, b)
							}
						}
					})
				}
			} else {
				a.each(b,
				function(b, c) {
					a(c).css({
						opacity: "1",
						filter: "alpha(opacity=1)"
					})
				});
				if (this.ifCallback) {
					h.call(b)
				}
			}
		},
		_updateAfterPrepend: function(b, c) {
			var d = this.gridArr;
			a.each(c,
			function(a, b) {
				d.unshift(b)
			});
			this.gridArr = d
		},
		resize: function() {
			this.box.find(a(".galcolumn")).remove();
			this._setCols();
			this.ifCallback = false;
			this.isResizing = true;
			this._renderGrid("append");
			this.ifCallback = true;
			this.isResizing = false
		},
		append: function(b) {
			var c = this.gridArr;
			var d = this.gridArrPrepend;
			a.each(b,
			function(a, b) {
				c.push(b);
				d.push(b)
			});
			this._renderGrid("append", b, a(b).size())
		},
		prepend: function(b) {
			this.ifCallback = false;
			this._renderGrid("prepend", b, a(b).size());
			this.ifCallback = true
		}
	};

	a.fn.gridalicious = function(b, c) {
		if (typeof b === "string") {
			this.each(function() {
				var d = a.data(this, "gridalicious");
				d[b].apply(d, [c])
			})
		} else {
			this.each(function() {
				a.data(this, "gridalicious", new a.Gal(b, this))
			})
		}
		return this
	}
})(jQuery);


