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

function getNamespaces(){
  global $apiEntryPoint;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $apiEntryPoint."/api/v1/namespaces");
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);
  $namespaces = json_decode($output,true);
  $data = array();
  $nowtime = time();
  foreach($namespaces["items"] as $namespace){
    $nestedData=array();
    $nestedData['name'] = $namespace['metadata']['name'];
    $nestedData['labels'] = $namespace['metadata']['labels'];
    $nestedData['status'] = $namespace['status']['phase'];
    $starttime = $namespace['metadata']['creationTimestamp'];
    $nestedData['uptime'] = countTime($nowtime, $starttime);
    $data[] = $nestedData;
  }
  $json_data = array("data" => $data);
  echo json_encode($json_data);
}

function createNamespace(){
  global $apiEntryPoint;
  $comment = $_POST["comment"];
  if (!empty($comment)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$apiEntryPoint."/api/v1/namespaces/");
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

function deleteNamespace(){
  require('require/dbconfig.php');
  global $apiEntryPoint;
  $namespaces = $_POST["namespaces"];
  if (!empty($namespaces)) {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    for ($i=0;$i<count($namespaces);$i++) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL,$apiEntryPoint."/api/v1/namespaces/".$namespaces[$i]);
      curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_exec($ch);
      curl_close ($ch);
      try {
        $conn->exec("DELETE FROM student_containers WHERE namespace = '".$namespaces[$i]."';");
      }
      catch(PDOException $e)
      {
        echo "Error: " . $e->getMessage();
      }
    }
    $conn = null;
    echo "ok";
  }
}

function getPodsList(){
  global $apiEntryPoint;
  $namespace = $_POST["namespace"];
  if (!empty($namespace)) {
    $ch = curl_init();  
    if ($namespace == "all")
      curl_setopt($ch, CURLOPT_URL, $apiEntryPoint."/api/v1/pods");
    else
      curl_setopt($ch, CURLOPT_URL, $apiEntryPoint."/api/v1/namespaces/".$namespace."/pods");
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
      $nestedData['namespace'] = $pod['metadata']['namespace'];
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
}

function createPod(){
  require('require/dbconfig.php');
  global $apiEntryPoint;
  $namespace = $_POST["namespace"];
  $comment = $_POST["comment"];
  $students = $_POST["students"];
  if (!empty($namespace) && !empty($comment) && !empty($students)) {
    if ($namespace == "all"){
      echo "There is no namespace selected. Please select from namespaces.";
      return;
    } else {
      $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      foreach ($students as $student) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiEntryPoint."/api/v1/namespaces/".$namespace."/pods");
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/yaml"));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$comment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        if ( ( $http_code == "200" ) || ( $http_code == "201" ) || ( $http_code == "202" ) ) {
          $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
          $body = substr($output, $header_size);
          $json = json_decode($body,true);
          $name = $json['metadata']['name'];
          $uid = $json['metadata']['uid'];
          try {
            $conn->exec("INSERT INTO student_containers (student, name, uid, namespace, create_time) VALUES ('$student', '$name', '$uid', '$namespace', NOW());");
          }
          catch(PDOException $e)
          {
            echo "Error: " . $e->getMessage();
          }
        } else {
          $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
          $body = substr($output, $header_size);
          $json = json_decode($body,true);
          $message = $json['message'];
          $code = $json['code'];
          echo "Code: ".$code.", Message: ".$message.".";
          return;
        }
        curl_close ($ch);
      }
      $conn = null;
      echo "ok";
    }
  }
}

function getPodYaml(){
  global $apiEntryPoint;
  $pod = $_POST["pod"];
  $namespace = $_POST["namespace"];
  if (!empty($pod) && !empty($namespace)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEntryPoint."/api/v1/namespaces/".$namespace."/pods/".$pod);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($output, $header_size);
    echo $body;
    curl_close($ch);
  }
}

function editPod(){
  global $apiEntryPoint;
  $pod = $_POST["pod"];
  $namespace = $_POST["namespace"];
  $data = $_POST["data"];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEntryPoint."/api/v1/namespaces/".$namespace."/pods/".$pod);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, "$data");
    $output = curl_exec($ch);
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    if ($http_code == "200") {
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

function deletePod(){
  require('require/dbconfig.php');
  global $apiEntryPoint;
  $pods = $_POST["pods"];
  $namespaces = $_POST["namespaces"];
  if (!empty($pods) && !empty($namespaces)) {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    for ($i=0;$i<count($pods);$i++) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$apiEntryPoint."/api/v1/namespaces/".$namespaces[$i]."/pods/".$pods[$i]);
      curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_exec($ch);
      curl_close ($ch);
      try {
        $conn->exec("DELETE FROM student_containers WHERE name = '$pods[$i]';");
      }
      catch(PDOException $e)
      {
        echo "Error: " . $e->getMessage();
      }
    }
    $conn = null;
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

function getCurriculumName() {
  require('require/dbconfig.php');
  try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT id, name FROM class;");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
  }
  catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
  $conn = null;
}

function addPodList() {
  require('require/dbconfig.php');
  $requestData = $_REQUEST;
  $namespace = $_POST['namespace'];
  $classId = $_POST['classId'];
  if (!empty($namespace) || !empty($classId)) {
    try {
      $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $stmt = $conn->prepare("SELECT id, account, name FROM class_group,student WHERE class_group.student = student.id AND class = $classId AND student NOT IN (SELECT student FROM student_containers WHERE namespace = '$namespace');");
      $stmt->execute();
      $totalData = $stmt->rowCount();
      $totalFiltered = $totalData;
      $data = array();
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nestedData=array();
        $nestedData['id'] = $row['id'];
        $nestedData['account'] = $row['account'];
        $nestedData['name'] = $row['name'];
        $data[] = $nestedData;
      }
      $json_data = array(
        "draw" => intval($requestData['draw']),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
      );
      echo json_encode($json_data);
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
    $conn = null;
  } else {
    echo 'fail';
  }
}

if (function_exists($_GET['f'])) {
  $_GET['f']();
}
?>
