<?php
    // データベースに接続
    $link = mysqli_connect('localhost','root','');
    if(!$link) {
        die('データベースに接続できません:'.mysqli_error());
    }

    // データベースを選択する
    mysqli_select_db($link,'online_bbs');

    $errors = array();

    // POSTなら保存処理実行
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 名前が正しく入力されているかチェック
        $name = null;
        if (!isset($_POST['name']) || !strlen($_POST['name'])) {
            $errors['name'] = '名前を入力してください';
        } else if(strlen($_POST['name']) > 40) {
            $errors['name'] = '名前は40文字以内で入力してください';
        } else {
            $name = $_POST['name'];
        }

        // ひとことが正しく入力されているかチェック
        $comment = null;
        if(!isset($_POST['comment']) || !strlen($_POST['comment'])) {
            $errors['comment'] = 'ひとことを入力してください';
        } else if(strlen($_POST['comment']) > 200) {
            $comment = $_POST['comment'];
        }

        /* 文字セットを utf8 に変更する */
        if (!mysqli_set_charset($link, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($link));
            exit();
        } else {
            printf("Current character set: %s\n", mysqli_character_set_name($link));
        }

        // エラーがなければ保存
        if (count($errors) === 0) {
            $name = mysqli_real_escape_string($link,$name);
            $comment = mysqli_real_escape_string($link,$name);
            $date = date('Y-m-d H:i:s');

            // 保存するためのSQL文を作成
            $sql = "INSERT INTO post (name,comment,created_at) VALUES (
                '$name','$comment','$date')";
            // 保存する
            mysqli_query($link,$sql);
        }

    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ひとこと掲示板</title>
</head>
<body>
    <h1>ひとこと掲示板</h1>
    <form action="oneline_bbs.php" method="post">
    <?php if (count($errors)): ?>
    <ul class="error_list">
        <?php foreach ($errors as $error): ?>
        <li>
            <?php echo htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?>
        </li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>
        名前:<input type="text" name="name" /><br />
        ひとこと:<input type="text" name="comment" size="60" /><br />
        <input type="submit" name="submit" value="送信"/>
    </form>

    <?php
        // 投稿された内容を取得するSQLを作成して結果を取得
        $sql = "SELECT * FROM post ORDER BY created_at DESC";
        $result = mysqli_query($link,$sql);

        // 取得した結果を$postsに格納
        // $posts = array();
        // if($result !== false && mysqli_num_rows($result)) {
        //     while ($post = mysqli_fetch_assoc($result)) {
        //         $posts[] = $post;
        //     }
        // }

        // mysqli_free_result($result);
        // mysqli_close($link)

    ?>

    <?php if ($result !== false && mysqli_num_rows($result)): ?>
    <ul>
        <?php while ($post = mysqli_fetch_assoc($result)):?>
        <li>
            <?php echo htmlspecialchars($post['name'],ENT_QUOTES,'UTF-8'); ?>;
            <?php echo htmlspecialchars($post['comment'],ENT_QUOTES,'UTF-8'); ?>;
            <?php echo htmlspecialchars($post['created_at'],ENT_QUOTES,'UTF-8'); ?>;
        </li>
    <?php endwhile; ?>
    </ul>
<?php endif; ?>
<?php
// 取得結果を解放して接続を閉じる
mysqli_free_result($result);
mysqli_close($link);
print_r($_SERVER['HTTP_HOST']);
print_r($_SERVER['REQUEST_URI']);
// header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
exit;
?>

</body>
</html>