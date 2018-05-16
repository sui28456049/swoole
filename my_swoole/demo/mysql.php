<?php 
class asyMysql{

    public $db = null;

    public $dbConfig = null;

    public function __construct() {

        $this->db = new Swoole\MySQL;
        $this->dbConfig = [
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => 'MyNewPass666!',
            'database' => 'sad',
            'charset' => 'utf8mb4', //指定字符集
            'timeout' => 2,  // 可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
        ];
    }

    public function execute($id, $username) 
    {
             //连接mysql
            $this->db->connect($this->dbConfig, function($db, $r) use($id,$username) {

                if ($r === false) 
                {
                    var_dump($db->connect_errno, $db->connect_error);
                    die;
                }

                     # $sql = 'select * from os_order where order_id = '.$id;
                $sql = 'update `os_order` set `consignee` = '."'{$username}'".' where `order_id` = '.$id; 
                 
                 $this->db->query($sql, function(swoole_mysql $db, $r) {
                        if ($r === false)   //sql语句错误执行
                        {
                            var_dump($db->error, $db->errno);
                        }
                        elseif ($r === true )  //增加,修改,删除
                        {
                            echo '修改成功!';
                            #var_dump($db->affected_rows, $db->insert_id);
                        }
                        #var_dump($r);     //select 返回的结果集
                
                 $this->db->close();
            });
        });

            return true;
   }

}

$mysql = new asyMysql();

$flag = $mysql->execute('1','suisuisui');

echo $flag.PHP_EOL;

echo "我先执行".PHP_EOL;  


// 异步执行








 ?>