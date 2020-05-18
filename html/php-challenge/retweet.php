<?php
session_start();
require('dbconnect.php');
error_reporting(E_ALL);

if (isset($_SESSION['id'])) {
  // リツイートする
  $tweeted_by = $_REQUEST['tweeted_by'];
  $retweeted_message = $_REQUEST['retweeted_message'];
  $retweeted_by = $_REQUEST['retweeted_by'];
  $retweeted_post_id = $_REQUEST['retweeted_post_id'];
  $message = $db->prepare('INSERT INTO posts SET 
      member_id=?, message=?, retweeted_post_id=?, retweeted_by=?, created=NOW()');
  $message->execute(array(
    $tweeted_by,
    $retweeted_message,
    $retweeted_post_id,
    $retweeted_by,
  ));
}
header('Location: index.php');
exit();
