<?php
class Ws {

    CONST HOST = "0.0.0.0";
    CONST PORT = 9501;
    CONST CHAT = 8812;

    public $ws = null;
    public function __construct() {

        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);
        $this->ws->listen(self::HOST, self::CHAT, SWOOLE_SOCK_TCP);

        $this->ws->set(
            [
                'worker_num' => 3,
                'task_worker_num' => 2,
                'document_root' => '/var/games/tp5.1/public/static',
                'enable_static_handler' => true,
            ]
        );

        $this->ws->on("start", [$this, 'onStart']);
        $this->ws->on("workerstart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        //websocket
        $this->ws->on('open',[$this, 'onOpen']);
        $this->ws->on('message',[$this, 'onMessage']);
        $this->ws->on('close',[$this, 'onClose']);

        $this->ws->start();
    }

/**
 * @Author    Mr.Sui
 * @DateTime  2018-05-16
 * @copyright [copyright]
 * @license   [license]
 * @version   [version]
 * @return    [type]      [description]
 */
        public function onStart()
        {
            swoole_set_process_name('live_game');
        }
    /**
     * [tp框架常量会加载在常驻在内存中,除非结束该进程,需要改变框架中代码写法.]
     * 另一种方法写在onRequest 回调中,每次请求重新加载框架中的东西,类似于传统php-fpm形式
     * @param  [type] $serv      [description]
     * @param  [type] $worker_id [description]
     * @return [type]            [description]
     */
    public function onWorkerStart($serv, $worker_id)
    {
        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../application/');
        // 加载框架引导文件
        // require __DIR__ . '/../thinkphp/base.php';
        require __DIR__ . '/../thinkphp/start.php';


        //判断是redis有连接记录,重启清空
        $clientList = app\common\redis\Predis::getInstance()->sMembers(config('redis.live_game_key'));
        if(!empty($clientList)) app\common\redis\Predis::getInstance()->delete(config('redis.live_game_key'));

    }

    public function onRequest($request, $response)
    {
        
        if($request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return ;
        }
        //超全局变量保存在内存中,不会释放
        $_GET = [];
        $_SERVER = [];
        $_POST = [];
        $_FILES = [];
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
        if(isset($request->files)) {
            foreach($request->files as $k=>$v) {
                $_FILES[$k] = $v;
            }
        }
        // 记录日志
        $this->writeLog();

        // add sui
        $_POST['http_server'] = $this->ws;  //异步task任务需要用
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
    }
    /**
     * @param $serv
     * @param $taskId
     * @param $workerId
     * @param $data
     */
    public function onTask($serv, $taskId, $workerId, $data) {
        // 耗时场景 10s
        // 分发task 任务机制,让不同的任务走不通的逻辑
        $obj = new app\common\lib\task\Task;
        $method = $data['method'];
        $flag = $obj->$method($data['data'],$serv);
        return $flag; // 告诉worker
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $data
     */
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    public function onOpen (swoole_websocket_server $server, $request)
    {
        // print_r($server);
        //echo "connetct success";
        //存储用户
        app\common\redis\Predis::getInstance()->sAdd(config('redis.live_game_key'),$request->fd);
    
    }

    public function onMessage(swoole_websocket_server $server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, date('Y-m-d H:i:s',time()));
    }

    public function onClose($ser, $fd)
    {
        app\common\redis\Predis::getInstance()->sRem(config('redis.live_game_key'),$fd);
    }



    public function writeLog()
    {
       $datas = array_merge([
            'date' => date("Ymd H:i:s")],
            $_GET, 
            $_POST, 
            $_SERVER);

        $logs = "";
        foreach($datas as $key => $value) {
            $logs .= $key . ":" . $value . " ";
        }

        swoole_async_writefile(APP_PATH.'../runtime/log/'.date("Ym")."/".date("d")."_access.log", $logs.PHP_EOL, function($filename){
            // todo
        }, FILE_APPEND);

    }
}

new Ws();

?>