<?php
$limit = mb_convert_kana($_GET['target'], 'a', 'UTF-8');
if(!is_numeric($limit) || $limit <= 0 || preg_match('/^([1-9]\d*|0)\.(\d+)?$/', $limit)) {
  http_response_code(400);
  echo json_encode('1以上の整数をターゲット値としてください', JSON_UNESCAPED_UNICODE);
  exit();
}

$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';

try {
  $db = new PDO($dsn,$dbuser,$dbpassword);
} catch(PDOException $e) {
  http_response_code(500);
  $errMsg = 'DB接続エラー: ' . $e->getMessage();
  echo json_encode($errMsg, JSON_UNESCAPED_UNICODE);
  exit();
}

/* ターゲット値以下の数値をデータベースより取得し、配列($dbarry)に格納 */
$records = $db->prepare("SELECT * FROM prechallenge3 WHERE value <= ? ORDER BY value");
$records->bindParam(1, $limit, PDO::PARAM_INT);
$records->execute();
foreach($records as $number) {
  $dbarry[] = $number['value'];
}

/* 任意の配列について、格納されている数値の組合せを配列として全列挙し、
   配列$allCombinationsに格納するファンクション */
function everyCombination($arrayedNums) {
  $allCombinations = array(array());
  foreach($arrayedNums as $arrayedNum) {
    foreach($allCombinations as $combination) {
      array_push($allCombinations, array_merge(array($arrayedNum), $combination));
    }
  }
  return $allCombinations;
}

/* データベースに保存されている数値の組合せを全て列挙し、それらを$forComparisonsに格納 */
$forComparisons = everyCombination($dbarry);

/* データベースから取得した数値の各組合せについて、その和とターゲット値を比較し、
   等値の組合せを出力対象の配列($matchedCombinations)に格納 */
foreach($forComparisons as $forComparison) {
  if(array_sum($forComparison) === (int)$limit) {
    $matchedCombinations[] = $forComparison;
  }
}
if(is_null($matchedCombinations)) {
  $matchedCombinations = [[]];
}

echo json_encode($matchedCombinations, JSON_NUMERIC_CHECK);