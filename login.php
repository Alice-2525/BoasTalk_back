<?php
    // ファイルの取り込み
    require_once __DIR__ . "/def.php"; 
    require_once __DIR__ . "/utils.php";

     // DB結果格納用配列
     $result = [
        "status" => true,
        "result" => null,
        "message" => null,
    ];

    //文字コード用変数
    $charset = "UTF-8";

    // フィルタリング
    $mail = filter_input(INPUT_POST,"mail",FILTER_VALIDATE_EMAIL);
    $pass = filter_input(INPUT_POST,"pass");

    // メールアドレス
    if($mail != ""){
        // trimで空白消去
        $mail = trim($mail);
        // mb_convert_kanaで文字の形式を指定
        $mail = mb_convert_kana($mail,"rVn");
        // 特殊文字出力
        $mail = htmlspecialchars($mail,ENT_QUOTES,$charset);
    }else{
        $result["message"] .= "メールアドレスが入力されていません<br>";
        $result["status"] = false;
    }

    // パスワード
    if($pass != ""){
        // mb_convert_kanaで文字の形式を指定
        $mail = mb_convert_kana($mail,"asV");
        // 特殊文字出力
        $pass = htmlspecialchars($pass,ENT_QUOTES,$charset);
        // ハッシュ
        $pass = hash('sha256',$pass);
    }else{
        $result["message"] .= "パスワードが入力されていません<br>";
        $result["status"] = false;
    }

    //　ーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーーー

    try{
        //　定型分
        $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
        $db = new PDO($dsn,USERNAME,PASSWORD);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $db->beginTransaction();

        if($result["status"]) {
            $sql = "SELECT NAME FROM `USERS` WHERE MAIL = :mail AND PASSWD = :pass";
            $stmt = $db->prepare($sql);
            $stmt-> bindParam(":mail",$mail,PDO::PARAM_STR);
            $stmt-> bindParam(":pass", $pass, PDO::PARAM_STR); // implode()は不要

            $stmt->execute();

            $result["result"] = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result["result"]){
                $result["message"] = $result["result"]["NAME"] . "さんようこそ！！";
            }else{
                $result["message"] = "ユーザーが見つかりませんでした。<br>メールアドレスまたは、パスワードが違います。";
            }
        }
    }catch(PDOException $poe) {
        echo "DB接続エラー".$poe->getMessage();
    }finally{
        $stmt = null;
        $db = null;
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン結果</title>
</head>
<body>
    <h1>ログイン結果</h1>
    <p><?= $mail ?></p>
    <p><?= $pass ?></p>
    <h2><?= $result["message"] ?></h2>
</body>
</html>