<?php
$host = '127.0.0.1';
$port = '19990';
//创建 socket
//AF_INET  表示网络层协议 IPv4
//SOCK_STREAM 表示传输层数据格式，TCP 协议基于字节流式套接字
//SOL_TCP 表示传输层协议 TCP
if (($listenSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
    echo "socket_create error: " . socket_strerror($listenSocket) . PHP_EOL;
    exit();
}

//把 socket 绑定在一个 IP 和端口上
if (socket_bind($listenSocket, $host, $port) < 0) {
    echo "socket_bind error: " . socket_strerror($listenSocket) . PHP_EOL;
    exit();
}

//监听由指定socket的所有连接
if (socket_listen($listenSocket, 256) < 0) {
    echo "socket_listen error: " . socket_strerror($listenSocket) . PHP_EOL;
    exit();
}
echo "Start time:" . date('Y-m-d H:i:s') . PHP_EOL;
echo "Listening at " . $host . ':' . $port . PHP_EOL;

//需要通过 select 遍历的 socket 数组
$socketArr = [$listenSocket];
while (true) {
    // socket_select 一共四个参数
    // read 返回可读的 $socket 数组
    // write 返回可写的 $socket 数组
    // except 返回异常的 $socket 数组
    // tv_sec 设置 select 等待时间，null 表示当没有事件发生时阻塞在 select
    // https://www.php.net/manual/zh/function.socket-select
    $reads = $socketArr;
    if (socket_select($reads, $writes, $excepts, null) > 0) {
        //进来则表示有事件发生了
        if (in_array($listenSocket, $reads)) {
            //如果是新连接进来了，那么就调用 accept
            $conn = socket_accept($listenSocket);
            $msg = "From server: tcp socket connect successful...\n";
            socket_write($conn, $msg, strlen($msg));
            //把新连接加到 socket 数组
            $socketArr[] = $conn;
            //把监听的 socket 删掉，那么剩下的全是已经建立连接的 socket
            $key = array_search($listenSocket, $reads);
            unset($reads[$key]);
        }
        if (!empty($reads)) {
            //如果已经建立连接的 socket 有数据返回,那么遍历依次读取数据
            foreach ($reads as $conn) {
                $buf = socket_read($conn, 2048);
                if ($buf === '') {
                    //当 buf === '' 表示 client 已经断开连接
                    //这个时候就要关闭这个 socket，并且从 socketArr 删除
                    socket_close($conn);
                    $key = array_search($conn, $socketArr);
                    unset($socketArr[$key]);
                } else {
                    echo "From Cilent:{$buf}\n";
                }
            }
        }
    }
}
//关闭socket
socket_close($listenSocket);
