<?php

class http {

    CONST HOST = "0.0.0.0";
    CONST PORT = 9501;

    public $http = null;
    public function __construct() {
        $this->http = new swoole_http_server(self::HOST, self::PORT);

        $this->http->set(
            [
                'worker_num' => 3,
                'task_worker_num' => 2,
                'document_root' => '/var/games/tp5.1/public/static',
                'enable_static_handler' => true,
            ]
        );

        $this->http->on("workerstart", [$this, 'onWorkerStart']);
        $this->http->on("request", [$this, 'onRequest']);
        $this->http->on("task", [$this, 'onTask']);
        $this->http->on("finish", [$this, 'onFinish']);

        $this->http->start();
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


    }

  public function onRequest($request, $response)
  {
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
        $_POST['http_server'] = $this->http;  //异步task任务需要用
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
        $flag = $obj->$method($data['data']);
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

}

new http();