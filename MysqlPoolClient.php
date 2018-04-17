<?php
/**
 *
 */
class MysqlPoolClient
{
    private $client;
    private $i = 0;
    private $time = 0;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        //绑定回调函数
        $this->client->on('Connect', [$this, 'onConnect']);
        $this->client->on('Receive', [$this, 'onReceive']);
        $this->client->on('Close', [$this, 'onClose']);
        $this->client->on('Error', [$this, 'onError']);
    }

    //回调函数
    public function onConnect($cli)
    {
        $cli->send("Chrison_{$this->i}");
        $this->time = time();
    }

    public function onReceive($cli, $data)
    {
        $this->i++;
        if( $this->i >= 10000 ) {
            $now = time();
            $time_spend = $now - $this->time;
            echo "插入10000条数据，总共耗时：[".$time_spend."]秒\n";
            exit(0);
        }
        else{
            $cli->send("Chrison_{$this->i}");
        }
    }

    public function onError() {
        echo "错误！\n";
    }

    public function onClose($cli){
        echo "连接关闭！\n";
    }

    //功能函数
    public function connect()
    {
        $fp = $this->client->connect('127.0.0.1', 9501);
        if(!$fp){
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
    }

    public function send($data)
    {
        $this->client->send( $data );
    }

    public function isConnected() {
        return $this->client->isConnected();
    }
}

$cli = new MysqlPoolClient();
$cli->connect();
