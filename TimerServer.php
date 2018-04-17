<?php
/**
 *
 */
class TimerServer
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'debug_mode'=> 1 ,
            'heartbeat_check_interval' => 5,
            'heartbeat_idle_time' => 10,
        ));

        //绑定回调函数
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('WorkerStart', array($this, 'onWorkStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));

        //启动服务端
        $this->serv->start();
    }

    public function onStart($serv)
    {
        echo "服务已启动...\n";
    }

    public function onConnect($serv, $fd, $from_id)
    {
        $serv->send($fd, "欢迎您:{$from_id}\n");
        echo "成功连接用户：{$fd}->{$from_id}\n";
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {
        echo "收到来自客户端[{$fd}->{$from_id}]的信息:{$data}\n";
    }

    public function onWorkStart($serv, $work_id)
    {
        if($work_id == 0){
            //$serv->tick(1000, array($this, 'onTick'), array('work_id'=> $work_id));
        }
    }

    public function onTick($timer_id, $data)
    {
        echo "定时器执行{$data['work_id']}->{$timer_id}...\n";
    }

    public function onClose($serv, $fd, $from_id)
    {
        echo "客户端[{$fd}{$from_id}]的连接关闭！\n";
    }
}

new timerServer();
