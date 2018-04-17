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
        $this->serv->addlistener("0.0.0.0", 9502 , SWOOLE_UDP);
        $this->serv->Set(array(
            'worker_num' => 8,
            'task_worker_num' => 2,
            'deamonize' => false,
        ));

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish',array($this, 'onFinish'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->start();
    }

    public function onStart( $serv )
    {
        echo "Start\n";
    }

    public function onConnect( $serv, $fd, $from_id )
    {
        $serv->send( $fd, "Hello {$fd}!" );
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data )
    {
        $info = $serv->connection_info($fd, $from_id);
        if($info['from_port'] == 9501) {
            $serv->send( $fd, "Hello {$fd}! From 9501" );
        }
        else {
            $serv->send( $fd, "Hello {$fd}! From 9502" );
        }
        /*if($data == "Task"){
            $serv->task($data , -1);
        }
        else {
            echo "Get Message From Client {$fd}:{$data}\n";
            $serv->send($fd, $data);
        }*/
    }

    public function onTask( swoole_server $serv, $task_id, $from_id, $data )
    {
        echo "onTask: {$data};".date('Y-m-d H:i:s', time())."\n";
    }

    public function onFinish( swoole_server $serv, $task_id, $data )
    {
        echo "onFinish";
    }

    public function onClose( $serv, $fd, $from_id )
    {
        echo "Client {$fd} close connection\n";
    }
}

$server = new Server();
