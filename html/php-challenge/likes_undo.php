<?php
  session_start();
  require('dbconnect.php');
  error_reporting(E_ALL);

  if(isset($_SESSION['id'])) {
    // リツイートする
    $like_undone_by = $_REQUEST['like_undone_by'];
    $likeUndone_post_id = $_REQUEST['likeUndone_post_id'];
    $likeUndo = $db->prepare('UPDATE likes SET deleteflag=1
    WHERE liked_post_id=? AND liked_by=?');
    $likeUndo->execute(array(
      $likeUndone_post_id,
      $like_undone_by
    ));
  }
  header('Location: index.php'); exit();
