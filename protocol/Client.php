<?php
/**
 *
 */
class Client
{
    private $client;
    private $type;

    public function __construct($type)
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
        $this->type = $type;
    }

    public function connect()
    {
        if(!$this->client->connect('127.0.0.1', 9501)) {
            echo "服务端连接失败！[{$this->client->errCode}]\n";
        }

        //根据类型向服务端发送信息
        switch ($this->type) {
            case 'eof':
            {
                $i = 1;
                $msg_eof = "This is a Msg!\r\n";
                while ($i <= 1000) {
                    $msg_str = "[{$i}]".$msg_eof;
                    $this->client->send($msg_str);
                    $i++;
                }
                break;
            }
            case 'length':
            {
                $i = 1;
                $msg_normal = "这是用于测试固定包头类型协议的信息!";
                $msg_str = pack("N" , strlen($msg_normal) ). $msg_normal;
                while ($i <= 10) {
                    $this->client->send($msg_str);
                    $i++;
                }
                break;
            }
            default:
                break;
        }
    }
}

$client = new Client('eof');
$client->connect();
