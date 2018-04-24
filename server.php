<?php

class SwooleServer{

    private $serv;
    

    public function __construct(){

        $this->serv = new Swoole\Server("127.0.0.1", 9501);
        $this->serv->set(array(
            'worker_num' => 8,   //工作进程数量
            'daemonize' => false, //是否作为守护进程
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'task_worker_num' => 8
        ));


        $this->serv->on('Start',array($this,'onStart'));
        $this->serv->on('Connect',array($this,'onConnect'));
        $this->serv->on('Receive',array($this,'onReceive'));
        $this->serv->on('Task',array($this,'onTask'));
        $this->serv->on('Finish',array($this,'onFinish'));
        $this->serv->on('Close', array($this,'onClose'));
        $this->serv->start();
    }

    public function onStart($serv){
        echo "start\r\n";
    }

    public function onConnect($serv,$fd,$from_id){
        echo " client {$fd} connect\r\n";
    }
    
    public function onReceive(swoole_server $serv,$fd,$from_id,$data){
        echo "get MESSAGE {$fd} {$data}\r\n";

        $data = [
            'id' => 1,
            "data" => $data,
            "fd"   => $fd
        ];
       
        $this->serv->task(json_encode($data));
    }
    
    public function onTask($serv,$task_id,$from_id,$data){
            echo "This Task {$task_id} from worker {$from_id}";

            $data = json_decode($data,true);
            echo "Data:{$data['data']}";

            $serv->send($data['fd'],"hello");

            return "finish";
    }

    public function onClose($serv,$fd,$from_id){
        echo "close";
    }

    public function onFinish($serv,$task_id,$data){
        echo "finish";
        echo "result : {$data}";
    }



}


    new SwooleServer();


?>
