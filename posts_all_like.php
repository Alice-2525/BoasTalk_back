<?php
  require_once __DIR__ . "/def.php";
  require_once __DIR__ . "/utils.php";

  $result=[];
  $id = null;
  $result_like;

  try{
    // DB接続
    $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
    $db = new PDO($dsn,USERNAME,PASSWORD);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    $db->beginTransaction();

    // ポスト表示
    $sql = "SELECT * FROM POSTS WHERE DISPLAY = 0";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
      $result[] = $rows;
    }

    foreach ($result as $r) {
      $like_button = "like_" . $r["ID"];
      if (isset($_POST[$like_button])) {
        $user_id = filter_input(INPUT_POST,"user_id");
        $post_id = filter_input(INPUT_POST, "post_id_".$r["ID"]);

        // いいねの重複
        $sql_cnt = "SELECT COUNT(post_id) FROM LIKES WHERE user_id = :user_id AND post_id = :post_id";
        $stmt_check = $db->prepare($sql_cnt);
        $stmt_check->bindParam('user_id',$user_id);
        $stmt_check->bindParam('post_id',$post_id);
        $stmt_check->execute();
        $a = $stmt_check->fetchColumn();

        if($a == 0){
          $sql = "INSERT INTO LIKES (id, user_id, post_id) VALUES (:id, :user_id, :post_id)";
          $stmt = $db->prepare($sql);
    
          $stmt-> bindParam("id",$id,PDO::PARAM_INT);
          $stmt-> bindParam("user_id",$user_id,PDO::PARAM_INT);
          $stmt-> bindParam("post_id",$post_id,PDO::PARAM_INT);
    
          $result_like = $stmt->execute();
          if($result_like == 1){
              $db->commit();
          }
        }
      }
    }
  }catch(PDOException $poe) {
    echo "DB接続エラー".$poe->getMessage();
  }finally{
    $stmt = null;
    $db = null;
  }
?>
<html>
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ポスト表示</title>
  </head>
  <body>
    <h2>データベース内のポスト確認（DISPLAY=1は表示されない）</h2>
    <h2>❤️ボタンでいいねの入力ができます</h2>
    <?php foreach ($result as $r) : ?>
      <?php if(!$r["IMAGE"] == null) : ?>
        <img src=<?= $r["IMAGE"]?> />
      <?php endif ?>
      <p><?= $r["CONTENTS"]?></p>
      <form action="" method="post"  novalidate>
        <input type="hidden" name="user_id" value="<?= $r["USER_ID"]?>">
        <input type="hidden" name="post_id_<?= $r["ID"]?>" value="<?= $r["ID"]?>">
        <button type="submit" name="like_<?= $r["ID"] ?>">❤️</button><br>
      </from>
    <?php endforeach ?>
  </body>
</html>