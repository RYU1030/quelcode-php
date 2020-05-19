<?php
session_start();
require('dbconnect.php');
error_reporting(E_ALL);

if (isset($_SESSION['id'])) {
	$id = $_REQUEST['id'];
	
	// 投稿を検査する
	$messages = $db->prepare('SELECT * FROM posts WHERE id=?');
	$messages->execute(array($id));
	$message = $messages->fetch();
	
	// リツイート元が削除されると、リツイートされた投稿も削除される
	if ($message['member_id'] === $_SESSION['id']) {
		// 論理削除
		$del = $db->prepare('UPDATE posts SET delete_flag=1 WHERE id=? OR retweeted_post_id=?');
		$del->execute(array(
			$id,
			$id
		));
	}
	// 自身のツイートのリツイートを削除した際も、
	// リツイート元及びリツイートされた投稿の全てを削除する
	if ($message['retweeted_post_id'] > 0) {
		if ($message['member_id'] === $_SESSION['id']) {
				// 論理削除
			$del = $db->prepare('UPDATE posts SET delete_flag=1 WHERE id=? OR retweeted_post_id=? OR id=?');
			$del->execute(array(
				$id,
				$message['retweeted_post_id'],
				$message['retweeted_post_id']
			));
		}
	}
}

header('Location: index.php'); exit();
