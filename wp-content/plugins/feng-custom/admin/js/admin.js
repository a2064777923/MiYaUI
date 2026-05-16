window.addEventListener('load', () => {
    let
        // 总计时
        refreshAllInterval,
        refreshTotalSecond = 0,
        refreshAllIndex = 0

    const
        // 一键更新按钮
        refreshAllBtn = document.querySelector("[data-btn=refreshAll]"),
        // 包含RSS地址的链接列表
        rssElement = document.querySelectorAll("[data-rss]"),
        // 整个链接列表元素
        linkListElemetn = document.querySelector(".links-list"),
        // 检查单个RSS源
        refresh = (params, callback) => {
            const
                { linkElement, rssElement } = { ...params },
                ingDot = '......',
                waitTime = 1500,
                statusElement = linkElement.querySelector(".status"),
                checkBtn = linkElement.querySelector("[data-btn=check-rss]"),
                link_id = linkElement.getAttribute("data-link"),
                rss_url = linkElement.getAttribute("data-rss"),
                link_name = linkElement.querySelector(".title").innerText,
                updateElement = (params) => {
                    checkBtn.disabled = false
                    const success = params.success !== undefined ? params.success : false,
                        data = params.data !== undefined ? params.data : {},
                        update_time = data.update_time !== undefined ? data.update_time : (new Date()).toString(),
                        message = data.message !== undefined ? data.message : '插件系统内部错误，请联系插件作者反馈'
                    statusElement.innerHTML = '<p class="check-time">检查RSS时间：<span>' + update_time + '</span></p>'
                    if (success === true) {
                        statusElement.innerHTML += '<p class="ok">OK</p>'
                        linkElement.style.backgroundColor = "#f1faf0"
                        fct_notice_show("success", link_name + " [" + rss_url + "] OK")
                    } else {
                        linkElement.style.backgroundColor = "#F8F8DE"
                        statusElement.innerHTML += '<p class="error"><span>错误信息如下：</span><br>' + message + '</p>'
                        fct_notice_show("error", link_name + " [" + rss_url + "] Error<br />" + message)
                    }
                    // 更新状态
                    clearInterval(ingInterval)
                    ingSecond = 0
                    checkBtn.removeAttribute("data-ing")
                    checkBtn.innerHTML = "检查RSS"
                }

            let
                ingStatus = checkBtn.getAttribute("data-ing"),
                ingStep = 1,
                ingSecond = 1,
                ingInterval
            if (!ingStatus) {
                checkBtn.disabled = true
                checkBtn.setAttribute("data-ing", 1)
                linkElement.style.backgroundColor = "#FEFEFE"
                statusElement.innerHTML = '<p class="checking">正在检查RSS源</p>'
                checkBtn.innerHTML = "正在检查..."
                fct_notice_show("success", "正在检查： " + link_name + "[" + rss_url + "] ... " + ingSecond + "s")
                ingInterval = setInterval(() => {
                    statusElement.innerHTML = '<p class="checking">正在检查RSS源' + ingDot.slice(0, ingStep) + '</p>'
                    checkBtn.innerHTML = "正在检查... " + ingSecond + "s"
                    fct_notice_show("success", "正在检查： " + link_name + "[" + rss_url + "]... " + ingSecond + "s")
                    ingStep >= ingDot.length ? ingStep = 1 : ''
                    ingStep++
                    ingSecond++
                }, 1000);

                jQuery.ajax({
                    "url": feng_custom_ajax_obj.ajax_url,
                    "method": "POST",
                    "data": {
                        _ajax_nonce: feng_custom_ajax_obj.nonce,
                        action: 'feng_custom_refresh_rss',
                        do: rssElement ? 'check' : 'order',
                        link_id
                    },
                }).fail(response => {
                    // 服务器错误
                    updateElement(response)
                    linkElement.style.backgroundColor = "#F8F8DE"
                    if (rssElement) {
                        callback(response)
                    }
                    fct_notice_show("error", "操作中断！插件系统内部错误，请联系插件作者反馈。")
                }).done(function (response) {
                    if (rssElement) {
                        // 等待间隔
                        setTimeout(() => {
                            refreshAllIndex++
                            if (refreshAllIndex < rssElement.length) {
                                updateElement(response)
                                refresh({
                                    linkElement: rssElement[refreshAllIndex],
                                    rssElement
                                }, callback)
                            } else {
                                updateElement(response)
                                callback(response)
                            }
                        }, waitTime)
                    } else {
                        updateElement(response)
                    }
                })
            }
        }

    if (linkListElemetn) {
        // RSS聚合信息页面
        linkListElemetn.addEventListener('click', (e) => {
            const btn = e.target.getAttribute("data-btn")
            if (btn === "check-rss") {
                const linkElement = e.target.parentElement.parentElement
                refresh({ linkElement })
            }
        })

        refreshAllBtn.addEventListener('click', e => {
            refreshAllBtn.disabled = true
            let msg = '正在更新下列链接的RSS信息... '
            refreshAllBtn.innerHTML = msg
            fct_notice_show('success', msg);
            // 开启计时器
            refreshAllInterval = setInterval(() => {
                refreshTotalSecond++
                msg = '正在更新下列链接的RSS信息... ' + refreshTotalSecond + 's'
                refreshAllBtn.innerHTML = msg
            }, 1000);
            refresh({
                linkElement: rssElement[refreshAllIndex],
                rssElement: rssElement
            }, () => {
                jQuery.ajax({
                    "url": feng_custom_ajax_obj.ajax_url,
                    "method": "POST",
                    "data": {
                        _ajax_nonce: feng_custom_ajax_obj.nonce,
                        action: 'feng_custom_refresh_rss',
                        do: 'order'
                    },
                }).fail(response => {
                    clearInterval(refreshAllInterval)
                    if (refreshAllIndex >= rssElement.length - 1) {
                        refreshAllIndex = 0
                    }
                    refreshAllBtn.innerHTML = '更新下列链接的RSS信息'
                    refreshAllBtn.disabled = false
                    fct_notice_show("error", "更新检查RSS源遇到问题，已用时 " + refreshTotalSecond + " 秒！")
                    refreshTotalSecond = 0
                }).done(response => {
                    // 清除定时器
                    clearInterval(refreshAllInterval)
                    refreshAllIndex = 0
                    refreshAllBtn.innerHTML = '更新下列链接的RSS信息'
                    refreshAllBtn.disabled = false
                    fct_notice_show("success", "检查RSS源全部完成，共用时 " + refreshTotalSecond + " 秒！")
                    refreshTotalSecond = 0
                })
            })
        })
    }

});


