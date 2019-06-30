<?php
  if (isset($_GET["name"])) {
    $pod = $_GET["name"];
    $server = $_SERVER['SERVER_NAME'];
    $ip="localhost";
    while (true == true) {
      $port = rand(30000, 32767);
      $fp = fsockopen('localhost', $port);
      if (!$fp) break;
    }
    exec("sudo -b DISPLAY=$server TERM='xterm' /opt/go/bin/gotty -w -t -a $ip -p $port --once kubectl exec -it $pod /bin/bash > /dev/null 2>&1");
    header("Location: https://$ip:$port");
  }
?>
