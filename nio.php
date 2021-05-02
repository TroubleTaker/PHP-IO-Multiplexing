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

//设置为非阻塞 IO
socket_set_nonblock($socket);
//已建立连接的 socket
$activeConn = [];
while (true) {
    //接收一个Socket连接,此时 accept 返回 false
    if (($conn = socket_accept($socket)) < 0) {
        echo "socket_accept error: " . socket_strerror($conn) . PHP_EOL;
        break;
    } else {
        //如果有连接进来，添加到 activeConn
        //如果没有连接进来，遍历 activeConn,依次读取每个 conn 的消息
        if ($conn) {
            //已经建立连接的 socket 设置非阻塞 IO
            socket_set_nonblock($conn);
            $msg = "From server: tcp socket connect successful...\n";
            socket_write($conn, $msg, strlen($msg));
            $activeConn[] = $conn;
        } else {
            if ($activeConn) {
                while (true) {
                    foreach ($activeConn as $conn) {
                        // 获得客户端的输入，此时 read 也不是阻塞的了，返回 false
                        $buf = socket_read($conn, 2048);
                        if ($buf) {
                            echo "From Cilent:{$buf}\n";
                        }
                    }
                    break;
                }
            }
        }
    }
}
//关闭socket
socket_close($socket);
