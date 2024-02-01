<?php
  require_once __DIR__ . "/def.php";
  require_once __DIR__ . "/utils.php";

  $id = null;
  $user_id = filter_input(INPUT_POST,"user_id");
  $image = null;
  $contents = filter_input(INPUT_POST,"contents");
  $post_date = date("Y/m/d H:i:s");

  // DB結果配列
  $result = [
    "status" => true,
    "result" => null,
  ];

  // 画像ファイルの拡張子
  $list = array( 'jpg', 'jpeg', 'png', 'gif' );

  // FTPサーバー名
  $host="";
  // ユーザー名
  $name="";
  // パスワード
  $pass="";

  try{
    // FTP通信の処理
    if($_FILES["image"]["name"]){
      // ファイル名
      $filename = $_FILES['image']['name'];
      // ファイル名に空白が入っていたらアンダーバーに置き換える
      $filename = str_replace(array(" ","　"),"_",$filename);
      // ファイル名の拡張子
      $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );

      // ログイン処理
      $ftp = ftp_connect($host);
      $login_result = ftp_login($ftp, $name, $pass);

      // images/user_id フォルダ の作成
      $user_path = "images/".$user_id;
      if(!file_exists($user_path)){
        if(mkdir($user_path, 0777)) {
          chmod($user_path, 0777);
        }
      }

      // images/user_id/日付 フォルダの作成
      $directory_path = $user_path."/".date("Y-m-d");
      if(!file_exists($directory_path)){
        if(mkdir($directory_path, 0777)) {
          chmod($directory_path, 0777);
        }
      }

      if($login_result) {
        // 拡張子が画像ファイルの拡張子と一致してるか確認
        if( in_array( $extension, $list ) ){
          if (file_exists($directory_path)) {
            // 保存するファイルパス
            $remoteFilePath = $directory_path."/".$filename;
            // $imageに代入(DB挿入に使う)
            $image = "https://click.ecc.ac.jp/ecc/チーム名/".$remoteFilePath;
            // ファイルをアップロード
            $uploadResult = move_uploaded_file($_FILES['image']['tmp_name'],$remoteFilePath);
            // 接続を閉じる
            ftp_close($ftp);
          }
        } else{
          echo "アップロードできるのは画像のみです";
        }
      } else {
        echo "FTP 接続に失敗しました";
      }
    }

    // ここからDBの処理
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
        // posts.phpにリダイレクト
        $success_url = "https://click.ecc.ac.jp/ecc/チーム名/posts.php";
        header("Location: {$success_url}");
      }
    }
  }catch(PDOException $poe) {
    echo "DB接続エラー".$poe->getMessage();
  }finally{
    $stmt = null;
    $db = null;
  }
?>