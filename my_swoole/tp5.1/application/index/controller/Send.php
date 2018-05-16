<?php
namespace app\index\controller;

use app\common\lib\ali\Sms;
use app\common\lib\Redis;
use app\common\lib\Util;
use app\common\redis\Predis;  //原生redis

class Send {
	/**
	 * [index 发送验证码]
	 * @return [type] [description]
	 */
	public function index()
    {
        $phoneNum = intval($_GET['phone_num']);

        if (empty($phoneNum)) {
            return Util::show(config('code.error'), 'error');
        }

        //生成一个随机数
        $code = rand(1000, 9999);


        $taskData = [
            'method' => 'sendSms',
            'data' => [
                'phone' => $phoneNum,
                'code' => $code,
            ]
        ];
        $_POST['http_server']->task($taskData);
        return Util::show(config('code.success'), 'ok');

//        try {
//            #$response = Sms::sendSms($phoneNum, $code); $response->Code === "OK"
//            $response = 'OK';
//        }catch (\Exception $e) {
//            // todo
//            return Util::show(config('code.error'), '阿里大于内部异常');
//        }
//        if($response === "OK") {
//            // 携程redis
//           /* $redis = new \Swoole\Coroutine\Redis();
//            $redis->connect(config('redis.host'), config('redis.port'));
//            $redis->set(Redis::smsKey($phoneNum), $code, config('redis.out_time'));*/
//
//            // 异步redis
//
//            //原生redis
//            Predis::getInstance()->set(Redis::smsKey($phoneNum), $code, config('redis.out_time'));
//
//            return Util::show(config('code.success'),$code.'----success');
//        } else {
//         return Util::show(config('code.error'), '验证码发送失败');
//        }



    }

}
