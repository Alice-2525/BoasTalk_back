<?php
  require_once __DIR__ . "/def.php";
  require_once __DIR__ . "/utils.php";

  $id = filter_input(INPUT_POST,"id");  // 投稿ID
  $delete = filter_input(INPUT_POST,"delete");  // 表示(0)、非表示(1)

  try{
    // JSON形式で返す
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");

    $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
    $db = new PDO($dsn,USERNAME,PASSWORD);

    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

    $sql = "UPDATE `POSTS` SET `DISPLAY`=$delete WHERE ID = $id";
    $stmt = $db->prepare($sql);
    $stmt->execute();

  }catch(PDOException $poe) {
    echo "DB接続エラー".$poe->getMessage();
  }finally{
    $stmt = null;
    $db = null;
  }

?>
