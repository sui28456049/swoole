<?php

$http = new swoole_http_server("0.0.0.0", 9501);

$http->set(array(
    'worker_num' => 3,    //worker process num
    'document_root' => '/var/games/tp5.1/public/static',
    'enable_static_handler' => true,
));

$http->on('request', function($request, $response) use($http){

    //超全局变量保存在内存中,不会释放
    $_GET = [];
    $_SERVER = [];
    $_POST = [];
    if(isset($request->server)) {
        foreach($request->server as $k=>$v) {
            $_SERVER[strtoupper($k)] = $v;
        }
    }

    if(isset($request->header)) {
        foreach($request->header as $k=>$v ) {
            $_SERVER[strtoupper($k)] = $v;
        }
    }

    if(isset($request->get)) {
        foreach($request->get as $k=>$v ) {
            $_GET[$k] = $v;
        }
    }

    if(isset($request->post)) {
        foreach($request->post as $k=>$v) {
            $_POST[$k] = $v;
        }
    }
    // add sui
    ob_start();
    // tp5.1  执行应用并响应
    try {
        think\Container::get('app', [APP_PATH])
            ->run()
            ->send();
    }catch (\Exception $e) {
        //Todo
    }
    $res = ob_get_contents();

    ob_end_clean();

    $response->end($res);

    //$http->close();  //粗暴方法

}

);




$http->on('WorkerStart','onWorkerStart');

/**
 * [tp框架常量会加载在常驻在内存中,除非结束该进程,需要改变框架中代码写法.]
 * 另一种方法写在onRequest 回调中,每次请求重新加载框架中的东西,类似于传统php-fpm形式
 * @param  [type] $serv      [description]
 * @param  [type] $worker_id [description]
 * @return [type]            [description]
 */
function onWorkerStart($serv, $worker_id) {
    // 定义应用目录
        define('APP_PATH', __DIR__ . '/../application/');
    // 加载框架引导文件
      require __DIR__ . '/../thinkphp/base.php';

}



$http->start();