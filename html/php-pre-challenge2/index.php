<?php
$array = explode(',', $_GET['array']);

// 修正はここから
$length = sizeof($array);
for ($i = 0; $i < $length; $i++) {
  for ($j = 0; $j < $length - 1 - $i; $j++) {
    if ($array[$j] > $array[$j + 1]) {
      $temp = $array[$j];
      $array[$j] = $array[$j + 1];
      $array[$j + 1] = $temp;
    }
  }
}
// 修正はここまで
echo "<pre>";
print_r($array);
echo "</pre>";
