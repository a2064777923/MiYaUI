<?php
// 获取post_id参数
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if($post_id == 0) {
    die('请传入post_id参数');
}

// 请求URL
$url = 'https://www.yrucd.com/wp-admin/admin-ajax.php';

// 读取cookie文件
$cookie = file_get_contents('cookie.txt');
if(!$cookie) {
    die('无法读取cookie文件');
}

// POST数据
$post_data = [
    'action' => 'vvip_downdata',
    'post_id' => $post_id
];

// 初始化CURL
$ch = curl_init();

// 设置CURL选项
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post_data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_COOKIE => $cookie,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Origin: https://www.yrucd.com',
        'Referer: https://www.yrucd.com',
        'Accept: application/json, text/javascript, */*; q=0.01',
        'X-Requested-With: XMLHttpRequest'
    ]
]);

// 执行请求
$response = curl_exec($ch);

// 检查是否有错误
if(curl_errno($ch)) {
    die('Curl error: ' . curl_error($ch));
}

// 获取HTTP状态码
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// 关闭CURL
curl_close($ch);

// 输出结果
header('Content-Type: application/json');
echo $response;
?>
