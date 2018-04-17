<?php
/**
 *
 */
class MysqlPoolServer
{
    private $serv;
    private $pdo;

    public function __construct()
    {
        $this->serv = new swoole_server('0.0.0.0', 9501);
        $this->serv->Set(array(
            'worker_num' => 16,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch' => 3,
            'debug_mode' => 1,
            'task_worker_num' => 16,
        ));

        //绑定回调函数
        $this->serv->on('Start', function() {
            echo "服务已启动...\n";
        });
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));

        //启动服务
        $this->serv->start();
    }

    public function onWorkerStart($serv, $worker_id)
    {
        echo "[{$worker_id}]onWorkerStart!\n";
        if($worker_id >= $serv->setting['worker_num']) {
            $this->pdo = new PDO(
                'mysql:host=localhost;port=3306;port=3306;dbname=swoole',
                'homestead',
                'secret' ,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8';",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => TRUE
                )
            );
        }
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "服务端：{$fd}-{$from_id} 连接成功！\n";
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        $sql = array(
            'sql' => 'INSERT INTO `test` (`fd_id`, `from_id`, `name`) VALUES (:fd_id, :from_id, :name)',
            'param' => array(
                'fd' => $fd,
                'from_id' => $from_id,
                'name' => $data
            ),
            'fd' => $fd
        );
        $serv->task(json_encode($sql));
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        try {
            $sql = json_decode($data, true);
            $statement = $this->pdo->prepare($sql['sql']);
            $statement->bindParam(":fd_id", $sql['param']['fd']);
            $statement->bindParam(":from_id", $sql['param']['from_id']);
            $statement->bindParam(":name", $sql['param']['name']);
            $statement->execute();

            $serv->send( $sql['fd'],"Insert");
            return true;
        } catch ( PDOException $pdo_e ) {
            echo "{$pdo_e->getMessage()}\n";
            echo "{$pdo_e->getCode()}\n";
            echo "{$pdo_e->getFile()}\n";
            echo "{$pdo_e->getLine()}\n";
            return false;
        }

    }

    public function onFinish($serv, $task_id, $data)
    {

    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "服务端：{$fd}-{$from_id} 断开！\n";
    }
}

new MysqlPoolServer();
