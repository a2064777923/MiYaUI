/**
 * 
 * 雪花飘落特效（晨风自定义）
 * 
 * 该文件不会被加载，该文件是为了方便开发使用
 * 如果您有更好的优化建议，请联系我或Gitee内提交
 * @link https://gitee.com/ouros/feng-custom
 * 
 * @author 阿锋
 * @link https://feng.pub/contact
 * 
 * NOTE: This plugin is based on the work by Jason Brown (Loktar00)
 * https://github.com/loktar00/JQuery-Snowfall
 * 
 */
if (!Date.now)
    Date.now = function() { return new Date().getTime(); };

(function() {
    'use strict';
    
    var vendors = ['webkit', 'moz'];
    for (var i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
        var vp = vendors[i];
        window.requestAnimationFrame = window[vp+'RequestAnimationFrame'];
        window.cancelAnimationFrame = (window[vp+'CancelAnimationFrame']
                                   || window[vp+'CancelRequestAnimationFrame']);
    }
    if (/iP(ad|hone|od).*OS 6/.test(window.navigator.userAgent) // iOS6 is buggy
        || !window.requestAnimationFrame || !window.cancelAnimationFrame) {
        var lastTime = 0;
        window.requestAnimationFrame = function(callback) {
            var now = Date.now();
            var nextTime = Math.max(lastTime + 16, now);
            return setTimeout(function() { callback(lastTime = nextTime); },
                              nextTime - now);
        };
        window.cancelAnimationFrame = clearTimeout;
    }
}());

var snowFall = (function(){
    function jSnow(){
        // local methods
        var defaults = {
                // flakeCount : 35,
				count : 30,
                // flakeColor : '#ffffff',
                color : '#ffffff',
                // flakeIndex: 999999,
				zIndex: 100,
                flakePosition: 'fixed',
                minSize : 1,
                maxSize : 14,
                minSpeed : 1,
                maxSpeed : 5,
                round : false,
                shadow : false,
                collection : false,
                image : false,
				text : false,
                collectionHeight : 40
            },
            flakes = [],
            element = {},
            elHeight = 0,
            elWidth = 0,
            widthOffset = 0,
            snowTimeout = 0,
            // For extending the default object with properties
            extend = function(obj, extObj){
                for(var i in extObj){
                    if(obj.hasOwnProperty(i)){
                        obj[i] = extObj[i];
                    }
                }
            },
            // For setting CSS3 transform styles
            transform = function (el, styles){
                el.style.webkitTransform = styles;
                el.style.MozTransform = styles;
                el.style.msTransform = styles;
                el.style.OTransform = styles;
                el.style.transform = styles;
            },
            // random between range
            random = function random(min, max){
				var mathRandom = Math.round((max - min) * Math.random() + min); 
				if (mathRandom == 0) {
					mathRandom = min;
				}
                return mathRandom;
            },
            // Set multiple styles at once.
            setStyle = function(element, props)
            {
                for (var property in props){
                    element.style[property] = props[property] + ((property == 'width' || property == 'height' || property == 'fontSize') ? 'px' : '');
                }
            },
            // snowflake
            flake = function(_el, _size, _speed, _color, _text, _image)
            {
                // Flake properties
                this.x  = random(widthOffset, elWidth - widthOffset);
                this.y  = random(0, elHeight);
                this.size = _size;
                this.speed = _speed;
                this.step = 0;
                this.stepSize = random(1,10) / 100;
				this.color = _color;
				this.text = _text;
				this.image = _image;

                if(defaults.collection){
                    this.target = canvasCollection[random(0,canvasCollection.length-1)];
                }
                
                var flakeObj = null;
                
                if(defaults.image){
                    flakeObj = new Image();
                    flakeObj.src = this.image;
					setStyle(flakeObj, {'width' : this.size });
                }else{
					if (defaults.text) {
						flakeObj = document.createElement('div');
						flakeObj.innerHTML = this.text;
						setStyle(flakeObj, {'color' : this.color, 'fontSize' : this.size });
					} else{
						flakeObj = document.createElement('div');
						setStyle(flakeObj, {'width' : this.size, 'height' : this.size, 'background' : this.color, 'fontSize' : 0 });
						defaults.round = true;
						defaults.shadow = true;
					}
                }
                
                flakeObj.className = 'snowfall-flakes';
                setStyle(flakeObj, {'position' : defaults.flakePosition, 'top' : 0, 'left' : 0, 'will-change': 'transform', 'zIndex' : defaults.zIndex});
        
                // This adds the style to make the snowflakes round via border radius property 
                if(defaults.round){
                    setStyle(flakeObj,{'-moz-border-radius' : ~~(defaults.maxSize) + 'px', '-webkit-border-radius' : ~~(defaults.maxSize) + 'px', 'borderRadius' : ~~(defaults.maxSize) + 'px'});
                }
            
                // This adds shadows just below the snowflake so they pop a bit on lighter colored web pages
                if(defaults.shadow){
                    setStyle(flakeObj,{'-moz-box-shadow' : '1px 1px 1px #555', '-webkit-box-shadow' : '1px 1px 1px #555', 'boxShadow' : '1px 1px 1px #555'});
                }

                if(_el.tagName === document.body.tagName){
                    document.body.appendChild(flakeObj);
                }else{
                    _el.appendChild(flakeObj);
                }

                this.element = flakeObj;
                
                // Update function, used to update the snow flakes, and checks current snowflake against bounds
                this.update = function(){
                    this.y += this.speed;
					
                    if(this.y > elHeight - (this.size  + 6)){
                        this.reset();
                    }

                    transform(this.element, 'translateY('+this.y+'px) translateX('+this.x+'px)');

                    this.step += this.stepSize;
                    this.x += Math.cos(this.step);
                    
                    if(this.x + this.size > elWidth - widthOffset || this.x < widthOffset){
                        this.reset();
                    }
                }
                
                // Resets the snowflake once it reaches one of the bounds set
                this.reset = function(){
                    this.y = 0;
                    this.x = random(widthOffset, elWidth - widthOffset);
                    this.stepSize = random(1,10) / 100;
                    this.size = random((defaults.minSize * 100), (defaults.maxSize * 100)) / 100;
                    if (defaults.image) {
						this.element.style.width = this.size + 'px';
					}else {
						if (defaults.text) {
							this.element.innerHTML = randomText(1, defaults.text);
							this.element.style.fontsize = this.size + 'px';
						}else {
							this.element.style.width = this.size + 'px';
							this.element.style.height = this.size + 'px';
						}
					}
                    this.speed = random(defaults.minSpeed, defaults.maxSpeed);
                }
            },
            // this controls flow of the updating snow
            animateSnow = function(){
                for(var i = 0; i < flakes.length; i += 1){
                    flakes[i].update();
                }
                snowTimeout = requestAnimationFrame(function(){animateSnow()});
            },
            /**
			 * 对定义的数组字符集进行随机选取
			 * @@param {Object} n 数量
			 * @param {Object} n  数组或半角逗号的字符串
			 */
			randomText = function(n, str) {
				var res = "";
				if (typeof (str) == 'string') {
					str = str.split(',');
				}
				var length = str.length;
				for (var i = 0; i < n; i++) {
					var id = Math.ceil(Math.random() * (length)) - 1;
					res += str[id];
				}
				return res;
			}
        return{
            snow : function(_element, _options){
                extend(defaults, _options);
                
                //init the element vars
                element = _element;
                elHeight = window.screen.height;
                // elWidth = window.screen.width;
				elWidth = window.innerWidth;
				

                element.snow = this;
				
				defaults.halfCount = Math.ceil(defaults.count / 2);
				defaults.allCount = defaults.count;
				// 雪花数量自适应
				element.snow.autoCount();
				
                // if this is the body the offset is a little different
                if(element.tagName.toLowerCase() === 'body'){
                    widthOffset = 25;
                }
                
                // Bind the window resize event so we can get the innerHeight again
                window.addEventListener('resize', function(){
                    elHeight = window.screen.height;
                    // elWidth = window.screen.width;
					elWidth = window.innerWidth;
					
					// 雪花数量自适应
					element.snow.autoCount();
					
					// 清理变更之前的雪花
					element.snow.clear();
					element.snow.initFlakes();
					
					animateSnow();
                }, true);
                
                element.snow.initFlakes();
				
                // start the snow
                animateSnow();
            },
			autoCount : function(){
				if (elWidth < 900) {
					// 宽度小于900雪花数量减少一半
					defaults.count = defaults.halfCount;
				}else {
					defaults.count = defaults.allCount;
				}
			},
			initFlakes : function(){
				// initialize the flakes
				for(i = 0; i < defaults.count; i+=1){
				    flakes.push(new flake(element, random((defaults.minSize * 100), (defaults.maxSize * 100)) / 100, random(defaults.minSpeed, defaults.maxSpeed), randomText(1, defaults.color), randomText(1, defaults.text), randomText(1, defaults.image)));
				}
			},
            clear : function(){
                var flakeChildren = null;

                if(!element.getElementsByClassName){
                    flakeChildren = element.querySelectorAll('.snowfall-flakes');
                }else{
                    flakeChildren = element.getElementsByClassName('snowfall-flakes');
                }

                var flakeChilLen = flakeChildren.length;
                while(flakeChilLen--){
                    if(flakeChildren[flakeChilLen].parentNode === element){
                        element.removeChild(flakeChildren[flakeChilLen]);
                    }
                }

                cancelAnimationFrame(snowTimeout);
            }
        }
    };
    return{
        snow : function(elements, options){
            if(typeof(options) == 'string'){
                if(elements.length > 0){
                    for(var i = 0; i < elements.length; i++){
                        if(elements[i].snow){
                            elements[i].snow.clear();
                        }
                    }
                }else{
                    elements.snow.clear();
                }
            }else{
                if(elements.length > 0){
                    for(var i = 0; i < elements.length; i++){
                        new jSnow().snow(elements[i], options);
                    }
                }else{
                    new jSnow().snow(elements, options);
                }
            }
        }
    }
})();

