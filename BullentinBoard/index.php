<?php
var_dump($_POST);

// メッセージを保存するファイルのパス設定
//define( 'FILENAME', './message.txt');

// 変数の初期化
$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$clean = array();

// データベースの接続情報
define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
//define( 'DB_PASS', 'password');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

//セッション開始
session_start();

// 書き込みボタンが押されたら
if( !empty($_POST['btn_submit']) ) {

	// 表示名入力チェックバリデーション
	if(empty($_POST['view_name'])) {
		$error_message[] ='表示名を入力してください。';
	} else {
		$clean['view_name'] = htmlspecialchars($_POST['view_name'], ENT_QUOTES);
		$clean['view_name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['view_name']);
	}
	
	// セッションに表示名を保存
	$_SESSION['view_name'] = $clean['view_name'];

	// メッセージの入力チェック
	if( empty($_POST['message']) ) {
		$error_message[] = 'ひと言メッセージを入力してください。';
	} else {
		$clean['message'] = htmlspecialchars($_POST['message'], ENT_QUOTES);
		//$clean['message'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['message']);
	}

	// バリデーションエラーがなければファイル書き込みOK
	if(empty($error_message)) {

		// データベース接続
		$mysqli = new mysqli(DB_HOST, DB_USER, '', DB_NAME);

		// 接続エラーの確認
		if( $mysqli->connect_errno ) {
			$error_message[] = '書き込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
		} else {
			// 文字コード設定
			$mysqli->set_charset('utf8');

			// 書き込み日時を取得
			$now_date = date("Y-m-d H:i:s");
			var_dump($now_date);
			// データを登録するSQL作成
			$sql = "INSERT INTO message (view_name, message, post_date) VALUES ('$clean[view_name]', '$clean[message]', '$now_date')";

			// データを登録
			$res = $mysqli->query($sql);
			
			if( $res ) {
				$success_message = 'メッセージを書き込みました。';
			} else {
				$error_message[] = '書き込みに失敗しました。';
			}
		
			// データベースの接続を閉じる
			$mysqli->close();
		}
	}
}

// 読み込み
// データベースに接続
$mysqli = new mysqli( DB_HOST, DB_USER, '', DB_NAME);

// 接続エラーの確認
if( $mysqli->connect_errno ) {
	$error_message[] = 'データの読み込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} else {

	$sql = "SELECT view_name, message, post_date FROM message ORDER BY post_date DESC";
	$res = $mysqli->query($sql);
	var_dump($res);
	if( $res ) {
		$message_array = $res->fetch_all(MYSQLI_ASSOC);
	}
	
	$mysqli->close();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="css/main.css">
<title>ひと言掲示板</title>

</head>
<body>
<h1>ひと言掲示板</h1>
<!-- ここにメッセージの入力フォームを設置 -->

<?php if(!empty($success_message)) : ?>
	<p class="success_message"><?php echo $success_message; ?></p>
<?php endif; ?>

<?php if( !empty($error_message) ): ?>
	<ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
			<li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form method="post">
	<div>
		<label for="view_name">表示名</label>
		<input id="view_name" type="text" name="view_name"
			   value="<?php if( !empty($_SESSION['view_name']) ){ echo $_SESSION['view_name']; } ?>">
	</div>
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message"></textarea>
	</div>
	<input type="submit" name="btn_submit" value="書き込む">
</form>
<hr>
<section>
<!-- ここに投稿されたメッセージを表示 -->

<?php if( !empty($message_array) ): ?>
<?php foreach( $message_array as $value ): ?>
<!-- article:内容が単体で完結するセクションである -->
<article>
    <div class="info">
        <h2><?php echo $value['view_name']; ?></h2>
        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
    </div>
    <p><?php echo nl2br($value['message']); ?></p>
</article>

<?php endforeach; ?>
<?php endif; ?>
</section>
</body>
</html>
