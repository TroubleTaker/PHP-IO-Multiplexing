<?php
$host = '127.0.0.1';
$port = '19990';
//创建 socket 
//AF_INET  表示网络层协议 IPv4
//SOCK_STREAM 表示传输层数据格式，TCP 协议基于字节流式套接字
//SOL_TCP 表示传输层协议 TCP
if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
    echo "socket_create error: " . socket_strerror($socket) . PHP_EOL;
    exit();
}

//把 socket 绑定在一个 IP 和端口上
if (socket_bind($socket, $host, $port) < 0) {
    echo "socket_bind error: " . socket_strerror($socket) . PHP_EOL;
    exit();
}

//监听由指定socket的所有连接
if (socket_listen($socket, 256) < 0) {
    echo "socket_listen error: " . socket_strerror($socket) . PHP_EOL;
    exit();
}
echo "Start time:" . date('Y-m-d H:i:s') . PHP_EOL;
echo "Listening at " . $host . ':' . $port . PHP_EOL;

while (true) {
    //接收一个Socket连接
    if (($conn = socket_accept($socket)) < 0) {
        echo "socket_accept error: " . socket_strerror($conn) . PHP_EOL;
        break;
    } else {
        //连接进来了，fork 一个进程，处理该连接的消息
        if (pcntl_fork() == 0) {
            //发送到客户端
            $msg = "From server: tcp socket connect successful...\n";
            socket_write($conn, $msg, strlen($msg));
            while (true) {
                // 获得客户端的输入
                $buf = socket_read($conn, 2048);
                // 把客户端输出打印到控制台
                if ($buf === '') {
                    socket_close($conn);
                    exit(0);
                } else {
                    echo "From Cilent:{$buf}\n";
                }
            }
        }
    }
}
//关闭socket
socket_close($socket);
