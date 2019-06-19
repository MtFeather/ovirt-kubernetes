<?php
$apiEntryPoint="http://localhost:8001";
function checkApi(){
  global $apiEntryPoint;
  
  $timeout = 10;
  $ch = curl_init();
  curl_setopt ( $ch, CURLOPT_URL, $apiEntryPoint );
  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
  $http_respond = curl_exec($ch);
  $http_respond = trim( strip_tags( $http_respond ) );
  $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
  if ( ( $http_code == "200" ) || ( $http_code == "302" ) ) {
    echo "ok";
  } else {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('message' => 'ERROR', 'code' => 1337)));
  }
  curl_close( $ch );
}

function getPodsList(){
  global $apiEntryPoint;
  $ch = curl_init();  
  curl_setopt($ch, CURLOPT_URL, $apiEntryPoint."/api/v1/namespaces/default/pods");
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
  $output = curl_exec($ch);
  curl_close($ch);
  $pods = json_decode($output,true);
  $data = array();
  $nowtime = time();
  foreach($pods["items"] as $pod){
    $nestedData=array();
    $nestedData['name'] = $pod['metadata']['name'];
    $nestedData['host_ip'] = $pod['status']['hostIP'];
    $nestedData['pod_ip'] = $pod['status']['podIP'];
    $nestedData['restarts'] = $pod['status']['containerStatuses'][0]['restartCount'];
    $nestedData['status'] = $pod['status']['phase'];
    $starttime = $pod['status']['startTime'];
    $nestedData['uptime'] = countTime($nowtime, $starttime);
    $data[] = $nestedData;
  }
  $json_data = array("data" => $data);
  echo json_encode($json_data);  
}

function createPod(){
  global $apiEntryPoint;
  $comment = $_POST["comment"];
  if (!empty($comment)) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,$apiEntryPoint."/api/v1/namespaces/default/pods");
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/yaml"));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "$comment");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    if ( ( $http_code == "200" ) || ( $http_code == "201" ) || ( $http_code == "202" ) ) {
      echo "ok";
    } else {
      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $body = substr($output, $header_size);
      $json = json_decode($body,true);
      $message = $json['message'];
      $code = $json['code'];
      echo "Code: ".$code.", Message: ".$message.".";
    }
    curl_close ($ch);
  }
}

function deletePod(){
  global $apiEntryPoint;
  $pods = $_POST["pods"];
  if (!empty($pods)) {
    foreach ($pods as &$pod) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL,$apiEntryPoint."/api/v1/namespaces/default/pods/".$pod);
      curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_POSTFIELDS, "$comment");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_exec($ch);
      curl_close ($ch);
    }
    echo "ok";
  }
}

function countTime($nowtime, $starttime){
  $starttime = strtotime($starttime); 
  $uptime = $nowtime - $starttime;
  if ($uptime < 60){
    return $uptime." sec";
  } else if ($uptime < 3600) {
    $mins = (int)($uptime / 60);
    return $mins." mins";
  } else if ($uptime < 86400) {
    $hours = (int)($uptime / 3600);
    return $hours." hours";
  } else if ($uptime >= 86400){
    $days = (int)($uptime / 86400);
    return $days." days";
  }
}

if (function_exists($_GET['f'])) {
  $_GET['f']();
}
?>
