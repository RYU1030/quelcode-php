<?php
session_start();
require('dbconnect.php');
error_reporting(E_ALL);

if (isset($_SESSION['id'])) {
  // 「いいね」登録処理
  $liked_by = $_REQUEST['liked_by'];
  $liked_post_id = $_REQUEST['liked_post_id'];
  $newLike = $db->prepare('INSERT INTO likes SET 
    liked_post_id=?, liked_by=?');
  $newLike->execute(array(
    $liked_post_id,
    $liked_by
  ));
}
header('Location: index.php');
exit();
