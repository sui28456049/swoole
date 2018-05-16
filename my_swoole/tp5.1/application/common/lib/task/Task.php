<?php
namespace app\common\lib\task;

use app\common\lib\ali\Sms;
use app\common\redis\Predis;
use app\common\lib\Redis;
class Task {

    /**
     * @Author MR.Sui
     * @title [异步发送验证码]
     * @param $data
     * @param $serv
     * @return bool
     */
    public function sendSms($data,$serv)
    {
        try {
            //$response = Sms::sendSms($data['phone'], $data['code']);  $response->Code === "OK"
            $response = 'OK';  //模拟验证码发送成功
        }catch (\Exception $e) {
            // todo
            return false;
        }

        // 如果发送成功 把验证码记录到redis里面
        if($response === "OK") {
            Predis::getInstance()->set(Redis::smsKey($data['phone']), $data['code'], config('redis.out_time'));
        }else {
            return false;
        }
        return true;
    }


    /**
     * 通过task机制发送赛况实时数据给客户端
     * @param $data
     * @param $serv swoole server对象
     */
    public function pushLive($data, $serv) {
        $clients = Predis::getInstance()->sMembers(config("redis.live_game_key"));

        foreach($clients as $fd) {
            $serv->push($fd, json_encode($data));
        }
    }

}


?>