<?php

use Wechat\XMW_FUN;

require( dirname(__FILE__).'/../../../wp-load.php' );


if($_GET["echostr"]){
    
    XMW_FUN::valid();
    
}elseif(!empty($_GET)){
    
    XMW_FUN::WechatReceive();
}


















// var_dump(XMW_FUN::$Common);