<?php
    require_once __DIR__ . "/def.php"; 
    require_once __DIR__ . "/utils.php";

    // DB結果格納用配列
    $result = [
        "status" => true,   // エラーがあるかないか（true or false）
        "result" => null,   // 結果を入れる
        "message" => null,  // メッセージを入れる
    ];

    // FTPサーバー名
    $host="";
    // ユーザー名
    $name="";
    // パスワード
    $pass="";

    //文字コード用変数
    $charset = "UTF-8";

    $firstName = filter_input(INPUT_POST,"firstName");
    $lastName = filter_input(INPUT_POST,"lastName");
    $mail = filter_input(INPUT_POST,"mail",FILTER_VALIDATE_EMAIL);
    $pass = filter_input(INPUT_POST,"pass");
    $fulname = null;
    // 苗字
    if($firstName != ""){
        $firstName = trim($firstName);
        $firstName = mb_convert_kana($firstName,"asKV");
        $firstName = htmlspecialchars($firstName,ENT_QUOTES,$charset);
        $firstName = nl2br($firstName);
    }else{
        $result["message"] = "苗字が入力されていません<br>";
        $result["status"] = false;
    }

    // 名前
    if($lastName != ""){
        $lastName = trim($lastName);
        $lastName = mb_convert_kana($lastName,"asKV");
        $lastName = htmlspecialchars($lastName,ENT_QUOTES,$charset);
        $lastName = nl2br($lastName);
    }else{
        $result["message"] .= "名前が入力されていません<br>";
        $result["status"] = false;
    }

    $fulname = $firstName;
    $fulname .= " ";
    $fulname .= $lastName;

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
        echo $pass;
        // ハッシュ
        $pass = hash('sha256',$pass);
    }else{
        $result["message"] .= "パスワードが入力されていません<br>";
        $result["status"] = false;
    }

    // --------------------------------------------------------

    if($result["status"]){
        try{
            $dsn = "mysql:host=".HOSTNAME.";dbname=". DATABASE.";charset=".CHARSET."";
            $db = new PDO($dsn,USERNAME,PASSWORD);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            $db->beginTransaction();
        
            if($result["status"]) {
                $sql = "INSERT INTO `USERS`(`name`, `mail`, `passwd`) VALUES (:fulname, :mail, :pass)";
                $stmt = $db->prepare($sql);
                
                $stmt-> bindParam("fulname",$fulname,PDO::PARAM_STR);
                $stmt-> bindParam("mail",$mail,PDO::PARAM_STR);
                $stmt-> bindParam("pass",$pass,PDO::PARAM_STR);
        
                $result["result"] = $stmt->execute();

                if($result["result"] == 1){
                    $db->commit();
                    $result["message"] = "成功しました。";
                }else{
                    $result["message"] = "例外エラーが発生しました。";
                }
            }
        }catch(PDOException $poe) {
            echo "DB接続エラー".$poe->getMessage();
        }finally{
            $stmt = null;
            $db = null;
        }
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント作成結果</title>
</head>
<body>
    <p><?= $firstName ?></p>
    <p><?= $lastName ?></p>
    <p><?= $mail ?></p>
    <p><?= $pass ?></p>
    <p><?= $fulname ?></p>
    <h3><?= $result["message"] ?></h3>
</body>
</html>