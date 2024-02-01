<?php
  require_once __DIR__ . "/def.php"; 
  require_once __DIR__ . "/utils.php";

  $USER_ID = filter_input(INPUT_POST,"user_id");

    try{
      $result=[];
      $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
      $db = new PDO($dsn,USERNAME,PASSWORD);
      $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    
      $db->beginTransaction();

      $sql = "SELECT TIMESTAMPDIFF(SECOND, POST_DATE, NOW()) AS UPLOAD_TIME FROM POSTS WHERE USER_ID = :user_id ORDER BY POST_DATE DESC LIMIT 1;";
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':user_id', $USER_ID, PDO::PARAM_INT);
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
  header("Content-Type: application/json");
  echo json_encode($result);
?>