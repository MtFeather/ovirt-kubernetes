<?php
  if (isset($_GET["name"]) && isset($_GET["namespace"])) {
    $pod = $_GET["name"];
    $namespace = $_GET["namespace"];
    $server = $_SERVER['SERVER_NAME'];
    $ip="ip";
    while (true == true) {
      $port = rand(30000, 32767);
      $fp = fsockopen('localhost', $port);
      if (!$fp) break;
    }
    exec("sudo -b DISPLAY=$server TERM='xterm' /opt/go/bin/gotty -w -t -a $ip -p $port --max-connection 1 --once kubectl --namespace=$namespace exec -it $pod /bin/bash > /dev/null 2>&1");
    header("Location: https://$ip:$port");
  }
?>
