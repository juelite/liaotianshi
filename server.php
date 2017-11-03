#! /usr/local/bin/php
<?php
date_default_timezone_set('Asia/Shanghai');

$server = new swoole_websocket_server("127.0.0.1", 9502);

connects(0 , 'clear');

$server->on('open', function($server, $req){
    connects($req->fd);
});

$server->on('message', function($server, $frame){
    $msg = explode('~' , $frame->data);

    $rep = ['code' => $msg[0] , 'user' => $msg[1] , 'msg' => $msg[2] , 'time' => date('H:i:s')];

    $connects = connects(0,'read');

    foreach($connects as $v) {
        $server->push($v, json_encode($rep));
    }


});

$server->on('close', function($server, $fd){
    connects($fd , 'out');
});

$server->start();


function connects($cid = 0 , $op = 'in')
{
    $connects = file_get_contents('/tmp/t.php');
    $connects = @json_decode($connects , true);
    if($op == 'out'){
        if(!$connects){
            return;
        }
        if(in_array($cid , $connects)){
            $connects = array_filter($connects , function($v) use ($cid){
                if($v != $cid){
                    return true;
                }
            });
            file_put_contents('/tmp/t.php' , json_encode($connects));
        }
    }elseif($op == 'read'){
        if(!$connects){
            return;
        }
        return $connects;
    }elseif($op == 'clear'){
        file_put_contents('/tmp/t.php' , json_encode([]));
    }else{
        if(!$connects){
            $connects = [];
        }
        $n = array_merge($connects , array($cid));
        file_put_contents('/tmp/t.php' , json_encode($n));
    }
}