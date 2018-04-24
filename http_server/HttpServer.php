<?php
class HttpServer
{
    private $http_server;
    
    public function __construct()
    {
        $this->http_server = new swoole_http_server("127.0.0.1", 9501);
        $this->http_server->Set([
            'worker_num' => 16,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mod' => 2,
        ]);

        //绑定回调函数
        $this->http_server->on('Start', [$this, 'onStart']);
        $this->http_server->on('Request', [$this, 'onRequest']);

        //启动服务
        $this->http_server->start();
    }

    public function onStart(swoole_http_server $http)
    {
        echo "Http服务已启动...\n";
    }

    public function onRequest($request, $response)
    {
        var_dump($request);
        $request->end("<h1>Hello Swoole...</h1>");
    }
}

new HttpServer();
