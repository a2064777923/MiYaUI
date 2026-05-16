# 节日氛围

## 目录结构
```
static
　├　css
　│　　└　festivals.css
　├　img
　├　js
　│　　├　festivals.js
　│　　└　lunar.js
　├　less
　│　　└　festivals.less
　└　festivals.html
```
## 使用方法

### 1、引入CSS、JS文件

festivals.css 节日氛围样式文件

lunar.js 日历脚本文件

festivals.js 节日氛围脚本文件

注意：脚本文件引入顺序，先引入lunar.js文件，再引入festivals.js文件。
```
    <link rel="stylesheet" href="./css/festivals.css">
    <script src="./js/lunar.js"></script>
    <script src="./js/festivals.js"></script>
```

### 2、添加脚本
```
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const option = {
                // 元旦
                new_year_open: 0,
                new_year_text: '',
                new_year_advance: '',
                new_year_delay: '',

                // 开始春节氛围
                spring_festival_open: 1,
                // 春节氛围开启的时间（农历）
                spring_festival_open_range: {
                    start: {
                        // 用数字表示，12表示腊月
                        month: 12,
                        // 用数字表示，22表示廿二
                        day: 22
                    },
                    end: {
                        // 用数字表示，1表示正月
                        month: 1,
                        // 用数字表示，9表示初九
                        day: 9
                    },
                },
                // 春节倒计时开关
                spring_festival_open_countdown: 1,
                // 春节倒计时自定义图片URL
                spring_festival_countdown_image: '',
                // 春节海报开关
                spring_festival_open_poster: 1,
                // 春节海报自定义图片
                spring_festival_poster_image: '',
                // 春节挂件开关
                spring_festival_open_pendant: 1,
                // 春节挂件自定义图片URL
                spring_festival_pendant_image: '',
                // 挂件位置
                spring_festival_position: [],

                // 开启中秋节氛围
                mid_autumn_open: 0,
                // 中秋快乐显示的文字，可以为空，默认为中秋快乐
                mid_autumn_text: '',
                // 提前n天开启中秋氛围，可以为空，默认为提前3天
                mid_autumn_advance: '',
                // 延迟n天关闭中秋氛围，可以为空，默认为1天
                mid_autumn_delay: '',

                // 开启国庆节日氛围
                national_day_open: 0,
                // 欢度国庆显示的文字，可以为空，默认为欢度国庆
                national_day_text: '',
                // 提前n天开启国庆氛围，可以为空，默认为3天
                national_day_advance: '',
                // 延迟n天关闭节日氛围，可以为空，默认为6天
                national_day_delay: ''
            }

            fengCustomFestivals.run(option)
        })
    </script>
```
### 完整示例
```
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>节日氛围</title>
    <link rel="stylesheet" href="./css/festivals.css">
    <script src="./js/lunar.js"></script>
    <script src="./js/festivals.js"></script>
</head>

<body>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const option = {
                // 元旦
                new_year_open: 0,
                new_year_text: '',
                new_year_advance: '',
                new_year_delay: '',

                // 开始春节氛围
                spring_festival_open: 1,
                // 春节倒计时开关
                spring_festival_open_countdown: 1,
                // 春节倒计时自定义图片URL
                spring_festival_countdown_image: '',
                // 春节海报开关
                spring_festival_open_poster: 1,
                // 春节海报自定义图片
                spring_festival_poster_image: '',
                // 春节挂件开关
                spring_festival_open_pendant: 1,
                // 春节挂件自定义图片URL
                spring_festival_pendant_image: '',
                // 挂件位置
                spring_festival_position: [],

                // 开启中秋节氛围
                mid_autumn_open: 0,
                // 中秋快乐显示的文字，可以为空，默认为中秋快乐
                mid_autumn_text: '',
                // 提前n天开启中秋氛围，可以为空，默认为提前3天
                mid_autumn_advance: '',
                // 延迟n天关闭中秋氛围，可以为空，默认为1天
                mid_autumn_delay: '',

                // 开启国庆节日氛围
                national_day_open: 0,
                // 欢度国庆显示的文字，可以为空，默认为欢度国庆
                national_day_text: '',
                // 提前n天开启国庆氛围，可以为空，默认为3天
                national_day_advance: '',
                // 延迟n天关闭节日氛围，可以为空，默认为6天
                national_day_delay: ''
            }

            fengCustomFestivals.run(option)
        })
    </script>
</body>

</html>
```