<?php
/**
 *
 */
class AfterClient
{
    private $client;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        //绑定回调函数
        $this->client->on('Connect', [$this, 'onConnect']);
        $this->client->on('Receive', [$this, 'onReceive']);
        $this->client->on('Error', [$this, 'onError']);
        $this->client->on('Close', [$this, 'onClose']);
    }

    public function connect()
    {
        if(!$this->client->connect("127.0.0.1", 9501, 1)) {
            echo "连接服务端失败: {$this->client->errMsg}[{$this->client->errCode}]\n";
        }
    }

    public function onConnect(swoole_client $client)
    {
        echo "连接服务端成功！\n";
        fwrite(STDOUT, "请输入消息：");
        $msg = trim(fgets(STDIN));
        $this->client->send( $msg );
    }

    public function onReceive(swoole_client $client, $data)
    {
        echo "收到来自服务端的消息：{$data}\n";
        fwrite(STDOUT, "请输入消息：");
        $msg = trim(fgets(STDIN));
        $this->client->send( $msg );
    }

    public function onError(swoole_client $client)
    {
        echo "连接服务端失败: {$client->errMsg}[{$client->errCode}]\n";
    }

    public function onClose(swoole_client $client)
    {
        echo "与服务端的连接断开！\n";
    }
}

$AfterClient = new AfterClient();
$AfterClient->connect();
