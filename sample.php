<?php
require_once __DIR__ . '/DataTableForPHP.php';

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

try {
	// 厳密モードに設定する
	// デフォルト：true
	// 厳密モードに設定するとカラム構成と
	// 異なるレコードを設定した場合に例外を発生させる
	DataTableForPHP::setStrictMode(TRUE);

	// 厳密モードかどうか
	// echo DataTableForPHP::getStrictMode();	// true

	// DataTableForPHP オブジェクトの生成
	$dtp = new DataTableForPHP('データテーブル名');

	// DataTableForPHP は長いので、クラスの別名を「DTP」「DataTable」で定義ある
	// $dtp = new DTP('データテーブル名');
	// $dtp = new DataTable('データテーブル名');
	// でも同じ意味になる

	// データテーブル名の取得
	// echo $dtp->getTableName();

	// データテーブル名の設定
	// $dtp->setTableName('データテーブル名2')

	// DBデータを設定する
	$dtp->setDBData($datas);

	// データテーブル名、カラム構成のコピー
	// レコードはコピーされない
	$copyOfDtp = $dtp->cloneDataTable();

	// 完全なコピーが欲しい場合は、
	// PHP の clone を使う
	// $copyOfDtp = clone $dtp;

	// データテーブルの破棄
	// 破棄後、使用不可
	$copyOfDtp->dispose();

	// 表示して中身を見る
	$dtp->watchTable();

	// 比較用の文字列を取得
	// $strConv = $dtp->compareFormat();

	// カラム追加
	$dtp->addColumn('a');
	// 複数同時に追加
	$dtp->addColumn(array('b', 'c', 'd'));

	// カラム名の取得
	$columns = $dtp->getAllColumn();

	// 2番目のカラムを取得(0始まり)
	$column = $dtp->getColumn(2);

	// カラムが存在するか
	$ret = $dtp->containsColumn('age');

	// カラム数の取得
	$count = $dtp->countColumn();

	// カラム名の変更
	$dtp->changeColumn('a', 'A');

	// カラムの削除
	$dtp->removeColumn('d');
	// 指定位置のカラムの削除
	$dtp->removeAtColumn(5);

	// カラムの全削除
	// $dtp->clearColumn();

	// 全レコードの取得
	$rows = $dtp->getAllRow();

	// 2番目のレコードの取得(0始まり)
	$row = $dtp->getRow(2);

	// 追加用レコードの作成
	$row = $dtp->newRow();

	// レコードの追加
	$dtp->addRow($row);

	// 0番目にレコードを挿入
	$dtp->insertAtRow($row, 0);

	// 0番目のレコードを更新
	$row['no'] = 0;
	$row['name'] = 'zero';
	$row['age'] = 0;
	$dtp->setRow($row, 0);

	// 4番目のレコードの削除
	$dtp->removeRow(4);

	// 全レコード削除
	// $dtp->clearRow();

	// 1番目のレコードのカラム「no」の値を取得
	$value = $dtp->getData(1, 'no');
	// 1番目のレコードの0番目のカラム(no)の値を取得
	$value =$dtp->getDataForIdx(1, 0);

	// 2番目のレコードのカラム「age」に値を設定
	$dtp->setData(2, 'age', 22);
	// 2番目のレコードの2番めのカラム(age)に値を設定
	$dtp->setDataForIdx(2, 2, 2);

	// 特定のカラムデータの取得
	// 第2引数にTRUEを指定すると配列として取得
	// 無指定の場合はDataTableForPHPオブジェクトで取得
	// メソッドチェーン可
	$datas = $dtp->projection('no', TRUE);
	// 複数指定可
	$datas = $dtp->projection(array('no', 'age'), TRUE);

	// array_filter() を使ってフィルタリングを行う
	// 第1引数に判定関数を指定する
	// 第2引数にTRUEを指定すると配列として取得
	// 無指定の場合はDataTableForPHPオブジェクトで取得
	// メソッドチェーン可
	$match = 1;
	$datas = $dtp->filter(function($row) use($match) {
		return ($row['no'] == $match);
	}, TRUE);

	// array_multisort() を使ってソートを行う
	// ソート情報配列
	// キーにカラム名を指定する
	$sorts = array(
		// 第1キー
		// 値が未指定な場合、order = SORT_ASC, flags = SORT_REGULAR として扱われる
		'no'	=> NULL,
		// 第2キー
		// 値が配列でない場合、order を指定したとみなされる。その際の flags は SORT_REGULAR となる
 		'age'	=> SORT_DESC,
		// 第3キー
		// order, flags ともに指定した場合
		'name'	=> array(
			'order' => SORT_ASC,
			'flags' => SORT_STRING,
		),
		// 値が配列であっても order, flags が未指定の場合は、
		// order = SORT_ASC, flags = SORT_REGULAR として扱われる
	);
	$datas = $dtp->sort($sorts);

} catch (Exception $e) {
	echo '<pre style="font-size:12px;">';
	echo $e;
	echo "\r\n";
	echo '</pre>';
}