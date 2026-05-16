(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const data = window.DynamicIslandData || {};
        const siteName = data.siteName || '网站';
        const currentPage = data.currentPage || 'home';
        console.log(currentPage)
        const iconUrl = data.iconUrl;

        let message = '';

        function getTitle() {
            const h1 = document.querySelector('h1');
            let titleText = '';
            if(currentPage == 'other' || currentPage == 'other') {
                titleText = document.title || '此页面';
                const separators = [' - ', ' – ', ' | ', ' — '];
                let firstSeparatorIndex = titleText.length;
                separators.forEach(sep => {
                    const index = titleText.indexOf(sep);
                    if (index !== -1 && index < firstSeparatorIndex) {
                        firstSeparatorIndex = index;
                    }
                });
                if (firstSeparatorIndex < titleText.length) {
                    titleText = titleText.substring(0, firstSeparatorIndex).trim();
                }
            }else if (h1 && h1.innerText.trim() !== '') {
                titleText = h1.innerText.trim();
            }
            return titleText;
        }

        switch(currentPage) {
            case 'home':
                message = `欢迎来到 ${siteName}!`;
                break;
            case 'single':
                const postTitle = getTitle() || '此文章';
                message = `正在访问 ${postTitle} &nbsp;`;
                break;
            case 'page':
                const pageTitle = getTitle() || '此页面';
                message = `正在浏览 ${pageTitle} &nbsp;`;
                break;
            case 'category':
                const categoryTitle = getTitle() || '该分类';
                message = `正在浏览 ${categoryTitle} 分类&nbsp;`;
                break;
            case 'tag':
                const tagTitle = getTitle() || '该标签';
                message = `正在浏览 ${tagTitle} 标签&nbsp;`;
                break;
            case 'search':
                const searchQuery = new URLSearchParams(window.location.search).get('s') || '内容';
                message = `搜索关键词: "${searchQuery}"`;
                break;
            case 'other':
            const otherTitle = getTitle() || '此页面';
            message = `正在浏览 ${otherTitle} &nbsp;`;
            break;
            default:
                message = `欢迎来到 ${siteName}!`;
        }

        const style = document.createElement('style');
        style.textContent = `
            .dynamic-island {
                position: fixed;
                top: 50px;
                left: 50%;
                transform: translateX(-50%);
                background: rgb(22, 22, 22);
                border-radius: 25px;
                padding: 8px 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                max-width: 90%;
                min-width: 240px;
                height: 35px;
                z-index: 999999;
                opacity: 0;
                transition: transform 0.4s ease-in-out, height 0.6s ease-in-out, border-radius 0.6s ease-in-out, box-shadow 0.5s ease-in-out, opacity 0.5s ease-in-out, width 0.4s ease-in-out;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dynamic-island.show {
                opacity: 1;
            }

            .dynamic-island:hover {
                height: 52px;
                border-radius: 50px;
            }
            .dynamic-island .ldd-icon {
                flex-shrink: 0;
                transition: transform 0.3s ease; 
            }

            .dynamic-island:hover .ldd-icon {
                transform: scale(1.4); 
            }
            .island-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-grow: 1;
                min-width: 0;
            }

            .content-text {
                color: white;
                margin: 0;
                font-size: 14px;
                transition: margin 0.3s ease;
                flex-grow: 1;
                
            }
            .bars {
                display: flex;
                align-items: center;
                gap: 3px;
            }

            .bar {
                width: 3px;
                height: 15px;
                background: linear-gradient(to bottom, #4cd964, #5ac8fa, #007aff, #34aadc, #5856d6);
                animation: soundBars 0.5s ease infinite;
                border-radius: 1px;
            }

            .bar:nth-child(1) { animation-delay: 0.1s; }
            .bar:nth-child(2) { animation-delay: 0.2s; }
            .bar:nth-child(3) { animation-delay: 0.3s; }
            .bar:nth-child(4) { animation-delay: 0.4s; }
            .bar:nth-child(5) { animation-delay: 0.5s; }

            @keyframes soundBars {
                0% { height: 6px; }
                30% { height: 15px; }
                60% { height: 8px; }
                100% { height: 12px; }
            }

            .dynamic-island img {
                flex-shrink: 0;
            }
            @media (max-width: 768px) {
                .content-text {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            }
            
            @media (max-width: 480px) {
               
                .content-text {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                
            }

        `;

        document.head.appendChild(style);

        const div = document.createElement('div');
        div.className = 'dynamic-island';
        div.innerHTML = `
            <img class="ldd-icon" src="${iconUrl}" alt="通知图标" width="20" height="20">
            <div class="island-content">
                <p class="content-text">${message}</p>
                <div class="bars">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
            </div>
        `;

        document.getElementById('dynamic-island-container').appendChild(div);
        requestAnimationFrame(() => div.classList.add('show'));

        function adjustWidth() {
            const island = div;
            const content = div.querySelector('.content-text');
            const bars = div.querySelector('.bars');
            const padding = 40; 

            const tempSpan = document.createElement('span');
            tempSpan.style.visibility = 'hidden';
            tempSpan.style.whiteSpace = 'nowrap';
            tempSpan.innerText = content.innerText;
            document.body.appendChild(tempSpan);
            const textWidth = tempSpan.getBoundingClientRect().width;
            document.body.removeChild(tempSpan);

            const barsWidth = bars.getBoundingClientRect().width;
            const totalWidth = textWidth + barsWidth + padding;

            const viewportWidth = window.innerWidth * 0.9;
            island.style.width = `${Math.min(totalWidth, viewportWidth)}px`;
        }

        adjustWidth();

        window.addEventListener('resize', adjustWidth);

        setTimeout(() => {
            div.classList.remove('show');
            setTimeout(() => div.remove(), 500);
        }, 3000);
    });
})();
