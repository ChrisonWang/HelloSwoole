<?php
/**
 *
 */
class ReloadServer
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_server('0.0.0.0', 9501);
        $this->serv->Set([
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'debug_mode' => 1
        ]);

        //绑定回调函数
        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on('Receive', [$this, 'onReceive']);
        $this->serv->on('Close', [$this, 'onClose']);

        //启动服务
        $this->serv->start();
    }

    public function onStart(swoole_server $serv)
    {
        echo "服务已启动...\n";
        cli_set_process_title('reload_master');
    }

    public function onWorkerStart(swoole_server $serv, $worker_id)
    {
        echo "workStart!\n";
        require_once "reload_page.php";
        Test();
    }

    public function onConnect(swoole_server $serv, $fd, $from_id)
    {
        echo "与客户端[{$fd}-{$from_id}]建立连接！\n";
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        echo "收到来自客户端[{$fd}-{$from_id}]的信息：{$data}。\n";
    }
    public function onClose(swoole_server $serv, $fd, $from_id)
    {
        echo "与客户端[{$fd}-{$from_id}]的连接已关闭！\n";
    }
}

$ReloadServer = new ReloadServer();
