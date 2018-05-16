<?php

class Server {
    const PORT = 9501;

    public function port() {
        //netstat -anp 2>/dev/null | grep 9501 | grep LISTEN | wc -l
        $shell  =  "netstat -anp 2>/dev/null | grep ". self::PORT . " | grep LISTEN | wc -l";

        $result = shell_exec($shell);

        if($result != 1) {
            // 发送报警服务 邮件 短信
            /// todo
            echo date("Ymd H:i:s")."error".PHP_EOL;
        } else {
            echo date("Ymd H:i:s")."succss".PHP_EOL;
        }
    }
}

// nohup
swoole_timer_tick(2000, function($timer_id) {
    (new Server())->port();
    echo "time-start".PHP_EOL;
});
