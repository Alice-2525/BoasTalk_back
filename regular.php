<?php
  require_once __DIR__ . "/def.php"; 
  require_once __DIR__ . "/utils.php";

  $id = null;
  $user_id = filter_input(INPUT_POST,"user_id");
  if($_FILES["image"]["error"] == 0) {
    $_FILES["image"]["name"] = str_replace(array(" ","　"),"_",$_FILES["image"]["name"]);
    $image = "https://click.ecc.ac.jp/ecc/チーム名/images/".$user_id."/".$_FILES["image"]["name"];
  }else {
    $image = null;
  }
  $contents = filter_input(INPUT_POST,"contents");
  $post_date = date("Y/m/d H:i:s");
  // DB結果格納用配列
  $result = [
    "status" => true,
    "result" => null,
    "message" => null,
  ];

  // FTPサーバー名
  $host="";
  // ユーザー名
  $name="";
  // パスワード
  $pass="";

  try{
    $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
    $db = new PDO($dsn,USERNAME,PASSWORD);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->beginTransaction();

    $sql_cnt = "SELECT COUNT(user_id) FROM POSTS WHERE user_id = :user_id";
    $stmt = $db->prepare($sql_cnt);
    $stmt->bindParam("user_id",$user_id,PDO::PARAM_INT);
    $stmt->execute();

    if($result["status"]) {

      $sql = "INSERT INTO POSTS (id, user_id, image, contents, post_date) VALUES (:id, :user_id, :image, :contents, :post_date)";
      $stmt = $db->prepare($sql);
      
      $stmt-> bindParam("id",$id,PDO::PARAM_INT);
      $stmt-> bindParam("user_id",$user_id,PDO::PARAM_INT);
      $stmt-> bindParam("image",$image,PDO::PARAM_STR);
      $stmt-> bindParam("contents",$contents,PDO::PARAM_STR);
      $stmt-> bindParam("post_date",$post_date,PDO::PARAM_STR);

      $result["result"] = $stmt->execute();
      if($result["result"] == 1){
        $db->commit();
        $result["message"] = "登録完了";
      }

      if($_FILES["image"]["name"]){

        $sql = "SELECT * FROM USERS";
        $stmt = $db->prepare($sql);
        $stmt->execute();
  
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $result_user[] = $rows;
        }
  
        $ftp = ftp_connect($host);
        $login_result = ftp_login($ftp, $name, $pass);

        $directory_path = "images/".$user_id;
        if(!file_exists($directory_path)){
          // 権限を付与して作成
          if(mkdir($directory_path, 0777)) {
            chmod($directory_path, 0777);
          }
        }
  
        if($login_result) {
          $filename = $_FILES['image']['name'];
          
          if (file_exists($directory_path)) {
            // 保存するファイルパス
            $remoteFilePath = $directory_path."/".$filename;
            var_dump($remoteFilePath);
            // ファイルをアップロード
            $uploadResult = move_uploaded_file($_FILES['image']['tmp_name'],$remoteFilePath);
            // 接続を閉じる
            ftp_close($ftp);
            // アップロード結果の確認
            if ($uploadResult) {
              echo "アップロード成功";
            } else {
              echo "アップロード失敗";
            }
          }
        } else {
          echo "FTP 接続に失敗しました";
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
    <title>登録確認</title>
  </head>
  <body>
    <h1>登録確認</h1>
    <h3><?= $result["message"] ?></h3>
    <a href="posts.php">表示確認</a>
  </body>
</html>