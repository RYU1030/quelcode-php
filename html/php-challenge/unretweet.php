<?php
session_start();
require('dbconnect.php');
error_reporting(E_ALL);

if (isset($_SESSION['id'])) {
  // リツイートのキャンセル処理
  $unretweeted_by = $_REQUEST['unretweeted_by'];
  $unretweeted_post_id = $_REQUEST['unretweeted_post_id'];
  $unretweet = $db->prepare('UPDATE posts SET deleteflag=1 WHERE
     (id=? OR retweeted_post_id=?) AND retweeted_by=?');
  $unretweet->execute(array(
    $unretweeted_post_id,
    $unretweeted_post_id,
    $unretweeted_by
  ));
}
header('Location: index.php');
exit();
