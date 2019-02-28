<?php
	class WebsocketTest {
    public $server;
    public function __construct() {
        $this->server = new Swoole\WebSocket\Server("192.168.61.130", 9000);
        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            static $user_list='';
            $arr=json_decode("{$frame->data}",'ture');
            if($arr['type']=='handshake'){
                $user_list.=$arr['content'];
                $arr['user_list']=explode(',',$user_list);
                $arr['num']=count($arr['user_list']);
                $data=json_encode($arr);
                foreach ($this->server->connections as $fd) {
                    $this->server->push($fd, "$data");
                }
            }
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            foreach ($this->server->connections as $fd) {
                $this->server->push($fd, $request->post['message']);
            }
        });
        $this->server->start();
    }
	}
	new WebsocketTest();
?>