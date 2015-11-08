<?php
/**
 * DataTableForPHP
 *
 * @author mamiya_shou
 */
class DataTableForPHP {

	/**
	 * テーブル名
	 *
	 * @var string
	 */
	private $_tableName = NULL;

	/**
	 * カラム配列
	 * @var array
	 */
	private $_columns = array();

	/**
	 * レコード配列
	 *
	 * @var array
	 */
	private $_rows = array();

	/**
	 * 厳格モードか否か
	 *
	 * @var boolean
	 */
	public static $isStrictMode = TRUE;

	/**
	 * エラーメッセージ配列
	 *
	 * @var array
	 */
	public static $errorMsgs = array(
		'column_not_exists'					=> 'カラム「%s」は存在しません。取得することができません。',
		'column_exists_add'					=> 'カラム「%s」は既に存在します。追加することができません。',
		'column_not_exists_delete'			=> 'カラム「%s」は存在しません。削除することができません。',
		'column_exists_change'				=> 'カラム「%s」は既に存在します。変更することができません。',
		'column_not_exists_change'			=> 'カラム「%s」は存在しません。変更することができません。',
		'column_index_not_exists'			=> '%d番目のカラムは存在しません。取得することができません。',
		'column_index_not_exists_delete'	=> '%d番目のカラムは存在しません。削除することができません。',
		'column_configuration_invalid'		=> 'レコードのカラム構成に問題があります。',
		'row_not_insert'					=> 'レコードの挿入位置(%d)が無効です。',
		'row_not_exists'					=> '%d番目のレコードは存在しません。取得することができません。',
		'row_not_exists_update'				=> '%d番目のレコードは存在しません。更新することができません。',
		'row_not_exists_delete'				=> '%d番目のレコードは存在しません。削除することができません。',
	);

	/**
	 * コンストラクタ
	 *
	 * @param string $tableName テーブル名
	 */
	public function __construct($tableName = NULL)
	{
		if ($tableName !== NULL) {
			$this->setTableName($tableName);
		}
	}

	// データテーブル全般

	/**
	 * テーブル名を取得する
	 *
	 * @return string テーブル名
	 */
	public function getTableName()
	{
		return $this->$_tableName;
	}

	/**
	 * テーブル名を設定する
	 *
	 * @param string $tableName テーブル名
	 * @return void
	 */
	public function setTableName($tableName)
	{
		$this->_tableName = $tableName;
	}

	/**
	 * DBデータを設定する
	 *
	 * @param array $datas
	 * @return DataTableForPHP メソッドチェーンで使う用
	 * @throws Exception
	 */
	public function setDBData(array $datas)
	{
		// レコードあり
		if (count($datas) > 0) {
			$firstRow = reset($datas);
			$this->_columns = array_keys($firstRow);

			// 厳格モードの場合
			if (self::$isStrictMode === TRUE) {
				foreach ($datas as $row) {
					if ($this->judgeColumnConfiguration($row, 'set') === TRUE) {
						// レコードの追加
						$this->addRow($row);
					}
				}
			}
			// 非・厳格モードの場合
			else {
				$this->_rows = $datas;
			}
		}
		// レコード無し
		else {
			$this->_columns = array();
			$this->_rows = array();
		}

		return $this;
	}

	/**
	 * データテーブルを複製する
	 * ※カラム構成のみでレコードは複製しない
	 * 　完全な複製が欲しい場合は、PHP の clone を使うこと
	 *
	 * @return DataTableForPHP
	 */
	public function cloneDataTable()
	{
		$tableName = $this->getTableName();
		$columns = $this->getAllColumn();

		$ret = new DataTableForPHP($tableName);
		$ret->addColumn($columns);
		return $ret;
	}

	/**
	 * リソースの破棄
	 * (破棄後、再使用可能)
	 *
	 * @return void
	 */
	public function dispose()
	{
		unset($this->_tableName);
		unset($this->_columns);
		unset($this->_rows);
	}

	/**
	 * 厳格モードの設定を行う
	 *
	 * @param boolean $mode 厳格モードか否か
	 * @return void
	 */
	public static function setStrictMode($mode)
	{
		self::$isStrictMode = (boolean)$mode;
	}

	/**
	 * 厳格モードか否かを取得する
	 */
	public static function getStrictMode()
	{
		return self::$isStrictMode;
	}

	// カラム関連

	/**
	 * カラムを取得する
	 *
	 * @return array カラム配列
	 */
	public function getAllColumn()
	{
		return $this->_columns;
	}

	/**
	 * 指定位置のカラム名を取得する
	 *
	 * @param int $index 指定位置
	 * @return string カラム名
	 * @throws Exception
	 */
	public function getColumn($index)
	{
		// カラムが存在しない場合
		if (array_key_exists($index, $this->_columns) === FALSE) {
			$message = sprintf(self::$errorMsgs['column_index_not_exists'], $index);
			throw new Exception($message);
		}

		return $this->_columns[$index];
	}

	/**
	 * 指定したカラム名が存在するか否か
	 *
	 * @param string $column カラム名
	 */
	public function containColumn($column)
	{
		return in_array($column, $this->_columns);
	}

	/**
	 * カラム数を返す
	 *
	 * @return int カラム数
	 */
	public function countColumn()
	{
		return count($this->_columns);
	}

	/**
	 * カラムを追加する
	 *
	 * @param string|array $column カラム名
	 * @return void
	 * @throws Exception
	 */
	public function addColumn($column)
	{
		// 配列でない場合
		if (is_array($column) === FALSE) {
			// カラム追加(内部メソッドコール)
			$ret = $this->_addColumn($column);
		}
		// 配列の場合
		else {
			$ret = array();
			foreach ($column as $col) {
				// カラム追加(内部メソッドコール)
				$ret[] = $this->_addColumn($col);
			}
		}
	}

	/**
	 * カラム名を追加する(内部メソッド)
	 *
	 * @param string $column カラム名
	 * @return void
	 * @throws Exception
	 */
	private function _addColumn($column)
	{
		// カラム配列に存在しない場合
		if (in_array($column, $this->_columns) === FALSE) {
			$this->_columns[] = $column;
		}
		// 存在する場合
		else {
			$message = sprintf(self::$errorMsgs['column_exists_add'], $column);
			throw new Exception($message);
		}

		// レコードにカラムを追加
		foreach ($this->_rows as &$row) {
			$row[$column] = NULL;
		}
	}

	/**
	 * カラム名を変更する
	 *
	 * @param string $oldColumnName 変更前のカラム名
	 * @param string $newColumnName 変更後のカラム名
	 * @return void
	 * @throws Exception
	 */
	public function changeColumn($oldColumnName, $newColumnName)
	{
		// 変更前と変更後の値が同じ場合、何もしないで処理を終える
		if ($oldColumnName === $newColumnName) {
			return;
		}

		// 古いカラム名が存在しない場合
		if ($this->containColumn($oldColumnName) === FALSE) {
			$message = sprintf(self::$errorMsgs['column_not_exists_change'], $oldColumnName);
			throw new Exception($message);
		}
		// 新しいカラム名が存在する場合
		if ($this->containColumn($newColumnName) === TRUE) {
			$message = sprintf(self::$errorMsgs['column_exists_change'], $newColumnName);
			throw new Exception($message);
		}

		$rows = array();
		// 配列の要素を置き換える
		$this->_columns = $this->replaceArray($this->_columns, $oldColumnName, $newColumnName);
		foreach ($this->_rows as $row) {
			// 置き換えた配列をキーに値配列を値にして新たな配列を作成する
			$rows[] = array_combine($this->_columns, $row);
		}
		$this->_rows = $rows;
	}

	/**
	 * 配列の要素を置き換える
	 *
	 * @param array $datas 配列
	 * @param string $old 置き換える値
	 * @param string $new 置換する値
	 * @return array 置換後の配列
	 */
	private function replaceArray(array $datas, $old, $new)
	{
		if (($key = array_search($old, $datas)) !== FALSE) {
			$datas[$key] = $new;
		}

		return $datas;
	}

	/**
	 * カラムを削除する
	 *
	 * @param string $column 削除するカラム名
	 * @return void
	 * @throws Exception
	 */
	public function removeColumn($column)
	{
		// カラム配列に存在する場合
		if (($key = array_search($column, $this->_columns)) !== FALSE) {
			// カラム削除
			unset($this->_columns[$key]);
			// 添字を振り直す
			$this->_columns = array_values($this->_columns);
		}
		// 存在しない場合
		else {
			$message = sprintf(self::$errorMsgs['column_not_exists_delete'], $column);
			throw new Exception($message);
		}

		// レコードからカラムを削除
		foreach ($this->_rows as &$row) {
			unset($row[$column]);
		}
	}

	/**
	 * 指定位置のカラムを削除する
	 *
	 * @param int $index 指定位置
	 * @return void
	 * @throws Exception
	 */
	public function removeAtColumn($index)
	{
		// 指定位置のカラムが存在する場合
		if (array_key_exists($index, $this->_columns) === TRUE) {
			$column = $this->_columns[$index];
			// カラム削除
			$this->removeColumn($column);
		}
		// 存在しない場合
		else {
			$message = sprintf(self::$errorMsgs['column_index_not_exists_delete'], $index);
			throw new Exception($message);
		}
	}

	/**
	 * カラムを消去する
	 *
	 * @return void
	 */
	public function clearColumn()
	{
		$columns = $this->_columns;
		foreach ($columns as $column) {
			// カラムの削除
			$rets[] = $this->removeColumn($column);
		}
		$this->_columns = array();
	}

	/**
	 * カラム構成を判定する
	 *
	 * @param array $row レコード
	 * @param string $msgKey メッセージキー
	 * @throws Exception
	 * @return boolean TRUE：問題なし / FALSE：問題あり
	 */
	private function judgeColumnConfiguration(array $row, $msgKey = 'add')
	{
		$ret = TRUE;

		// 厳格モードの場合
		if (self::$isStrictMode === TRUE) {
			$columns = array_keys($row);
			$retA = array_diff($this->_columns, $columns);
			$retB = array_diff($columns, $this->_columns);
			// カラム構成が正しい場合
			if (count($this->_columns) == count($columns) &&
				count($retA) == 0 && count($retB) == 0) {
				$ret = TRUE;
			}
			// 誤りがある場合
			else {
				$fronts = array(
					'add'		=> '追加する',
					'insert'	=> '挿入する',
					'update'	=> '更新する',
					'set'		=> '取り込む',
				);
				$message = $fronts[$msgKey] . self::$errorMsgs['column_configuration_invalid'];
				throw new Exception($message);
			}
		}
		// 非・厳格モードの場合
		else {
			// 常にTRUE
			$ret = TRUE;
		}

		return $ret;
	}

	// レコード関連

	/**
	 * レコード配列を取得する
	 *
	 * @return array レコード配列
	 */
	public function getAllRow()
	{
		return $this->_rows;
	}

	/**
	 * 指定位置のレコードを取得する
	 *
	 * @param int $index 指定位置
	 * @return array レコード
	 * @throws Exception
	 */
	public function getRow($index)
	{
		// レコードが存在しない場合
		if (array_key_exists($index, $this->_rows) === FALSE) {
			$message = sprintf(self::$errorMsgs['row_not_exists'], $index);
			throw new Exception($message);
		}
		return $this->_rows[$index];
	}

	/**
	 * レコード数を返す
	 *
	 * @return int レコード数
	 */
	public function countRow()
	{
		return count($this->_rows);
	}

	/**
	 * 値がNULLの追加用レコードを返す
	 *
	 * @return array 追加用レコード
	 */
	public function newRow()
	{
		$rets = array();
		foreach ($this->_columns as $column) {
			$rets[$column] = NULL;
		}
		return $rets;
	}

	/**
	 * レコードを追加する
	 *
	 * @param array $row 追加するレコード
	 * @return void
	 * @throws Exception
	 */
	public function addRow(array $row)
	{
		// カラム構成の判定
		$this->judgeColumnConfiguration($row, 'add');

		$this->_rows[] = $row;
	}

	/**
	 * 指定位置にレコードを挿入する
	 *
	 * @param array $row 挿入するレコード
	 * @param int $index 挿入位置(>=0)
	 * @return void
	 * @throws Exception
	 */
	public function insertAtRow(array $row, $index)
	{
		// 挿入位置が0未満の場合
		if ($index < 0) {
			$message = sprintf(self::$errorMsgs['row_not_insert'], $index);
			throw new Exception($message);
		}
		// カラム構成の判定
		$this->judgeColumnConfiguration($row, 'insert');

		array_splice($this->_rows, $index, 0, array($row));
	}

	/**
	 * 指定位置のレコードを更新する
	 *
	 * @param array $row 更新するレコード
	 * @param int $index 指定位置(>=0)
	 * @return void
	 */
	public function setRow(array $row, $index)
	{
		// レコードが存在しない場合
		if (array_key_exists($index, $this->_rows) === FALSE) {
			$message = sprintf(self::$errorMsgs['row_not_exists_update'], $index);
			throw new Exception($message);
		}

		// カラム構成の判定
		$this->judgeColumnConfiguration($row, 'update');

		$this->_rows[$index] = $row;
	}

	/**
	 * 指定位置のレコードを削除する
	 *
	 * @param int $index 指定位置
	 * @param boolean $isRenumbered 添字の振り直すか否か
	 * @return void
	 * @throws Exception
	 */
	public function removeRow($index, $isRenumbered = TRUE)
	{
		// レコードが存在しない場合
		if (array_key_exists($index, $this->_rows) === FALSE) {
			$message = sprintf(self::$errorMsgs['row_not_exists_delete'], $index);
			throw new Exception($message);
		}

		unset($this->_rows[$index]);

		// 添字の振り直し
		if ($isRenumbered === TRUE) {
			$this->_rows = array_values($this->_rows);
		}
	}

	/**
	 * レコードを消去する
	 *
	 * @return void
	 */
	public function clearRow()
	{
		$this->_rows = array();
	}

	// データ関連

	/**
	 * 値を取得する
	 *
	 * @param int $rowIndex 指定位置
	 * @param string $columnName カラム名
	 * @return variant 値
	 * @throws Exception
	 */
	public function getData($rowIndex, $columnName)
	{
		// レコードが存在しない場合
		if (array_key_exists($rowIndex, $this->_rows) === FALSE) {
			$message = sprintf(self::$errorMsgs['row_not_exists'], $rowIndex);
			throw new Exception($message);
		}
		// カラムが存在しない場合
		if ($this->containColumn($columnName) === FALSE) {
			$message = sprintf(self::$errorMsgs['column_not_exists'], $columnName);
			throw new Exception($message);
		}

		return $this->_rows[$rowIndex][$columnName];
	}

	/**
	 * (カラムインデックスを用いて)値を取得する
	 *
	 * @param int $rowIndex 指定位置
	 * @param int $columnIndex カラムの指定位置
	 * @return variant 値
	 * @throws Exception
	 */
	public function getDataForIndex($rowIndex, $columnIndex)
	{
		// カラム名を取得する
		$columnName = $this->getColumn($columnIndex);
		// 値を取得する
		return $this->getData($rowIndex, $columnName);
	}

	/**
	 * 値を更新する
	 *
	 * @param int $rowIndex 指定位置
	 * @param string $columnName カラム名
	 * @param variant 値
	 * @return void
	 * @throws Exception
	 */
	public function setData($rowIndex, $columnName, $value)
	{
		// レコードが存在しない場合
		if (array_key_exists($rowIndex, $this->_rows) === FALSE) {
			$message = sprintf(self::$errorMsgs['row_not_exists_update'], $rowIndex);
			throw new Exception($message);
		}
		// カラムが存在しない場合
		if ($this->containColumn($columnName) === FALSE) {
			$message = sprintf(self::$errorMsgs['column_not_exists_change'], $columnName);
			throw new Exception($message);
		}

		$this->_rows[$rowIndex][$columnName] = $value;
	}

	/**
	 * (カラムインデックスを用いて)値を更新する
	 *
	 * @param int $rowIndex 指定位置
	 * @param int $columnIndex カラムの指定位置
	 * @param variant 値
	 * @return void
	 * @throws Exception
	 */
	public function setDataForIndex($rowIndex, $columnIndex, $value)
	{
		// カラム名を取得する
		$columnName = $this->getColumn($columnIndex);
		// 値を更新する
		$this->setData($rowIndex, $columnName, $value);
	}
}

// DataTableForPHP では長いので DTP で使えるように別名定義
class_alias('DataTableForPHP', 'DTP');
// DataTableも使えるように別名定義
class_alias('DataTableForPHP', 'DataTable');

$dtp = new DataTableForPHP();

$datas = array(
	array(
		'a' => 1,
		'b' => 2,
		'c' => 3,
	),
	array(
		'a' => 10,
		'b' => 20,
		'c' => 30,
	),
	array(
		'a' => 100,
		'b' => 200,
		'c' => 300,
	),
);
pre_var_dump(DataTableForPHP::getStrictMode());
$dtp->setDBData($datas);
$dtp->changeColumn('z', 'd');
pre_var_dump($dtp);

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