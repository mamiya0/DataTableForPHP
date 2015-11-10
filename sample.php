<?php
require_once __DIR__ . '/DataTableForPHP.php';

// 厳密モードにする
DataTableForPHP::setStrictMode(TRUE);

$dtp = new DataTableForPHP();

// DBから取得したデータ(仮)
$datas = array(
	array(
		'no' => 1,
		'name' => 'taro',
		'age' => NULL,
	),
	array(
		'no' => 2,
		'name' => 'jiro',
		'age' => 20,
	),
	array(
		'no' => 3,
		'name' => 'saburo',
		'age' => 30,
	),
);

// DBデータを設定する
$dtp->setDBData($datas);
// 表示して中身を見る
$dtp->watchTable();


/**
 * var_dump() を整形して表示する
 */
function pre_var_dump()
{
	$args = func_get_args();
	if (func_num_args() == 1) {
		$args = reset($args);
	}
	echo '<pre style="font-size:12px;">';
	var_dump($args);
	echo '</pre>';
}