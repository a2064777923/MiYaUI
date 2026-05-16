/**
 * 节日氛围
 * festivals.js
 * 
 * @version 0.0.6
 * 
 * @author 阿锋
 * @link https://feng.pub
 */
const fengCustomFestivals = (() => {

    const
        // 手动关闭大盒子后的有效时间，超时后再次显示（单位分钟）
        closeBoxExpire = 10,
        // 海报自动关闭倒计时（单位秒）
        autoHideSeconds = 10,
        /**
         * 工具
         */
        tool = {
            /**
             * 创建元素
             * @param {*} className 
             * @returns 
             */
            createElement: (className) => {
                const div = document.createElement('div')
                div.className = className
                return div
            },

            /**
             * 元素绑定
             * @param {string|object} parent 父节点（默认为：body）
             * @param {object} child 子节点
             */
            bindElement: (parent, child) => {
                if (parent === undefined || parent === '') parent = 'body'
                if (typeof selector === "object") {
                    parent.appendChild(child)
                } else {
                    document.querySelector(parent).appendChild(child)
                }
            },

            /**
             * 字符串转换为数组，用于分割文字
             * @param {*} text 
             * @param {*} defaultText 
             * @returns 
             */
            stringToArray: (text, defaultText = []) => {
                if (typeof text === 'string') {
                    const splitText = text.split("")
                    if (splitText.length > 0) {
                        return splitText
                    }
                }
                return defaultText
            },

            /**
             * 转换为数字，获取提前或延后的天数
             * 
             * @param String number 数字
             * @param Number defaultNum 默认数字
             * @returns 
             */
            toNumber: (number, defaultNum) => {
                if (number === "") {
                    return Number(number) ? Number(number) : defaultNum
                }
                return Number(number)
            },

            /**
             * 比较日期 
             * 
             * @param {*} data 数据 month, day, isLunar, advanceDays, delayDays
             * @param {*} callback 执行函数
             */
            in: (data = {}, callback) => {
                const { month, day, isLunar, advanceDays, delayDays } = { ...data }
                // 获取当前时间
                const currentDate = Solar.fromDate(new Date()),
                    currentYear = currentDate.getYear(),
                    currentMonth = currentDate.getMonth()
                let year = currentYear
                if (currentMonth > month) {
                    year = currentYear + 1
                }
                const currentDateLunar = currentDate.getLunar()
                const lunarYear = currentDateLunar.getYear()
                const date = isLunar === true ? Lunar.fromYmd(lunarYear, month, day).getSolar() : Solar.fromYmd(year, month, day)

                // 开始日期
                const startDate = date.next(-advanceDays)

                // 结束日期
                const endDate = date.next(delayDays)

                if (currentDate.isAfter(startDate) && currentDate.isBefore(endDate)) {
                    if (callback !== undefined) {
                        callback()
                    }
                    return true
                } else {
                    return false
                }

            },

            /**
             * 判断当前日期是否在该范围内
             * @param {*} start 开始日期
             * @param {*} end 结束日期
             * @param {*} params 其他参数
             */
            between: (start, end, params) => {
                const { isLunar } = { ...params }
                const
                    currentDate = new Date(),
                    currentLunar = Lunar.fromDate(currentDate),
                    currentSolar = Solar.fromDate(currentDate),
                    // 重新计算时间
                    afresh = (params) => {
                        let { start, end, currentYear, currentMonth, currentDay, startYear, startMonth, startDay, endYear, endMonth, endDay } = { ...params }
                        if (start.year === undefined && end.year === undefined && startMonth > endMonth) {
                            endYear = startYear + 1
                        }

                        return { startYear, startMonth, startDay, endYear, endMonth, endDay }
                    }

                let startDateSolar, startYear, startMonth, startDay,
                    endDateSolar, endYear, endMonth, endDay
                if (isLunar === true) {
                    // 获取当前月的天数

                    // 阴历
                    startYear = start.year !== undefined ? start.year : currentLunar.getYear()
                    startMonth = start.month !== undefined ? start.month : currentLunar.getMonth()
                    startDay = start.day !== undefined ? start.day : 1
                    // 获取开始月的天数
                    const startMonthDays = LunarMonth.fromYm(startYear, startMonth).getDayCount()
                    startDay = startDay > startMonthDays ? startMonthDays : startDay

                    endYear = end.year !== undefined ? end.year : currentLunar.getYear()
                    endMonth = end.month !== undefined ? end.month : currentLunar.getMonth()
                    // 获取结束月的天数
                    const endMonthDays = LunarMonth.fromYm(endYear, endMonth).getDayCount()
                    endDay = end.day !== undefined && end.day <= endMonthDays ? end.day : endMonthDays

                    const
                        currentYear = currentLunar.getYear(),
                        currentMonth = currentLunar.getMonth(),
                        currentDay = currentLunar.getDay(),
                        date = afresh({ start, end, currentYear, currentMonth, currentDay, startYear, startMonth, startDay, endYear, endMonth, endDay })

                    startDateSolar = Lunar.fromYmd(date.startYear, date.startMonth, date.startDay).getSolar()
                    endDateSolar = Lunar.fromYmd(date.endYear, date.endMonth, date.endDay).getSolar()
                } else {
                    // 阳历
                    startYear = start.year !== undefined ? start.year : currentSolar.getYear()
                    startMonth = start.month !== undefined ? start.month : currentSolar.getMonth()
                    startDay = start.day !== undefined ? start.day : 1
                    // 获取开始月的天数
                    const startMonthDays = SolarUtil.getDaysOfMonth(startYear, startMonth)
                    startDay = startDay > startMonthDays ? startMonthDays : startDay

                    endYear = end.year !== undefined ? end.year : currentSolar.getYear()
                    endMonth = end.month !== undefined ? end.month : currentSolar.getMonth()
                    // 获取结束月的天数
                    const endMonthDays = SolarUtil.getDaysOfMonth(startYear, startMonth)
                    endDay = end.day !== undefined && end.day <= endMonthDays ? end.day : endMonthDays

                    const
                        currentYear = currentLunar.getYear(),
                        currentMonth = currentLunar.getMonth(),
                        currentDay = currentLunar.getDay(),
                        date = afresh({ start, end, currentYear, currentMonth, currentDay, startYear, startMonth, startDay, endYear, endMonth, endDay })

                    startDateSolar = Solar.fromYmd(date.startYear, date.startMonth, date.startDay)
                    endDateSolar = Solar.fromYmd(date.endYear, date.endMonth, date.endDay)
                }

                return currentSolar.isAfter(startDateSolar) && currentSolar.isBefore(endDateSolar)
            },

            /**
             * 创建盒子
             * @param string param
             * @returns 
             */
            createDiv: (param, bind) => {
                const div = document.createElement('div')
                if (param.indexOf('.') !== -1) {
                    div.className = param.substring(1)
                }
                if (param.indexOf('#') !== -1) {
                    div.setAttribute("id", param.substring(1))
                }
                if (typeof bind === 'object') {
                    bind.appendChild(div)
                } else {
                    if (bind === undefined || bind === '') bind = 'body'
                    document.querySelector(bind).appendChild(div)
                }
                return div
            },

            /**
             * 补零
             * @param {*} num 
             * @param {*} digits 
             */
            numZeor: (num, digits) => {
                return num.toString().padStart(digits, '0')
            },

            /**
             * 创建元素
             * @param {*} params 
             * @param {*} bind 
             * @param {*} num 
             * @returns 
             */
            buildElement: (params, bind, num) => {
                if (num) {
                    let objects = []
                    for (let index = 0; index < num; index++) {
                        objects[index] = tool.buildElement(params, bind)
                    }
                    return objects
                }

                let elementObj
                if (typeof params === 'string') {
                    if (params.indexOf('.') !== -1) {
                        elementObj = document.createElement('div')
                        elementObj.className = params.substring(1)
                    }
                    if (params.indexOf('#') !== -1) {
                        elementObj = document.createElement('div')
                        elementObj.setAttribute("id", params.substring(1))
                    }
                    if (!elementObj) {
                        elementObj = document.createElement(params)
                    }
                } else {
                    const { tagName, selector, idName, className } = { ...params }
                    elementObj = tagName ? document.createElement(tagName) : document.createElement('div')

                    if (selector) {
                        if (selector.indexOf('.') !== -1) {
                            elementObj.className = selector.substring(1)
                        }
                        if (selector.indexOf('#') !== -1) {
                            elementObj.setAttribute("id", selector.substring(1))
                        }
                    }
                    if (idName) {
                        elementObj.setAttribute("id", idName)
                    }
                    if (className) {
                        elementObj.className = className
                    }
                }

                if (typeof bind === 'object') {
                    bind.appendChild(elementObj)
                } else {
                    if (bind !== undefined) {
                        if (bind === '') bind = 'body'
                        document.querySelector(bind).appendChild(elementObj)
                    }
                }
                return elementObj
            },

            /**
             * 创建挂件
             * @param {*} element 
             * @param {*} bind 
             * @param {*} params 
             * @returns 
             */
            buildBox: (element, bind, params) => {
                let
                    idName,
                    className
                if (typeof element === 'string') {
                    if (element.indexOf('.') !== -1) {
                        className = element.substring(1)
                    }
                    if (element.indexOf('#') !== -1) {
                        idName = element.substring(1)
                    }
                } else {
                    idName = element.idName
                    className = element.className
                }

                const { type, showControl, controlPosition, loadTips, tipsStyle, autoHide, fadein } = { ...params }
                // 挂件
                let
                    clickTimes = 0,
                    tipsStatus = 0,
                    tips,
                    waitAutoHide = false,
                    closeBtnIntervalId

                if ((autoHide !== undefined)) {
                    if (typeof autoHide === "number") {
                        waitAutoHide = autoHide
                    } else if (autoHide) {
                        waitAutoHide = autoHideSeconds
                    }
                }

                const
                    parentClassName = type === "fullscreen" ? ".feng-custom-fullscreen" : ".feng-custom-pendant",
                    parent = tool.buildElement(parentClassName, bind),
                    box = tool.buildElement('.box', parent),
                    // 默认连续点击2次关闭盒子
                    clickTimesToClose = 2,
                    loadTipsBox = () => {
                        tips = tool.buildElement(".tips", parent)
                        tips.innerHTML = "再点一次，我将消失"
                        if (tipsStyle !== undefined) {
                            setTipsStyle(tipsStyle)
                        }
                        let setTimeoutID
                        box.addEventListener('click', () => {
                            setTimeoutID ? clearTimeout(setTimeoutID) : false
                            clickTimes > clickTimesToClose ? clickTimes = 1 : clickTimes++
                            if (clickTimes === clickTimesToClose - 1) {
                                showTipsBox()
                            }
                            if (clickTimes === clickTimesToClose) {
                                closeBox()
                                return
                            }
                            setTimeoutID = setTimeout(() => {
                                if (tipsStatus === 0 && clickTimes >= clickTimesToClose) {
                                    closeBox()
                                } else {
                                    clickTimes = 0
                                    hideTipsBox()
                                }
                            }, 2000);
                        })
                    },
                    showTipsBox = () => {
                        tips.style.filter = "opacity(1)"
                        tipsStatus = 1
                    },
                    hideTipsBox = () => {
                        if (tips === undefined) return
                        tips.style.filter = "opacity(0)"
                        tipsStatus = 0
                    },
                    setTipsStyle = (style) => {
                        Object.assign(tips.style, style)
                    },
                    loadControlBox = () => {
                        const
                            controlBox = tool.buildElement(".control", parent),
                            groupBox = tool.buildElement(".group", controlBox),
                            closeBtn = tool.buildElement(".close", groupBox)

                        if (waitAutoHide) {
                            closeBtn.classList.add("close-countdown")
                            let closeCountdown = waitAutoHide - 1
                            const
                                closeCountdownBox = tool.buildElement('span', closeBtn),
                                closeTextBox = tool.buildElement('span', closeBtn)
                            closeCountdownBox.innerHTML = closeCountdown + "s｜"
                            closeTextBox.innerHTML = "关闭"
                            closeBtnIntervalId = setInterval(() => {
                                closeCountdown--
                                if (closeCountdown == 0) {
                                    closeCountdownBox.style.display = "none"
                                    clearInterval(closeBtnIntervalId)
                                } else {
                                    closeCountdownBox.innerHTML = closeCountdown + "s｜"
                                }
                            }, 1000);
                        } else {
                            tool.buildElement('i', closeBtn)
                        }


                        closeBtn.addEventListener('click', () => {
                            closeBox()
                        })

                        if (controlPosition !== undefined) {
                            controlBox.classList.add("control-" + controlPosition.replace(" ", "-"))
                        } else {
                            if (type === "fullscreen") {
                                controlBox.classList.add("control-top-right")
                            } else {
                                controlBox.classList.add("control-left")
                            }
                        }
                    },
                    // 显示盒子
                    showBox = () => {
                        parent.style.display = "block"
                        parent.style.filter = "opacity(1)"
                    },
                    // 隐藏盒子
                    hideBox = () => {
                        clickTimes = 0
                        hideTipsBox()
                        parent.style.filter = "opacity(0)"
                        // 定时器时间设定要与CSS动画时间保持一致
                        setTimeout(() => {
                            parent.style.display = "none"
                        }, 1000)
                    },
                    // 关闭盒子
                    closeBox = () => {
                        hideBox()
                        // 记录当前关闭的时间
                        const closeTime = new Date(),
                            className = typeof element === "string" ? element : element.className
                        let closeData = JSON.parse(localStorage.getItem("feng-custom-effects"))
                        if (closeData && typeof closeData === "object" && Array.isArray(closeData)) {
                            closeData = closeData.filter((item, index) => {
                                return item.element !== className
                            })
                            closeData.push({ element: className, closeTime: closeTime })
                        } else {
                            closeData = [{ element: className, closeTime: closeTime }]
                        }
                        localStorage.setItem("feng-custom-effects", JSON.stringify(closeData))
                    }

                if (idName !== undefined) {
                    parent.setAttribute("id", idName)
                }

                if (className !== undefined) {
                    parent.classList.add(className)
                }

                if (type === 'fullscreen') {
                    // 默认不加载tips
                    if (loadTips) loadTipsBox()
                } else {
                    // 默认加载tips
                    if (loadTips === undefined || loadTips) loadTipsBox()
                }

                // 加载关闭按钮
                if (showControl === undefined || showControl) loadControlBox()

                // 自动隐藏
                if (autoHide !== undefined && autoHide) {
                    setTimeout(() => {
                        hideBox()
                    }, (autoHide === true ? autoHideSeconds : autoHide) * 1000)
                }

                box.setPosition = (position) => {
                    tool.setPosition(position, parent)
                }
                box.setTipsStyle = (style) => {
                    setTipsStyle(style)
                }
                box.hideBox = () => {
                    hideBox()
                }
                return box
            },

            /**
             * 检查是否允许创建特效
             * @param {*} element 
             * @returns 
             */
            allowBuild: (elementName) => {
                const expire = closeBoxExpire * 60 * 1000 // 5分钟 5 * 60 * 1000
                let closeData = JSON.parse(localStorage.getItem("feng-custom-effects"))
                if (closeData && typeof closeData === "object" && Array.isArray(closeData)) {
                    for (let index = 0; index < closeData.length; index++) {
                        const item = closeData[index];
                        if (item.element == elementName) {
                            // 判断时间
                            return Math.abs(new Date() - new Date(item.closeTime)) >= expire
                        }
                    }
                }
                return true
            },

            /**
             * 设置缩放
             * @param {*} params 
             * @param {*} elementObj 
             */
            setScale: (scale, elementObj) => {
                elementObj.style.transform = 'scale(' + scale + ')'
            },

            /**
             * 设置位置
             * @param {*} position 
             */
            setPosition: (position, elementObj) => {
                let { top, right, bottom, left } = { ...position }

                if (Array.isArray(position) && top === undefined && left === undefined && bottom === undefined && right === undefined) {
                    top = position[0] !== undefined && position[0] ? position[0] : undefined
                    right = position[1] !== undefined && position[1] ? position[1] : undefined
                    bottom = position[2] !== undefined && position[2] ? position[2] : undefined
                    left = position[3] !== undefined && position[3] ? position[3] : undefined
                }

                if (top === undefined && left === undefined && bottom === undefined && right === undefined) {
                    top = "50px"
                    right = "50px"
                }

                if (top !== undefined) elementObj.style.top = top
                if (right !== undefined) elementObj.style.right = right
                if (bottom !== undefined) elementObj.style.bottom = bottom
                if (left !== undefined) elementObj.style.left = left
            },

            /**
             * 设置倒计时
             * @param {*} params 
             */
            setCountdown: (params, callback) => {
                const { year, month, day, isLunar } = { ...params }
                let
                    currentDate,
                    currentSolar,
                    currentLunar,
                    currentYear,
                    currentMonth,
                    currentDay,
                    toYear = year,
                    toMonth = month,
                    toDay = day,
                    date = {},
                    resetCurrentDate = () => {
                        currentDate = new Date()
                        currentSolar = Solar.fromDate(new Date())
                        currentLunar = Lunar.fromDate(new Date())
                        if (toYear === undefined) {
                            toYear = currentYear = isLunar === true ? currentLunar.getYear() : currentSolar.getYear()
                            currentMonth = isLunar === true ? currentLunar.getMonth() : currentSolar.getMonth()
                            currentDay = isLunar === true ? currentLunar.getDay() : currentSolar.getDay()
                            if (currentMonth > toMonth) {
                                toYear = currentYear + 1
                            }
                        }

                        // 实例化为阳历
                        const toDate = isLunar === true ? Lunar.fromYmd(toYear, toMonth, toDay).getSolar() : Solar.fromYmd(toYear, toMonth, toDay),
                            currentDateObj = isLunar === true ? currentLunar.getSolar() : currentDate()

                        date.days = toDate.subtract(currentDateObj)
                        date.minutes = toDate.subtractMinute(currentDateObj)

                        // countdownYears = toDate.subtractYear(currentDateObj)
                        // countdownMonths = toDate.subtractMonth(currentDateObj)
                        date.hours = Math.ceil(date.minutes / 60)

                        date.hour = Math.floor(date.minutes / 60) % 24
                        date.minute = date.minutes % 60
                        date.second = 60 - currentDate.getSeconds()

                        date.currentDate = currentDate
                        date.currentLunar = currentLunar
                        date.currentSolar = currentSolar
                    }

                resetCurrentDate()
                if (callback !== undefined) callback(date)

                let intervalSecond = currentDate.getSeconds()
                // 1秒执行一次
                const interval = setInterval(() => {
                    if (date.minutes <= 1) {
                        if (date.second === 1) {
                            resetCurrentDate()
                            date.second = 0
                        } else if (date.second <= 15) {
                            currentDate = new Date()
                            const currentSecond = currentDate.getSeconds()
                            date.second = currentSecond ? 60 - currentSecond : 0
                        } else {
                            date.second--
                        }

                        if (callback !== undefined) callback(date)
                        if (date.second === 0) clearInterval(interval)
                        intervalSecond = 0
                    } else {
                        if (intervalSecond >= 60) {
                            resetCurrentDate()
                            if (callback !== undefined) callback(date)
                            intervalSecond = 0
                        }
                    }
                    intervalSecond++
                }, 1000);

                return {
                    interval
                }
            },

        },
        /*
         * 特效元素
         */
        element = {
            /**
             * 满月
             * @param {*} params 
             */
            fullMood: (params) => {

            },
            /**
             * 灯笼
             * @param {*} params 
             */
            lantern: (params) => {
                const { className, elementID, bindElement, light, text, position, top, left, bottom, right, scale, fontSize, lineHeight, } = { ...params }

                const lanternObject = {
                    // 创建灯笼主体
                    createBodyElement: (text) => {
                        const body = tool.createElement('body')
                        body.appendChild(tool.createElement('cadre'))
                        body.appendChild(tool.createElement('cadre'))
                        const textEle = tool.createElement('text')
                        if (text !== undefined) textEle.appendChild(document.createTextNode(text))
                        if (fontSize !== undefined) textEle.style.fontSize = fontSize
                        if (lineHeight !== undefined) textEle.style.lineHeight = lineHeight;
                        // (light === undefined || light === 'none' || light === '') ? body.style.boxShadow = 'none' : body.style.boxShadow = '0 0 30px #ffea00'
                        body.appendChild(textEle)
                        return body
                    },
                    // 创建灯笼底部
                    createBottomElement: () => {
                        const bottom = tool.createElement('bottom')
                        bottom.appendChild(tool.createElement('bottom_bg'))
                        bottom.appendChild(tool.createElement('bottom_line'))
                        bottom.appendChild(tool.createElement('bottom_line'))
                        bottom.appendChild(tool.createElement('bottom_line'))
                        bottom.appendChild(tool.createElement('bottom_line'))
                        return bottom
                    }
                }
                // 灯笼大盒子
                const lantern = tool.createElement('feng_custom_lantern')
                if (className !== undefined) lantern.classList.add(className)
                if (elementID !== undefined) lantern.setAttribute('id', elementID)
                if (position !== undefined) lantern.style.position = position

                const lanternBox = tool.createElement('lantern_box')
                lanternBox.appendChild(tool.createElement('line'))
                lanternBox.appendChild(tool.createElement('ring'))
                lanternBox.appendChild(tool.createElement('cap'))
                lanternBox.appendChild(lanternObject.createBottomElement())
                lanternBox.appendChild(tool.createElement('bottom_cap'))
                lanternBox.appendChild(lanternObject.createBodyElement(text))

                lantern.appendChild(lanternBox)

                //设置位置
                if (top !== undefined) lantern.style.top = top
                if (left !== undefined) lantern.style.left = left
                if (bottom !== undefined) lantern.style.bottom = bottom
                if (right !== undefined) lantern.style.right = right

                // 缩放大小
                if (scale !== undefined) lantern.style.transform = 'scale(' + scale + ')'

                if (bindElement !== undefined) {
                    tool.bindElement(bindElement, lantern)
                }

                return lantern
            },
            /**
             * 中国结
             * @param {*} params 
             */
            chineseKnot: (params) => {
                const box = tool.buildElement('.feng-custom-chinese-knot'),
                    head = tool.buildElement('.head', box),
                    body = tool.buildElement('.body', box),
                    cross = tool.buildElement('.cross', body),
                    circle = tool.buildElement('.circle', body),
                    circleMedium = tool.buildElement('.circle-m', body),
                    circleLarge = tool.buildElement('.circle-l', body),
                    foot = tool.buildElement('.foot', box),
                    ring = tool.buildElement('.ring', box)

                tool.buildElement('.hang', head)

                tool.buildElement('div', cross, 2)

                tool.buildElement('div', circleMedium, 2)

                tool.buildElement('div', circleLarge, 2)

                tool.buildElement('i', circle, 10)

                tool.buildElement('div', ring, 4)

                tool.buildElement('.line', foot)

                const ball = tool.buildElement('.ball', foot)
                tool.buildElement('div', ball, 2)

                tool.buildElement('.tassel', foot)

                tool.setFace(params, box)

                return box

            },
            /**
             * 中国福
             * @param {*} params 
             */
            chineseFortune: (params) => {
                const { year } = { ...params }
                const box = tool.buildElement('.feng-custom-chinese-fortune'),
                    bg = tool.buildElement('.bg', box),
                    text = tool.buildElement('.text', box),
                    dotArr = new Array()

                for (let index = 0; index < 4; index++) {
                    const dot = tool.buildElement('.dot', bg)
                    dotArr.push(dot)
                    tool.buildElement('span', dot, 2)
                }

                tool.buildElement('.fu', text).innerHTML = "福"
                if (year !== undefined) {
                    tool.buildElement('.year', text).innerHTML = year
                    dotArr[0].querySelector('span').style.display = 'none'
                }

                if (params.position) tool.setPosition(params.position, box)
                if (params.scale) tool.setScale(params.scale, box)
                return box
            },
        },
        /**
         * 构建满月
         */
        buildFullMood = () => {
        },
        /**
         * 构建灯笼
         */
        buildLantern = (data = {}) => {
            const { className, elementID, bindElement, light, text, position, top, left, bottom, right, scale, fontSize, lineHeight, } = { ...data }

            const lanternObject = {
                // 创建灯笼主体
                createBodyElement: (text) => {
                    const body = tool.createElement('body')
                    body.appendChild(tool.createElement('cadre'))
                    body.appendChild(tool.createElement('cadre'))
                    const textEle = tool.createElement('text')
                    if (text !== undefined) textEle.appendChild(document.createTextNode(text))
                    if (fontSize !== undefined) textEle.style.fontSize = fontSize
                    if (lineHeight !== undefined) textEle.style.lineHeight = lineHeight;
                    (light === undefined || light === 'none' || light === '') ? body.style.boxShadow = 'none' : body.style.boxShadow = '0 0 30px #ffea00'
                    body.appendChild(textEle)
                    return body
                },
                // 创建灯笼底部
                createBottomElement: () => {
                    const bottom = tool.createElement('bottom')
                    bottom.appendChild(tool.createElement('bottom_bg'))
                    bottom.appendChild(tool.createElement('bottom_line'))
                    bottom.appendChild(tool.createElement('bottom_line'))
                    bottom.appendChild(tool.createElement('bottom_line'))
                    bottom.appendChild(tool.createElement('bottom_line'))
                    return bottom
                }
            }
            // 灯笼大盒子
            const lantern = tool.createElement('feng_custom_lantern')
            if (className !== undefined) lantern.classList.add(className)
            if (elementID !== undefined) lantern.setAttribute('id', elementID)
            if (position !== undefined) lantern.style.position = position

            const lanternBox = tool.createElement('lantern_box')
            lanternBox.appendChild(tool.createElement('line'))
            lanternBox.appendChild(tool.createElement('ring'))
            lanternBox.appendChild(tool.createElement('cap'))
            lanternBox.appendChild(lanternObject.createBottomElement())
            lanternBox.appendChild(tool.createElement('bottom_cap'))
            lanternBox.appendChild(lanternObject.createBodyElement(text))

            lantern.appendChild(lanternBox)

            //设置位置
            if (top !== undefined) lantern.style.top = top
            if (left !== undefined) lantern.style.left = left
            if (bottom !== undefined) lantern.style.bottom = bottom
            if (right !== undefined) lantern.style.right = right

            // 缩放大小
            if (scale !== undefined) lantern.style.transform = 'scale(' + scale + ')'

            tool.bindElement(bindElement, lantern)
        },
        effect = {
            /**
             * 中秋
             * @param {*} option 
             */
            midAutumn: (option = {}) => {
                tool.in({
                    month: 8,
                    day: 15,
                    isLunar: true,
                    advanceDays: tool.toNumber(option.mid_autumn_advance, 3),
                    delayDays: tool.toNumber(option.mid_autumn_delay, 1)
                    // advanceDays: Number(option.mid_autumn_advance) ? Number(option.mid_autumn_advance) : 3,
                    // delayDays: Number(option.mid_autumn_delay) ? Number(option.mid_autumn_delay) : 1
                }, () => {
                    const moonEle = tool.createElement('feng_custom_full_moon moon')
                    const date = tool.createElement('date')
                    date.appendChild(document.createTextNode('八月十五'))
                    moonEle.appendChild(date)
                    const textEle = tool.createElement('text')
                    const showText = tool.stringToArray(option.mid_autumn_text, ['中', '秋', '快', '乐'])
                    showText.forEach((value) => {
                        const span = document.createElement('span')
                        span.appendChild(document.createTextNode(value))
                        textEle.appendChild(span)
                    })
                    moonEle.appendChild(textEle)

                    const midAutumnEle = tool.createElement('feng_custom_mid_autumn')
                    midAutumnEle.appendChild(moonEle)

                    tool.bindElement('body', midAutumnEle)
                })
            },
            /**
             * 国庆
             * @param {*} option
             */
            nationalDay: (option = {}) => {
                tool.in({
                    month: 10,
                    day: 1,
                    advanceDays: tool.toNumber(option.national_day_advance, 1),
                    delayDays: tool.toNumber(option.national_day_delay, 6),
                }, () => {
                    const showText = tool.stringToArray(option.national_day_text, ['欢', '度', '国', '庆'])
                    // 创建大盒子
                    const bigBox = tool.createElement('feng_custom_national_day')
                    tool.bindElement('body', bigBox)
                    buildLantern({ bindElement: '.feng_custom_national_day', text: showText[0], light: '1', top: '0', left: '2%' })
                    buildLantern({ bindElement: '.feng_custom_national_day', text: showText[1], light: '1', top: '0', left: '12%' })
                    buildLantern({ bindElement: '.feng_custom_national_day', text: showText[2], light: '1', top: '0', right: '12%' })
                    buildLantern({ bindElement: '.feng_custom_national_day', text: showText[3], light: '1', top: '0', right: '2%' })
                })
            },
            /**
             * 元旦
             * @param {} option 
             */
            newYear: (option) => {
                const { advance, delay, text, position } = { ...option }
                tool.in({
                    month: 1,
                    day: 1,
                    advanceDays: tool.toNumber(advance, 1),
                    delayDays: tool.toNumber(delay, 1),
                }, () => {
                    // 创建大盒子
                    const bigBox = tool.createElement('feng_custom_new_year')
                    tool.bindElement('body', bigBox)
                    const textBox = tool.createElement('text')
                    textBox.innerHTML = text ? text : "元旦快乐"
                    tool.bindElement('.feng_custom_new_year', textBox)

                    buildLantern({ bindElement: '.feng_custom_new_year', light: '1', bottom: '80px', left: '-63px', scale: 0.7 })
                    buildLantern({ bindElement: '.feng_custom_new_year', light: '1', top: '0', right: '-36px', scale: 0.5 })
                    //设置位置
                    let { top, right, bottom, left } = { ...position }
                    if (!top && !right && !bottom && !left) {
                        top = '90px'
                        right = '5%'
                    }
                    if (top) bigBox.style.top = top
                    if (right) bigBox.style.right = right
                    if (bottom) bigBox.style.bottom = bottom
                    if (left) bigBox.style.left = left

                    const closeBtn = tool.createElement('close')
                    closeBtn.innerHTML = "关闭"
                    closeBtn.setAttribute('title', '点击关闭元旦节日挂件')
                    tool.bindElement('.feng_custom_new_year', closeBtn)
                    // 事件
                    closeBtn.addEventListener('click', () => {
                        bigBox.style.display = "none"
                    })

                })
            },

            /**
             * 春节
             * @param {*} params 
             */
            springFestival: (params) => {
                const
                    {
                        openCountdown,
                        openPoster,
                        openPendant,
                        pendantPosition,
                        countdownImage,
                        pendantImage,
                        posterImage,
                    } = { ...params },
                    month = params.month === undefined ? 1 : params.month,
                    day = params.day === undefined ? 1 : params.day,
                    startMonth = params.startMonth === undefined ? 12 : params.startMonth,
                    startDay = params.startDay === undefined ? 22 : params.startDay,
                    endMonth = params.endMonth === undefined ? 1 : params.endMonth,
                    endDay = params.endDay === undefined ? 10 : (+params.endDay + 1),
                    isLunar = true,
                    posterName = "feng-custom-spring-festival-poster",
                    pendatName = "feng-custom-spring-festival-pendant",
                    countdownName = "feng-custom-spring-festival-countdown",
                    loadPoster = () => {
                        if (tool.allowBuild(posterName) === false) return
                        // 海报
                        const
                            currentLunar = Lunar.fromDate(new Date),
                            festivals = currentLunar.getFestivals(),
                            box = tool.buildBox({ className: posterName }, 'body', { type: "fullscreen", autoHide: true, controlPosition: "top-right" }),
                            topBox = tool.buildElement('.top-box', box),
                            bottomBox = tool.buildElement('.bottom-box', box)

                        box.appendChild(element.lantern({}))

                        if (posterImage !== undefined && posterImage) {
                            box.style.backgroundImage = "url(" + posterImage + ")"
                        } else {
                            box.appendChild(element.chineseFortune({ year: currentLunar.getYear() }))
                        }

                        tool.buildElement(".text", topBox).innerHTML = festivals.length >= 1 ? festivals[0] : currentLunar.getDayInChinese()
                        tool.buildElement(".text", bottomBox).innerHTML = currentLunar.getYearShengXiao() + "年大吉"
                        tool.buildElement(".foot-box", box).innerHTML = currentLunar.getYearInChinese() + "年 · " + currentLunar.getMonthInChinese() + "月 · " + currentLunar.getDayInChinese()

                    },
                    loadPendant = () => {
                        if (tool.allowBuild(pendatName) === false) return
                        // 挂件
                        const currentLunar = Lunar.fromDate(new Date),
                            festivals = currentLunar.getFestivals(),
                            box = tool.buildBox({ className: pendatName }, "body", { controlPosition: "right" })

                        if (pendantImage !== undefined && pendantImage) {
                            const imageBox = tool.buildElement(".image", box)
                            tool.buildElement(".bg", imageBox).style.backgroundImage = "url(" + pendantImage + ")"
                        } else {
                            box.appendChild(element.chineseFortune({}))
                        }

                        box.appendChild(element.lantern({}))

                        tool.buildElement(".lunar", box).innerHTML = festivals.length >= 1 ? festivals[0] : currentLunar.getDayInChinese()
                        tool.buildElement(".wish", box).innerHTML = currentLunar.getYearShengXiao() + "年大吉"

                        box.setPosition(pendantPosition)
                    },
                    loadCountdown = () => {
                        if (tool.allowBuild(countdownName) === false) return

                        const
                            box = tool.buildBox({ className: countdownName }, 'body', { controlPosition: "right", tipsStyle: {} }),
                            countdownBox = tool.buildElement('.countdown', box),
                            calendarBox = tool.buildElement('.calendar', box),
                            lunarBox = tool.buildElement('.lunar', calendarBox),
                            monthBox = tool.buildElement('.month', calendarBox),
                            dayBox = tool.buildElement('.day', calendarBox),
                            dateBox = tool.buildElement('.date', calendarBox),
                            imageBox = tool.buildElement('.image', countdownBox),
                            focusBox = tool.buildElement('.focus', countdownBox),
                            countdownTextBox = tool.buildElement('.text', countdownBox),
                            focusNumBox = tool.buildElement('span', focusBox),
                            focusUnitBox = tool.buildElement('i', focusBox),
                            focusTextBox = tool.buildElement('p', focusBox),
                            resetcalendar = (e) => {
                                const { currentDate, currentSolar, currentLunar } = { ...e }
                                monthBox.innerHTML = "<i>「 " + currentLunar.getMonthInChinese() + " 月 」</i>"
                                const festivals = currentLunar.getFestivals()
                                dayBox.innerHTML = festivals.length >= 1 ? "<i>" + festivals[0] + "</i>" : dayBox.innerHTML = "<i>" + currentLunar.getDayInChinese() + "</i>"

                                dateBox.innerHTML = currentSolar.toString() + " <i>星期" + currentSolar.getWeekInChinese() + "</i>"
                            }

                        countdownTextBox.innerHTML = "春节倒计时"
                        tool.setCountdown({ month, day, isLunar }, (e) => {
                            const { currentDate, currentSolar, currentLunar, days, hours, minutes, second } = { ...e }
                            resetcalendar({ currentDate, currentSolar, currentLunar })
                            if (days > 1) {
                                focusNumBox.innerHTML = days
                                focusUnitBox.innerHTML = "天"
                            } else if (hours > 1) {
                                focusNumBox.innerHTML = hours
                                focusUnitBox.innerHTML = "小时"
                            } else if (minutes > 1) {
                                focusNumBox.innerHTML = minutes
                                focusUnitBox.innerHTML = "分钟"
                            } else {
                                focusNumBox.innerHTML = ""
                                focusUnitBox.innerHTML = ""
                                if (second > 50) {
                                    focusTextBox.innerHTML = "最后<br/>1 分钟"
                                } else if (second > 40) {
                                    focusTextBox.innerHTML = "马上是<br />新年"
                                } else if (second > 37) {
                                    focusTextBox.innerHTML = "新年<br />好运来"
                                } else if (second > 34) {
                                    focusTextBox.innerHTML = "叠个<br />千纸鹤"
                                } else if (second > 31) {
                                    focusTextBox.innerHTML = "再系个<br />红飘带"
                                } else if (second > 28) {
                                    focusTextBox.innerHTML = "愿善良<br />的人们"
                                } else if (second > 25) {
                                    focusTextBox.innerHTML = "天天<br />好运来"
                                } else if (second > 15) {
                                    focusTextBox.innerHTML = "最后<br />" + second + " 秒"
                                } else if (second > 10) {
                                    focusTextBox.innerHTML = "即　将<br/>倒计时"
                                } else {
                                    focusBox.style.scale = "1.5"
                                    focusTextBox.innerHTML = ""
                                    focusNumBox.innerHTML = second
                                    focusUnitBox.innerHTML = "秒"
                                    if (second == 0) {
                                        // 倒计时结束 
                                        setTimeout(() => {
                                            box.hideBox()
                                            loadPendant()
                                        }, 500)
                                    }
                                }
                            }
                        })

                        if (countdownImage !== undefined && countdownImage) {
                            imageBox.style.backgroundImage = "url(" + countdownImage + ")"
                        } else {
                            imageBox.innerHTML = "春"
                        }
                        imageBox.appendChild(element.lantern({}))
                        box.setPosition(pendantPosition)
                    }

                if (tool.between({ month, day }, { month: endMonth, day: endDay }, { isLunar }) === true) {
                    if (openPoster === undefined || openPoster) loadPoster()
                    if (openPendant === undefined || openPendant) loadPendant()
                }

                if (openCountdown === undefined || openCountdown) {
                    if (tool.between({ month: startMonth, day: startDay }, { month, day }, { isLunar }) === true) loadCountdown()
                } else {
                    if (tool.between({ month: startMonth, day: startDay }, { month, day }, { isLunar }) === true) {
                        if (openPoster === undefined || openPoster) loadPoster()
                        if (openPendant === undefined || openPendant) loadPendant()
                    }
                }

                return {
                    loadPoster,
                    loadPendant,
                    loadCountdown,
                }
            },
        },
        run = (option) => {
            window.addEventListener('DOMContentLoaded', () => {
                if (option.new_year_open) {
                    effect.newYear({
                        advance: option.new_year_advance,
                        delay: option.new_year_delay,
                        text: option.new_year_text,
                        position: option.new_year_position,
                    })
                }
                if (option.spring_festival_open) effect.springFestival({
                    startMonth: option.spring_festival_open_range.start.month,
                    startDay: option.spring_festival_open_range.start.day,
                    endMonth: option.spring_festival_open_range.end.month,
                    endDay: option.spring_festival_open_range.end.day,
                    openCountdown: option.spring_festival_open_countdown,
                    openPoster: option.spring_festival_open_poster,
                    openPendant: option.spring_festival_open_pendant,
                    pendantPosition: option.spring_festival_position,
                    countdownImage: option.spring_festival_countdown_image,
                    pendantImage: option.spring_festival_pendant_image,
                    posterImage: option.spring_festival_poster_image,
                })
                if (option.mid_autumn_open) effect.midAutumn(option)
                if (option.national_day_open) effect.nationalDay(option)

            });
        }

    try {
        console.log("【节日特效 - WordPress插件】\nhttps://feng.pub/topic/feng-custom　\n\n【节日特效 - 静态JS/CSS文件】\nhttps://gitee.com/ouros/feng-custom/tree/master/static")
    } catch (e) { }

    return {
        // 配置并运行
        run,
        // 节日特效
        effect,
        // 特效元素
        element
    }

})();