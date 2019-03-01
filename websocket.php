<?php
class WebsocketTest {
    public $server;
    public function __construct() {
        static $user_list=[];//使用全局静态变量存储用户名
        $this->server = new Swoole\WebSocket\Server("192.168.61.130", 9000);
        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            global $user_list;
            $arr=json_decode("{$frame->data}",'ture');
            if($arr['type']=='handshake'){
                $user_list["{$frame->fd}"]=$arr['content'];
                $arr['user_list']=$user_list;
                $arr['num']=count($arr['user_list']);
                $data=json_encode($arr);
            }
            else if($arr['type']=='user'){
                $arr['from']=$user_list["{$frame->fd}"];
                $data=json_encode($arr);
            }
            else{
                return;
            }
            pushmsg($data);
        });
        $this->server->on('close', function ($ser, $fd) {
            $info=$this->server->connection_info($fd);
            $is_websocket=$info['websocket_status'];
            if($is_websocket){
                echo "client {$fd} closed\n";
                global $user_list;
                $arr['content']=$user_list["{$fd}"];
                unset($user_list["{$fd}"]);
                $arr['type']='close';
                $arr['user_list']=$user_list;
                $arr['num']=count($arr['user_list']);
                $data=json_encode($arr);
                pushmsg($data);
            }
        });
        $this->server->on('request', function ($request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            $data=json_encode($request->post);
            pushmsg($data);
        });
        $this->server->start();

        function pushmsg($data){
        foreach ($this->server->connections as $fd) {
            $this->server->push($fd, $data);
        }
        }
    }
}
new WebsocketTest();
?>