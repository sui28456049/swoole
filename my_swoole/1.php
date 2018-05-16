<?php
//$redis = new \Swoole\Coroutine\Redis();
//$redis->connect('127.0.0.1', 6379);
//$val = $redis->get('key');
//print_r($val);

$http = new swoole_http_server("0.0.0.0", 9501);

$http->on('request', function ($request, $response) {
    $redis = new \Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end(json_encode($redis));
});

$http->start();

?>
