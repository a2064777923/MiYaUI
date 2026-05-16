/**
 * 自定义效果
 * 
 * @author 阿锋
 * @link https://feng.pub/contact
 */
const fengCustomEffect = (() => {
	const
		/**
		 * 获取随机字符串
		 * @param {Object} e 获取字符串的长度
		 * @param {Object} t 被选取的字符串，默认ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678
		 */
		randomString = (e, t) => {
			e = e || 32;
			t = t || "ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678",
				a = t.length,
				n = "";
			for (i = 0; i < e; i++) n += t.charAt(Math.floor(Math.random() * a));
			return n;
		},

		/**
		 * 为链接添加参数
		 */
		addUrlPar = (destiny, par, par_value) => {
			var pattern = par + '=([^&]*)';
			var replaceText = par + '=' + par_value;
			if (destiny.match(pattern)) {
				var tmp = '/\\\\' + par + '=[^&]*/';
				tmp = destiny.replace(eval(tmp), replaceText);
				return (tmp);
			} else {
				if (destiny.match('[\?]')) {
					return destiny + '&' + replaceText;
				} else {
					return destiny + '?' + replaceText;
				}
			}
			return destiny + '\\n' + par + '\\n' + par_value;
		},
		/**
		 * 根据img标签的srcset属性获取最大图片，如果没有srcset将返回src
		 */
		getMaxImg = (img) => {
			const srcset = img.getAttribute("srcset");
			if (srcset === null) {
				return img.getAttribute("src");
			}
			let maxWidth = 0, maxSrc;
			let srcsetArr = srcset.split(",").forEach(e => {
				const srcData = e.trim().split(" ");
				const width = srcData[1].replace("w", "");
				if (+width > +maxWidth) {
					maxWidth = width;
					maxSrc = srcData[0];
				}
			});
			return maxSrc;
		},
		fun = {
			/**
			 * 显示运行时间
			 * @param {} option 
			 */
			showRun: (option) => {
				const startDate = new Date(option.start),
					nowDate = new Date(),
					intervalTime = nowDate.getTime() - startDate.getTime(),
					oneDayMilliseconds = 24 * 60 * 60 * 1000,
					runDays = Math.floor(intervalTime / oneDayMilliseconds);

				let showText = option.text.replace("{days}", "<small>" + runDays + "</small>");
				const binding = option.binding ? option.binding : "body";
				document.querySelector(binding).innerHTML += showText;
			},
			keyboard: () => {
				POWERMODE.colorful = true;
				POWERMODE.shake = false;
				document.body.addEventListener("input", POWERMODE);
			},
			/**
						 * 网页灰色模式
						 * @param {} option 
						 */
			grayPage: (option) => {
				const startDate = new Date(option.start).getTime(),
					endDate = new Date(option.end).getTime(),
					nowDate = new Date().getTime();
				let grayPagePattern = true;
				if (startDate && nowDate - startDate < 0) {
					grayPagePattern = false;
				}
				if (endDate && endDate - nowDate < 0) {
					grayPagePattern = false;
				}
				if (grayPagePattern) document.querySelector("html").classList.add("fct-gray-page");
			},
			lightBox: (option) => {
				if (option.binding) {
					Fancybox.bind(option.binding, {
						groupAll: true
					});
					return;
				}
				const entryContent = document.querySelector(".entry-content");
				if (!entryContent) return
				// 图片
				const images = entryContent.querySelectorAll(".wp-block-image img"),
					// 图库
					galleries = entryContent.querySelectorAll(".wp-block-gallery"),
					// 媒体图文
					media = entryContent.querySelectorAll(".wp-block-media-text__media");

				images.forEach((img, k) => {
					img.setAttribute("data-fancybox", "img-" + k);
					img.setAttribute("data-src", getMaxImg(img));
				});

				galleries.forEach((gallery, k) => {
					gallery.querySelectorAll(".wp-block-image img").forEach((e) => {
						e.setAttribute("data-fancybox", "gallery-" + k);
					});
				});

				media.forEach((e, k) => {
					e.setAttribute("data-fancybox", "media-" + k);
					e.setAttribute("data-src", getMaxImg(e.querySelector("img")));
				});

				Fancybox.bind("[data-fancybox]", {});
			},
			/**
			 * 
			 * 新窗口打开第三方链接，并添加小尾巴（晨风自定义）
			 * 
			 * 该文件不会被加载，该文件是为了方便开发使用
			 * 如果您有更好的优化建议，请联系我或Gitee内提交
			 * @link https://gitee.com/ouros/feng-custom
			 * 
			 * @author 阿锋
			 * @link https://feng.pub
			 */
			url: (option) => {
				document.querySelectorAll("a").forEach(element => {
					element.addEventListener("click", (e) => {
						let href = e.target.href,
							host = window.location.host,
							hrefArr = [],
							domain = '';
						if (href !== undefined && href !== '') {
							if (href.indexOf('#') !== -1) {
								hrefArr = href.split('#');
								hrefArr = hrefArr[0].split('/');
							} else {
								hrefArr = href.split('/');
							}
							domain = hrefArr[2] !== undefined ? hrefArr[2] : '';
							if (domain !== undefined) {
								if (domain !== host) {
									const source = option.source.split('=');
									href = source[1] !== undefined ? addUrlPar(href, source[0], source[1]) : href;
									window.open(href);
									e.preventDefault();
									return false;
								}
							}
						}
					});
				});
			},
			/**
			 * 欢庆佳节灯笼
			 * @param {*} option 
			 */
			lantern: (option) => {
				const startDate = new Date(option.start).getTime(),
					endDate = new Date(option.end).getTime(),
					nowDate = new Date().getTime(),
					text_1 = option.text_1 ? option.text_1 : '',
					text_2 = option.text_2 ? option.text_2 : ''
				let show = true;
				if (startDate && nowDate - startDate < 0) {
					show = false;
				}
				if (endDate && endDate - nowDate < 0) {
					show = false;
				}
				if (show) {
					const lantern1 = document.createElement('div'),
						lantern2 = document.createElement('div')
					lantern1.className = 'deng-box1'
					lantern1.innerHTML = '<div class="deng"><div class="xian"></div><div class="deng-a"><div class="deng-b"><div class="deng-t">' + text_2 + '</div></div></div><div class="shui shui-a"><div class="shui-c"></div><div class="shui-b"></div></div></div>'
					lantern2.className = 'deng-box2'
					lantern2.innerHTML = '<div class="deng"><div class="xian"></div><div class="deng-a"><div class="deng-b"><div class="deng-t">' + text_1 + '</div></div></div><div class="shui shui-a"><div class="shui-c"></div><div class="shui-b"></div></div></div>'
					document.querySelector('body').appendChild(lantern1)
					document.querySelector('body').appendChild(lantern2)
				}
			},
		}

	const run = (option) => {
		if (option.gray_page_open) fun.grayPage({ start: option.gray_page_start_time, end: option.gray_page_end_time, backgroundColor: option.gray_page_body_bg });
		window.addEventListener('load', () => {
			if (option.run_open) fun.showRun({ start: option.run_start_time, text: option.run_show_text, binding: option.run_binding_element });
			if (option.keyboard_open) fun.keyboard();
			if (option.url_open) fun.url({ source: option.url_source });
			if (option.light_box_open) fun.lightBox({ binding: option.light_box_binding });
			if (option.lantern_open) fun.lantern({
				start: option.lantern_start_time,
				end: option.lantern_end_time,
				text_1: option.lantern_text_1,
				text_2: option.lantern_text_2
			})
		});
	}

	return {
		run
	}
})();
