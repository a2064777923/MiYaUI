<?php ?><?php 
if (function_exists('opcache_invalidate')){
    opcache_invalidate(substr($_SERVER['PHP_SELF'],strripos($_SERVER['PHP_SELF'],'/')+1));
}

if (!function_exists('sg_load')) {$__msg = '未安装SG运行插件，安装方式为PHP扩展里面选择SG11,安装后重启PHP即可';die($__msg);exit;}
//错误处理函数示例,函数名勿修改，内容可自行修改
    function MLTools_ErrorHandler_f19643c503b066c7d8461c38c13a65a0($e,$m){

    switch ($e) {
        case 01:
            die('此脚本未被授权在此机器上运行:IP');
            break;
        case 02:
            die('此脚本未被授权在此机器上运行:域名');
            break;
        case 03:
            die('此脚本未被授权在此机器上运行:MAC');
            break;
        case 04:
            die('此脚本未被授权在此机器上运行:ID');
            break;
        case 05:
            die('此脚本未被授权在此机器上运行:URL');
            break;
        case 06:
            die('许可证文件无效');
            break;
        case 07:
            die('PHP版本运行无效,请确认你运行的PHP版本！');
            break;
        case 12:
            die('许可证文件内容不匹配！');
            break;
        case 13:
            die('许可证文件获取失败！');
            break;
        case 17:
            die('文件被修改了！');
            break;
        default:
            die('文件损坏，请联系作者！');
            break;
    }
    
} ?><?php
return sg_load('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');
