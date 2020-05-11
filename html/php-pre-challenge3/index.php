<?php
$limit = $_GET['target'];

try {
  $db = new PDO('mysql:dbname=test;host=mysql;charset=utf8', 'test', 'test');
} catch(PDOExecption $e) {
  echo 'DB接続エラー: ' . $e->getMessage();
}

$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';

$records = $db->query("SELECT * FROM prechallenge3 WHERE value <= $limit ORDER BY value DESC");
foreach($records as $number) {
  $dbarry[] = $number['value'];
}
function everyCombination($nums) {
  $allCombinations = array(array());
  foreach($nums as $num) {
    foreach($allCombinations as $combination) {
      array_push($allCombinations, array_merge(array($num), $combination));
    }
  }
  return $allCombinations;
}

echo json_encode(everyCombination($dbarry));