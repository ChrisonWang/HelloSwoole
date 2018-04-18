<?php
/**
 *
 */
class LengthServer
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server('0.0.0.0', 9501);
        $this->serv->Set([
            'worker_num' => 8,
            'daemonize' => false,
            'debug' => 1,
            'dispatch_mode' => 2,
            'max_request' => 10000,
            'package_max_length' => 8192,
            'open_eof_check'=> true,
            'package_eof' => "\r\n"
        ]);

        //绑定回调函数
        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on('Receive', [$this, 'onReceive']);
        $this->serv->on('Close', [$this, 'onClose']);

        //启动服务
        $this->serv->start();
    }

    public function onStart(swoole_server $srev)
    {
        echo "服务已启动...\n";
    }

    public function onConnect(swoole_server $serv, $fd, $from_id)
    {
        echo "与客户端[{$fd}-{$from_id}]建立连接\n";
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        $msg_list = explode("\r\n", $data);
        foreach ($msg_list as $key => $msg) {
            if(!empty($msg)) {
                echo "收到来自客户端[{$fd}-{$from_id}]的消息：{$msg}\n";
            }
        }
    }

    public function onClose(swoole_server $serv, $fd, $from_id)
    {
        echo "与客户端[{$fd}-{$from_id}]的连接\n";
    }
}

new LengthServer();
