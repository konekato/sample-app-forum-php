<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>毎日のご飯掲示板</title>
</head>
<body>
    <?php
        // DB接続設定
        $dsn = '<dsn>';
        $db_user = '<DB user>';
        $db_password = '<DB password>';
        $pdo = new PDO($dsn, $db_user, $db_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        
        // フォーム初期値設定
        $name_form = "";
        $comment_form = "";
        $password_form = "";
        $edited_id_form = -1;
        $error_message = "";
        
        if (!empty($_POST)) {
            // 送信フォーム処理
            if ($_POST['submit'] == "送信") {
                $name = $_POST["name"];
                $comment = $_POST["comment"];
                $password = $_POST["password"];
                
                // 編集対象のID
                $edited_id = (int) $_POST["edited_id"];
                
                // 編集対象のIDが0以下の時は、新規投稿
                if ($edited_id <= 0) {
                    // 新規投稿として、Insert
                    $sql = $pdo -> prepare("INSERT INTO comments (name, comment, password) VALUES (:name, :comment, :password)");
                    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                    $sql -> execute();
                    
                // 編集
                } else {
                    // 編集投稿として、Update
                    $sql = 'UPDATE comments SET name=:name,comment=:comment,password=:password WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $edited_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
                
                
            // 削除フォーム処理
            } elseif ($_POST['submit'] == "削除") {
                $delete_id = $_POST['delete_id'];
                $password = $_POST["password_for_delete"];
                
                // DELETE
                $sql = 'DELETE FROM comments WHERE id=:id AND password=:password';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->execute();
                $count = $stmt->rowCount();
                
                if (!$count) {
                    $error_message = "指定した削除対象番号、または、パスワードが間違っています。";
                }
                
            // 編集フォーム処理
            } elseif ($_POST["submit"] == "編集") {
                $edit_id = $_POST["edit_id"];
                $password = $_POST["password_for_edit"];
                
                // ID と パスワード が一致するコメントを選択
                $sql = 'SELECT * FROM comments WHERE id=:id AND password=:password';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $edit_id, PDO::PARAM_INT);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->execute();
                // 一件取得
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!empty($result)) {
                    // フォームに入力
                    $name_form = $result["name"];
                    $comment_form = $result["comment"];
                    $password_form = $result["password"];
                    $edited_id_form = $result["id"];
                } else {
                    $error_message = "指定した編集対象番号、または、パスワードが間違っています。";
                }
            }
        }
    ?>
    
    <h1 style="text-align: center">毎日のご飯掲示板</h1>
    <p style="text-align: center">毎日のご飯を投稿しよう！</p>
    
    <div style="padding: 0 3rem 1rem 3rem">
        <h2>投稿フォーム</h2>
        <form action="" method="post">
            名前：<input type="text" name="name" required placeholder="お名前" value=<?= $name_form ?>>
            コメント：<input type="text" name="comment" required placeholder="朝ごはんはオムライス！" value=<?= $comment_form ?>>
            パスワード：<input type="text" name="password" value=<?= $password_form ?>>
            <input type="hidden" name="edited_id" value=<?= $edited_id_form ?>>
            <input type="submit" name="submit">
        </form>
        <form action="" method="post">
            削除対象番号：<input type="number" name="delete_id" required>
            パスワード：<input type="text" name="password_for_delete">
            <input type="submit" name="submit" value="削除">
        </form>
        <form action="" method="post">
            編集対象番号：<input type="number" name="edit_id" required>
            パスワード：<input type="text" name="password_for_edit">
            <input type="submit" name="submit" value="編集">
        </form>
        <div style="color: red;"><?= $error_message ?></div>
    </div>
    
    <hr>
    
    <div style="padding: 1rem 3rem">
    <?php
        // 以下、表示処理
        $sql = 'SELECT id, name, comment, created_at FROM comments';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            echo $row['id'].' ';
            echo $row['name'].' ';
            echo $row['comment'].' ';
            echo $row['created_at'].'<br>';
        }

    ?>
    </div>
</body>
</html>