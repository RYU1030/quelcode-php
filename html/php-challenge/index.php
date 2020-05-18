<?php
session_start();
require('dbconnect.php');
//error_reporting(E_ALL);

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	$_SESSION['time'] = time();

	$members = $db->prepare('SELECT * FROM members WHERE id=?');
	$members->execute(array($_SESSION['id']));
	$member = $members->fetch();
} else {
	// ログインしていない
	header('Location: login.php');
	exit();
}

// 投稿を記録する
if (!empty($_POST)) {
	if ($_POST['message'] != '') {
		$message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
		$message->execute(array(
			$member['id'],
			$_POST['message'],
			$_POST['reply_post_id']
		));

		header('Location: index.php');
		exit();
	}
}

// 投稿を取得する
$page = $_REQUEST['page'];
if ($page == '') {
	$page = 1;
}
$page = max($page, 1);

// 最終ページを取得する
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts WHERE deleteflag=0');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;
$start = max(0, $start);

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p
    WHERE m.id=p.member_id AND p.deleteflag=0 ORDER BY created DESC LIMIT ?, 5');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

// 返信の場合
if (isset($_REQUEST['res'])) {
	$response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
	$response->execute(array($_REQUEST['res']));

	$table = $response->fetch();
	$message = '@' . $table['name'] . ' ' . $table['message'];
}

// htmlspecialcharsのショートカット
function h($value)
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 本文内のURLにリンクを設定します
function makeLink($value)
{
	return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>', $value);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
	<link rel="stylesheet" href="style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>ひとこと掲示板</h1>
		</div>
		<div id="content">
			<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
			<form action="" method="post">
				<dl>
					<dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
					<dd>
						<textarea name="message" cols="50" rows="5"><?php echo h($message); ?></textarea>
						<input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res']); ?>" />
					</dd>
				</dl>
				<div>
					<p>
						<input type="submit" value="投稿する" />
					</p>
				</div>
			</form>

			<?php
			foreach ($posts as $post) :
			?>
				<div class="msg">
					<img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
					<!-- 投稿がリツイートされたものの場合は、リツイートしたユーザの名前を投稿の上に表示 -->
					<?php if ($post['retweeted_post_id'] > 0) : ?>
						<?php
						$whoRTed = $db->prepare('SELECT name FROM members WHERE id=?');
						$whoRTed->execute(array($post['retweeted_by']));
						$whoRTed = $whoRTed->fetch();
						?>
						<p style="font-size: 11px; color: #007700;"><?php echo $whoRTed['name']; ?> さんがリツイートしました</p>
					<?php endif; ?>
					<p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($post['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
					<p class="day"><a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
						<?php
						if ($post['reply_post_id'] > 0) :
						?>
							<a href="view.php?id=<?php echo
																			h($post['reply_post_id']); ?>">
								返信元のメッセージ</a>
						<?php
						endif;
						?>

						<?php
						// ログインしているユーザが、表示されている投稿をリツイート済みかのチェック
						$checkRT = $db->prepare('SELECT COUNT(*) AS rtCheck FROM posts WHERE (id=? OR retweeted_post_id=?) 
		AND retweeted_by=? AND deleteflag=0');
						if ($post['retweeted_by'] == 0) {
							$checkRT->execute(array(
								$post['id'],
								$post['id'],
								$_SESSION['id']
							));
						} else {
							$checkRT->execute(array(
								$post['retweeted_post_id'],
								$post['retweeted_post_id'],
								$_SESSION['id']
							));
						}
						$checkRT = $checkRT->fetch();
						if ($checkRT['rtCheck'] > 0) :
						?>
							<!-- 既にリツイート済みの場合は、unretweet.phpへのリンクを設置  -->
							<a href="unretweet.php?unretweeted_post_id=
			<?php
							if ($post['retweeted_post_id'] > 0) {
								echo $post['retweeted_post_id'];
							} else {
								echo $post['id'];
							}
			?>
			<?php echo '&unretweeted_by=' ?><?php echo h($_SESSION['id']); ?>
			" style="text-decoration: none; color: #007700;"><i class="fas fa-retweet"></i>
							<?php else : ?>
								<!-- リツイートしていない投稿の場合は、retweet.phpへのリンクを設置 -->
								<a href="retweet.php?retweeted_post_id=
			<?php
							if ($post['retweeted_post_id'] > 0) {
								echo $post['retweeted_post_id'];
							} else {
								echo $post['id'];
							}
			?>
			<?php echo '&tweeted_by=' ?><?php echo h($post['member_id']); ?>
			<?php echo '&retweeted_message=' ?><?php echo h($post['message']); ?>
			<?php echo '&retweeted_by=' ?><?php echo h($_SESSION['id']); ?>
			" style="text-decoration: none;"><i class="fas fa-retweet"></i>
								<?php endif; ?>
								<!-- リツイート数の表示  -->
								<?php
								$rtCounts = $db->prepare('SELECT COUNT(*) as rtCnt FROM posts 
						WHERE retweeted_post_id=? AND deleteflag = 0 GROUP BY retweeted_post_id;');
								if ($post['retweeted_post_id'] == 0) {
									$rtCounts->execute(array($post['id']));
								} else {
									$rtCounts->execute(array($post['retweeted_post_id']));
								}
								$rtCounts = $rtCounts->fetch();
								echo $rtCounts['rtCnt'];
								?>
								</a>

								<?php
								// ログインしているユーザが、表示されている投稿を「いいね」済みかのチェック
								$checkLike = $db->prepare('SELECT COUNT(*) AS likeCheck FROM likes WHERE liked_post_id=? 
						AND liked_by=? AND deleteflag=0');
								if ($post['retweeted_post_id'] == 0) {
									$checkLike->execute(array(
										$post['id'],
										$_SESSION['id'],
									));
								} else {
									$checkLike->execute(array(
										$post['retweeted_post_id'],
										$_SESSION['id']
									));
								}
								$checkLike = $checkLike->fetch();
								// 「いいね」済みの投稿には、likes_undo.phpへのリンクを設置
								if ($checkLike['likeCheck'] > 0) : ?>
									<a href="likes_undo.php?likeUndone_post_id=
						<?php
									if ($post['retweeted_post_id'] > 0) {
										echo $post['retweeted_post_id'];
									} else {
										echo $post['id'];
									}
						?>
							<?php echo '&like_undone_by=' ?><?php echo h($_SESSION['id']); ?>
							" style="text-decoration: none; color: #FF0000;"><i class="fas fa-heart"></i>
									<?php else : ?>
										<!-- 「いいね」していない投稿の場合は、likes_do.phpへのリンクを設置 -->
										<a href="likes_do.php?liked_post_id=
						<?php
									if ($post['retweeted_post_id'] > 0) {
										echo $post['retweeted_post_id'];
									} else {
										echo $post['id'];
									}
						?>
							<?php echo '&liked_by=' ?><?php echo h($_SESSION['id']); ?>
							" style="text-decoration: none;"><i class="fas fa-heart"></i>
										<?php endif; ?>
										<!-- 「いいね」カウントの表示 -->
										<?php
										$likeCounts = $db->prepare('SELECT COUNT(*) as likeCnt FROM likes 
						WHERE liked_post_id=? AND deleteflag = 0 GROUP BY liked_post_id;');
										if ($post['retweeted_post_id'] == 0) {
											$likeCounts->execute(array($post['id']));
										} else {
											$likeCounts->execute(array($post['retweeted_post_id']));
										}
										$likeCounts = $likeCounts->fetch();
										echo $likeCounts['likeCnt'];
										?>
										</a>

										<?php
										if ($post['retweeted_post_id'] == 0 || $_SESSION['id'] == $post['retweeted_by']) {
											if ($_SESSION['id'] == $post['member_id']) {
										?>
												[<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color: #F33;">削除</a>]
										<?php
											}
										}
										?>
					</p>
				</div>
			<?php
			endforeach;
			?>

			<ul class="paging">
				<?php
				if ($page > 1) {
				?>
					<li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
				<?php
				} else {
				?>
					<li>前のページへ</li>
				<?php
				}
				?>
				<?php
				if ($page < $maxPage) {
				?>
					<li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
				<?php
				} else {
				?>
					<li>次のページへ</li>
				<?php
				}
				?>
			</ul>
		</div>
	</div>
</body>

</html>