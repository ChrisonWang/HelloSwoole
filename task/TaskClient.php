<?php
/**
 *
 */
class TaskClien
{
    private $client;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);


        //绑定回调函数
        $this->client->on('Connect', array($this, 'onConnect'));
        $this->client->on('Receive', array($this, 'onReceive'));
        $this->client->on('Error', array($this, 'onError'));
        $this->client->on('Close', array($this, 'onClose'));
    }

    public function connect() {
        $fp = $this->client->connect("127.0.0.1", 9501 , 1);
        if( !$fp ) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
    }

    public function onConnect($cli)
    {
        $cli->send("测试Task进程...\n");
    }

    public function onReceive($cli, $data)
    {
        $data = json_decode($data);
        echo "收到来自服务端的消息：{$data->msg}!\n";
        if($data->success == true){
            $this->client->close();
        }
    }

    public function onError($cli)
    {
        echo "连接失败!\n";
    }

    public function onClose($cli)
    {
        echo "连接关闭！\n";
    }

    public function send($data) {
        $this->client->send( $data );
    }
}

$TaskClien = new TaskClien();
$TaskClien->connect();
