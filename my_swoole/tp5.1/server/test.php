<?php
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 加载框架引导文件
// require __DIR__ . '/../thinkphp/base.php';
require __DIR__ . '/../thinkphp/start.php';

//app\common\redis\Predis::getInstance()->sRem('call1','suisusiusi');
$str = '1、购买伤心系列商品，用户可以通过app购买，通过微信或者支付宝支付，线下通过快递或者骑手送达商品。
2、用户可以通过app发布论坛帖子，可以上传自己的心情的动态来和网友互动。
技术特点，通过object-c来编写苹果端app，通过java编写安卓端app，原生代码手机系统支持好，用户体验优，少量的页面运用html编写，方便维护。';
echo mb_strlen($str);



?>