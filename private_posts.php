<?php
  require_once __DIR__ . "/def.php";
  require_once __DIR__ . "/utils.php";

  $result=[];

  $user_id = filter_input(INPUT_POST,"user_id");

  try{
    // JSON形式で返す
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");

    $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
    $db = new PDO($dsn,USERNAME,PASSWORD);

    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

    $sql = "SELECT * FROM POSTS WHERE USER_ID = $user_id AND DISPLAY = 0;;";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
      $result[] = $rows;
    }

  }catch(PDOException $poe) {
    echo "DB接続エラー".$poe->getMessage();
  }finally{
    $stmt = null;
    $db = null;
  }

  // JSON形式で返す
  echo json_encode($result);

?>
