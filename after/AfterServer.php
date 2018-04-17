<?php
/**
 *
 */
class AfterServer
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->Set([
            'worker_num' => 8,
            'daemonize' => false,
            'debug_mode' => true,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'heartbeat_check_interval'=>5,
            'heartbeat_idle_time'=>10
        ]);

        //绑定回调函数
        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on('Receive', [$this, 'onReceive']);
        $this->serv->on('Close', [$this, 'onClose']);

        //启动服务
        $this->serv->start();
    }

    public function onStart(swoole_server $serv)
    {
        echo "服务已启动...\n";
    }

    public function onConnect(swoole_server $serv, $fd, $from_id)
    {
        echo "与客户端[{$fd}-{$from_id}]建立连接！\n";
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        echo "收到来自客户端[{$fd}-{$from_id}]的消息：{$data}\n";
        $param = [
            'fd' => $fd,
            'msg' => "你好[{$fd}-{$from_id}]!现在是：".date('Y-m-d H:i:s', time())."。你刚才说：[{$data}]"
        ];
        $this->serv->after(3000, [$this, 'onAfter'], json_encode($param));
    }

    public function onAfter($data)
    {
        $param = json_decode($data);
        $this->serv->send($param->fd, $param->msg);
    }

    public function onTimer(swoole_server $serv, $interval)
    {

    }

    public function onClose(swoole_server $serv, $fd, $from_id)
    {
        $serv->send($fd, "连接超时！服务端已断开你的连接！\n");
        echo "来自客户端[{$fd}-{$from_id}]的连接关闭！\n";
    }
}

$AfterServer = new AfterServer();
