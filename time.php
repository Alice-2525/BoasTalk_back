<?php
  require_once __DIR__ . "/def.php"; 
  require_once __DIR__ . "/utils.php";

  $result=[];

  try{
    $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
    $db = new PDO($dsn,USERNAME,PASSWORD);

    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    
    $sql = "SELECT * FROM POSTS WHERE date_format(POST_DATE, '%Y-%m-%d') = CURRENT_DATE AND DISPLAY = 0;";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
      $result[] = $rows;
    }

    echo "<pre>";
    print_r($result);
    echo "</pre>";

  }catch(PDOException $poe) {
    echo "DB接続エラー".$poe->getMessage();
  }finally{
    $stmt = null;
    $db = null;
  }

?>