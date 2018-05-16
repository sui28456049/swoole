<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
    //    print_r($_GET);
//        print_r($this->request->param());
     //   return 'thinkphp5.1'.PHP_EOL;
        return '';
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'ddd,' . $name;
    }


    public function sui()
    {
        echo 'whesafsdfadsfsa'.PHP_EOL;
    }


    public function send()
    {
        $phoneNum = intval($_GET['phone_num']);
        echo $phoneNum;
    }

}
