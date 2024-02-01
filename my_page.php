<?php
  require_once __DIR__ . "/def.php";
  require_once __DIR__ . "/utils.php";

  $result;

  // JSON
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Origin: *");
  try{
    // DB接続
    $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
    $db = new PDO($dsn,USERNAME,PASSWORD);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    $db->beginTransaction();

    // 名前といいねされた数を表示
    $sql = "SELECT u.NAME, COUNT(l.post_id) AS LIKES FROM USERS u
              LEFT JOIN POSTS p ON u.id = p.user_id
              LEFT JOIN LIKES l ON p.id = l.post_id
              GROUP BY u.id";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  }catch(PDOException $poe) {
    echo "DB接続エラー".$poe->getMessage();
  }finally{
    $stmt = null;
    $db = null;
  }
  // JSON
  echo json_encode($result);
?>