<?php
class Ws
{
    public $ws = null;

    public function __construct()
    {
        $this->ws = new swoole_websocket_server("0.0.0.0", 9501);
        # 开启task
        $this->ws->set([
            'worker_num' => 4,    //worker process num
            'task_worker_num'=>2,
        ]);

        $this->ws->on('open', [$this,'onOpen']);
        $this->ws->on('message', [$this,'onMessage']);
        $this->ws->on('close', [$this,'onClose']);
        $this->ws->on('task', [$this,'onTask']);
        $this->ws->on('finish', [$this,'onFinish']);


        $this->ws->start();
    }

    public function onOpen( $ws, $frame)
    {
        swoole_async_writefile(__DIR__.'/log.txt','ddddd', function($filename) {
            sleep(10);
            echo "wirte ok.\n";
        }, FILE_APPEND);

        echo "server: handshake success with fd{$frame->fd}\n";
    }

    public function onMessage($ws, $frame)
    {
        $time = date('Y-m-d H:i:s',time());
        $data = [
            'name' => 'sui123456',
        ];
       // $res = $ws->task($data);

        $ws->push($frame->fd, $time);
    }

    public function onClose($ws, $fd)
    {
        echo "close ------  {$fd}\n";
    }

    // 投递任务
    public function onTask($ws,$task_id,$src_worker_id,$data)
    {
        print_r($data);
        //耗时场景
        sleep(10);
        return 'on fininsh ok';
    }

    public function onFinish(swoole_server $serv, int $task_id, string $data)
    {
        echo $data.date('Y-m-d H:i:s',time()).PHP_EOL;
    }
}

$webSocket = new Ws();





 ?>



?>