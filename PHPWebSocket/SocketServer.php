<?php
/**
 * Created by PhpStorm.
 * User: franklee
 * Date: 1/28/2016
 * Time: 8:41 AM
 */

namespace Myapp;


class SocketServer
{
    private static $socket;
    private static $timeout = 60;
    private static $maxconns = 1024;
    private static $connections = array();

    function SocketServer($port){
        global $errno, $errstr;
        if($port<1024){
            die("port must be a number which bigger than 1024");
        }

        $socket = socket_create_listen($port);
        if(!$socket) die("Listen $port failed");

        socket_set_nonblock($socket);

        while(true){
            $readfds = array_merge(self::$connections, array($socket));
            $writefds = array();

            if(socket_select($readfds, $writefds, $e = null, $t = self::$timeout)){
                if(in_array($socket, $readfds)){
                    $newconn = socket_accept($socket);
                    $i = (int)$newconn;
                    $reject = '';
                    if(count(self::$connections) >= self::$maxconns){
                        $reject = "Server full";
                    }

                    self::$connections[$i] = $newconn;
                    $writefds[$i] = $newconn;
                    if($reject){
                        socket_write($writefds[$i], $reject);
                        unset($writefds[$i]);
                        self::close($i);
                    } else{
                        echo "Client $i come.\n";
                    }

                    $key = array_search($socket, $readfds);
                    unset($readfds[$key]);
                }

                foreach($readfds as $rfd) {
                    $i = (int)$rfd;
                    $line = @socket_read($rfd, 2048, PHP_NORMAL_READ);
                    if ($line == false) {
                        echo "Connection closed on socket $i.\n";
                        self::close($i);
                        continue;
                    }

                    $tmp = substr($line, -1);
                    if ($tmp != "/r" && $tmp != "/n") {
                        continue;
                    }

                    $line = trim($line);
                    if ($line == "quit") {
                        echo "client $i quit./n";
                        self::close($i);
                        break;
                    }
                    if ($line) {
                        echo "client $i >> " . $line . "/n";
                    }
                }

                foreach($writefds as $wfd) {
                    $i = (int)$wfd;
                    $w = socket_write($wfd, "Welcome client $i!/n");
                }

            }
        }
    }

    function close($i){
        socket_shutdown(self::$connections[$i]);
        socket_close(self::$connections[$i]);
        unset(self::$connections[$i]);
    }

}

new SocketServer(12345);
