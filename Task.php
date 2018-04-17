<?php
/**
 *
 */
class Server
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->Set(array(
            'worker_num' => 4,
            'task_worker_num' => 4,
            'deamonize' => false,
        ));

        //绑定事件
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->start();
    }

    public function onStart( $serv )
    {
        echo "Swoole Server Start...\n";
    }

    public function onConnect($serv, $fd, $from_id)
    {
        echo "客户端: {$fd}->{$from_id} 连接成功!\n";
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data )
    {
        echo "收到客户端信息 {$fd}:{$data}\n";
        // send a task to task worker.
        $param = array(
            'fd' => $fd,
            'data' => $data,
            'from_id' => $from_id,
        );
        $serv->Task(json_encode($param));
        echo "Continue Handle Worker!\n";
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        echo "进入Task进程, TaskId:{$task_id},FormId:{$from_id}\n";
        echo "Data: {$data}\n";
        for ($i=1; $i <= 5 ; $i++) {
            sleep(1);
            echo "Taks {$task_id} Handle {$i} times...\n";
        }
        $fd = json_decode( $data , true )['fd'];
        //返回给客户端的数据
        $result = ['success'=> true, 'msg'=> "Data in Task {$task_id}"];
        $serv->send($fd , json_encode($result));
        return "Task {$task_id}'s result";
    }

    public function onFinish($serv, $task_id, $data)
    {
        echo "Task {$task_id} 进程结束\n";
        echo "Result: {$data}\n";
    }

    public function onClose( $serv, $fd, $from_id )
    {
        echo "Client {$fd} close connection！\n";
    }
}

$server = new Server();
