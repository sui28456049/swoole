<?php
namespace app\admin\controller;
use app\common\lib\Util;
use app\common\redis\Predis;
class Live {
    public function push()
    {
        if(empty($_GET)) {
            return Util::show(config('code.error'),'error');
        }

        //此处应该为mysql操作
        $teams = [
            1=>[
                'name' => '马刺',
                'logo' => '/live/imgs/team1.png',
            ],
            4 => [
                'name' => '火箭',
                'logo' => '/live/imgs/team2.png',
            ],
        ];
        $client_list = Predis::getInstance()->sMembers(config('redis.live_game_key'));

        $data = [
            'type' => intval($_GET['type']),
            'title' => !empty($teams[$_GET['team_id']]['name']) ?$teams[$_GET['team_id']]['name'] : '直播员',
            'logo' => !empty($teams[$_GET['team_id']]['logo']) ?$teams[$_GET['team_id']]['logo'] : '',
            'content' => !empty($_GET['content']) ? $_GET['content'] : '',
            'image' => !empty($_GET['image']) ? $_GET['image'] : '',
        ];
        
        //print_r($_GET);
        // 获取连接的用户
        // 赛况的基本信息入库  2. 组织数据, push到直播
        /* foreach ($client_list as $client)
         {
             $_POST['http_server']->push($client, json_encode($data));
         }*/
        $taskData = [
            'method' => 'pushLive',
            'data' => $data
        ];
        $_POST['http_server']->task($taskData);
        return Util::show(config('code.success'), 'ok');
    }

}