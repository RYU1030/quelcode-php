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
function everyCombination($arrayedNums) {
  $allCombinations = array(array());
  foreach($arrayedNums as $arrayedNum) {
    foreach($allCombinations as $combination) {
      array_push($allCombinations, array_merge(array($arrayedNum), $combination));
    }
  }
  return $allCombinations;
}

/*echo json_encode(everyCombination($dbarry));*/

$forComparisons = everyCombination($dbarry);
foreach($forComparisons as $forComparison) {
  if(array_sum($forComparison) === (int)$limit) {
    $matchedCombinations[] = $forComparison;
  }
}
if(is_null($matchedCombinations)) {
  $matchedCombinations = [[]];
}

echo json_encode($matchedCombinations, JSON_NUMERIC_CHECK);