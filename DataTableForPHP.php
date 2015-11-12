<?php
/**
 * DataTableForPHP
 *
 * @author mamiya_shou
 * @version 1.0.0
 * @caution PHP 5.3 以上
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
	private static $isStrictMode = TRUE;

	/**
	 * エラーメッセージ配列
	 *
	 * @var array
	 */
	private static $errorMsgs = array(
		'column_not_exists'					=> 'カラム「%s」は存在しません。取得することができません。',
		'column_exists_add'					=> 'カラム「%s」は既に存在します。追加することができません。',
		'column_not_exists_delete'			=> 'カラム「%s」は存在しません。削除することができません。',
		'column_exists_change'				=> 'カラム「%s」は既に存在します。変更することができません。',
		'column_not_exists_change'			=> 'カラム「%s」は存在しません。変更することができません。',
		'column_index_not_exists'			=> '%s番目のカラムは存在しません。取得することができません。',
		'column_index_not_exists_delete'	=> '%s番目のカラムは存在しません。削除することができません。',
		'column_configuration_invalid'		=> 'レコードのカラム構成に問題があります。',
		'row_not_insert'					=> 'レコードの挿入位置(%s)が無効です。',
		'row_not_exists'					=> '%s番目のレコードは存在しません。取得することができません。',
		'row_not_exists_update'				=> '%s番目のレコードは存在しません。更新することができません。',
		'row_not_exists_delete'				=> '%s番目のレコードは存在しません。削除することができません。',
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
	 * @see [.NET] DataTable.TableName
	 */
	public function getTableName()
	{
		return $this->_tableName;
	}

	/**
	 * テーブル名を設定する
	 *
	 * @param string $tableName テーブル名
	 * @return void
	 * @see [.NET] DataTable.TableName
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
	 * @see original
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
	 * データテーブルを複製する<br />
	 * ※カラム構成のみでレコードは複製しない<br />
	 * 　完全な複製が欲しい場合は、PHP の clone を使うこと
	 *
	 * @return DataTableForPHPオブジェクト
	 * @see [.NET] DataTable.Clone()
	 */
	public function cloneDataTable()
	{
		$ret = new DataTableForPHP($this->_tableName);
		$ret->addColumn($this->_columns);
		return $ret;
	}

	/**
	 * リソースの破棄<br />
	 * (破棄後、使用不可)
	 *
	 * @return void
	 * @see [.NET] DataTable.Dispose()
	 */
	public function dispose()
	{
		$this->_tableName	= NULL;
		$this->_columns		= array();
		$this->_rows		= array();
	}

	/**
	 * 厳格モードか否かを取得する
	 *
	 * @return boolean 厳格モードか否か
	 * @see original
	 */
	public static function getStrictMode()
	{
		return self::$isStrictMode;
	}

	/**
	 * 厳格モードの設定を行う
	 *
	 * @param boolean $mode 厳格モードか否か
	 * @return void
	 * @see original
	 */
	public static function setStrictMode($mode)
	{
		self::$isStrictMode = (boolean)$mode;
	}

	// カラム系

	/**
	 * カラムを取得する
	 *
	 * @return array カラム配列
	 * @see [.NET] DataTable.Columns
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
	 * @see [.NET] DataTable.Columns[index]
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
	 * @see [.NET] DataTable.Columns.Contains(column)
	 */
	public function containsColumn($column)
	{
		return in_array($column, $this->_columns, TRUE);
	}

	/**
	 * カラム数を返す
	 *
	 * @return int カラム数
	 * @see [.NET] DataTable.Columns.Count()
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
	 * @see [.NET]	DataTable.Columns.Add(column)<br />
	 * 				DataTable.Columns.AddRange(column)
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
		// カラム配列に存在する場合
		if (in_array($column, $this->_columns, TRUE) === TRUE) {
			$message = sprintf(self::$errorMsgs['column_exists_add'], $column);
			throw new Exception($message);
		}
		// 存在しない場合
		else {
			$this->_columns[] = $column;
		}

		// レコードにカラムを追加
		foreach ($this->_rows as $key => $row) {
			$this->_rows[$key][$column] = NULL;
		}
	}

	/**
	 * カラム名を変更する
	 *
	 * @param string $oldColumnName 変更前のカラム名
	 * @param string $newColumnName 変更後のカラム名
	 * @return void
	 * @throws Exception
	 * @see [.NET] DataTable.Columns[index].ColumnName
	 */
	public function changeColumn($oldColumnName, $newColumnName)
	{
		// 変更前と変更後の値が同じ場合、何もしないで処理を終える
		if ($oldColumnName === $newColumnName) {
			return;
		}

		// 古いカラム名が存在しない場合
		if ($this->containsColumn($oldColumnName) === FALSE) {
			$message = sprintf(self::$errorMsgs['column_not_exists_change'], $oldColumnName);
			throw new Exception($message);
		}
		// 新しいカラム名が存在する場合
		if ($this->containsColumn($newColumnName) === TRUE) {
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
	 * @see [.NET] DataTable.Columns[index].Remove()
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
	 * @see [.NET] DataTable.Columns.RemoveAt(index)
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
	 * @see [.NET] DataTable.Columns.Clear()
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
			// カラム構成が正しい場合
			if ($this->_columns == $columns) {
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

	// レコード系

	/**
	 * レコード配列を取得する
	 *
	 * @return array レコード配列
	 * @see [.NET] DataTable.Rows
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
	 * @see [.NET] DataTable.Rows[index]
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
	 * @see [.NET] DataTable.Rows.Count()
	 */
	public function countRow()
	{
		return count($this->_rows);
	}

	/**
	 * 値がNULLの追加用レコードを返す
	 *
	 * @return array 追加用レコード
	 * @see [.NET] DataTable.NewRow()
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
	 * @see [.NET] DataTable.Rows.Add(row)
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
	 * @see [.NET] DataTable.Rows.InsertAt(row, index)
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
	 * @see [.NET] DataTable.Rows[index]
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
	 * @see [.NET] DataTable.Rows[index].Remove()
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
	 * @see [.NET] DataTable.Rows.Clear()
	 */
	public function clearRow()
	{
		$this->_rows = array();
	}

	// データ系

	/**
	 * 値を取得する
	 *
	 * @param int $rowIndex 指定位置
	 * @param string $columnName カラム名
	 * @return variant 値
	 * @throws Exception
	 * @see [.NET] DataTable.Rows[rowIndex][columnName]
	 */
	public function getData($rowIndex, $columnName)
	{
		// レコードが存在しない場合
		if (array_key_exists($rowIndex, $this->_rows) === FALSE) {
			$message = sprintf(self::$errorMsgs['row_not_exists'], $rowIndex);
			throw new Exception($message);
		}
		// カラムが存在しない場合
		if ($this->containsColumn($columnName) === FALSE) {
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
	 * @see [.NET] DataTable.Rows[rowIndex][columnIndex]
	 */
	public function getDataForIdx($rowIndex, $columnIndex)
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
	 * @param variant $value 値
	 * @return void
	 * @throws Exception
	 * @see [.NET] DataTable.Rows[rowIndex][columnIndex]
	 */
	public function setData($rowIndex, $columnName, $value)
	{
		// レコードが存在しない場合
		if (array_key_exists($rowIndex, $this->_rows) === FALSE) {
			$message = sprintf(self::$errorMsgs['row_not_exists_update'], $rowIndex);
			throw new Exception($message);
		}
		// カラムが存在しない場合
		if ($this->containsColumn($columnName) === FALSE) {
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
	 * @param variant $value 値
	 * @return void
	 * @throws Exception
	 * @see [.NET] DataTable.Rows[rowIndex][columnIndex]
	 */
	public function setDataForIdx($rowIndex, $columnIndex, $value)
	{
		// カラム名を取得する
		$columnName = $this->getColumn($columnIndex);
		// 値を更新する
		$this->setData($rowIndex, $columnName, $value);
	}

	// データ操作系

	/**
	 * 射影 - 特定のカラムを取得する
	 *
	 * @param string|array $columns カラム文字列 or カラム配列
	 * @param boolean $isArray 配列で返すフラグ
	 * @return array|DataTableForPHP 射影後の配列 or DataTableForPHPオブジェクト
	 * @throws Exception
	 * @see original
	 */
	public function projection($columns, $isArray = FALSE)
	{
		foreach ($this->_columns as $column) {
			// カラム名が存在しない場合
			if ($this->containsColumn($column) === FALSE) {
				$message = sprintf(self::$errorMsgs['column_not_exists'], $column);
				throw new Exception($message);
			}
		}

		$columns = (array)$columns;
		$rows = array();
		// カラムが1つ、かつ配列で返す場合
		if (count($columns) == 1 && $isArray === TRUE) {
			$firstColumn = reset($columns);
			foreach ($this->_rows as $row) {
				$rows[] = $row[$column];
			}
		}
		// それ以外の場合
		else {
			foreach ($columns as $column) {
				foreach ($this->_rows as $index => $row) {
					$rows[$index][$column] = $row[$column];
				}
			}
		}

		// 配列で返さない場合(通常)
		if ($isArray === FALSE) {
			$ret = new DataTableForPHP($this->_tableName);
			$ret->setDBData($rows);
		}
		else {
			$ret = $rows;
		}

		return $ret;
	}

	/**
	 * フィルタリング
	 *
	 * @param function $whereFunc 抽出関数
	 * @param boolean $isArray 配列で返すフラグ
	 * @return array|DataTableForPHP フィルタリング後の配列 or DataTableForPHPオブジェクト
	 * @throws Exception
	 * @see [.NET] DataTable.select(whereFunc)
	 */
	public function filter($whereFunc, $isArray = FALSE)
	{
		$rows = array_filter($this->_rows, $whereFunc);
		$rows = array_values($rows);
		// 配列で返さない場合(通常)
		if ($isArray === FALSE) {
			$ret = $this->cloneDataTable();
			if (count($rows) > 0) {
				$ret->setDBData($rows);
			}
		}
		else {
			$ret = $rows;
		}

		return $ret;
	}

	/**
	 * ソート
	 *
	 * @param array $sorts ソート順、ソート方法配列
	 * @param string $isArray 配列で返すかフラグ
	 * @return array|DataTableForPHP ソート後の配列 or DataTableForPHPオブジェクト
	 * @throws Exception
	 * @see [.NET] DataTable.select("", sorts)
	 */
	public function sort(array $sorts, $isArray = FALSE)
	{
		$orders = array();
		$flags = array();
		$datas = array();
		foreach ($sorts as $key => $sort) {
			// カラム名が存在しない場合
			if ($this->containsColumn($key) === FALSE) {
				$message = sprintf(self::$errorMsgs['column_not_exists'], $key);
				throw new Exception($message);
			}
			// カラム単位でデータを取得
			$datas[] = $this->projection($key, TRUE);

			// NULLの場合
			if ($sort === NULL) {
				$orders[] = SORT_ASC;
				$flags[] = SORT_REGULAR;
			}
			// 昇順 or 降順 指定の場合(並び順のみ指定)
			else if ($sort === SORT_ASC || $sort === SORT_DESC) {
				$orders[] = $sort;
				$flags[] = SORT_REGULAR;
			}
			// 完全指定の場合
			else if (is_array($sort) === TRUE) {
				if (array_key_exists('order', $sort) === TRUE) {
					$orders[] = $sort['order'];
				}
				else {
					$orders[] = SORT_ASC;
				}
				if (array_key_exists('flags', $sort) === TRUE) {
					$flags[] = $sort['flags'];
				}
				else {
					$flags[] = SORT_REGULAR;
				}
			}
		}

		$rows = $this->_rows;
		if (count($datas) > 0) {
			$evals = array();
			foreach ($datas as $index => $data) {
				$evals[] = sprintf('$datas[%d], $orders[%d], $flags[%d]', $index, $index, $index);
			}
			$eval  = 'array_multisort(' . implode(', ', $evals) . ', $rows);';
			// echo $eval;
			eval($eval);
		}

		// 配列で返さない場合(通常)
		if ($isArray === FALSE) {
			$ret = $this->cloneDataTable();
			if (count($rows) > 0) {
				$ret->setDBData($rows);
			}
		}
		else {
			$ret = $rows;
		}

		return $ret;
	}

	/**
	 * フィルタリング および ソートを行う
	 *
	 * @param function $whereFunc 抽出関数
	 * @param array $sorts ソート順、ソート方法配列
	 * @param boolean $isArray 配列で返すかフラグ
	 * @return array|DataTableForPHP ソート後の配列 or DataTableForPHPオブジェクト
	 * @throws Exception
	 * @see [.NET] DataTable.select(whereFunc, sorts)
	 */
	public function select($whereFunc, $sorts = NULL, $isArray = FALSE)
	{
		// フィルタリング
		$rows = array_filter($this->_rows, $whereFunc);

		// ソート指定あり
		if (is_array($sorts) === TRUE) {
			$ret = $this->cloneDataTable();
			if (count($rows) > 0) {
				$ret->setDBData($rows);
			}
			// ソート
			$ret = $ret->sort($sorts);
		}

		// 配列で返す場合
		if ($isArray === FALSE) {
			$ret = $ret->_rows;
		}
		return $ret;
	}

	// Util系

	/**
	 * データテーブルの中身を見る
	 *
	 * @param boolean $sortColumn カラムのソートを行うか否か
	 * @param boolean $return 変数として返すか出力するか
	 * @return NULL|string $returnに依存する (TRUE：string, FALSE：NULL)
	 * @see original
	 */
	public function watchTable($sortColumn = FALSE, $return = FALSE)
	{
		$rows = $this->_rows;
		$columns = $this->_columns;
		$columnCount = count($columns);

		// カラムのソートを行う場合
		if ($sortColumn === TRUE) {
			foreach ($rows as $key => $row) {
				ksort($rows[$key]);
			}
			sort($columns);
		}

		$html = <<< _HTML_
<style type="text/css">
.datatableforphp {
	font-size: 13px !important;
}
.datatableforphp dd,
.datatableforphp dt {
	line-height: 110% !important;
}
.datatableforphp table {
	font-size: 13px !important;
	border-top: 1px solid #000000 !important;
	border-left: 1px solid #000000 !important;
	border-collapse: collapse !important;
}
.datatableforphp caption {
	font-weight: bold !important;
}
.datatableforphp th {
	background: #87E7AD !important;
	border-right: 1px solid #000000 !important;
	border-bottom: 1px solid #000000 !important;
	padding: 2px !important !important;
}
.datatableforphp td {
	border-right: 1px solid #000000 !important;
	border-bottom: 1px solid #000000 !important;
	padding: 2px !important;
}
.datatableforphp .null_value {
	color: #0582FF !important;
}
.datatableforphp .number_value {
	text-align: right !important;
}
.datatableforphp .warning_row {
	background: #FFD1D1 !important;
}
.datatableforphp .warning_message {
	color: #DD2332 !important;
	font-size: 13px !important;
}
</style>
_HTML_;

		$tableName = $this->_tableName;
		if ($tableName === NULL) {
			$tableName = 'NoName';
		}

		$th = '';
		foreach ($columns as $column) {
			$th .= "<th>{$column}</th>" . PHP_EOL;
		}

		$debugs = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
		$debugs = $debugs[0];

		$html .= '<div class="datatableforphp">' . PHP_EOL;
		$html .= '<dl>' . PHP_EOL;
		$html .= '<dt><b>DataTableForPHP->watchTable() がコールされました！</b></dt>' . PHP_EOL;
		$html .= "<dt>file</dt><dd>{$debugs['file']}</dd>" . PHP_EOL;
		$html .= "<dt>line</dt><dd>{$debugs['line']}</dd>" . PHP_EOL;
		$html .= "<dt>row count</dt><dd>{$this->countRow()}</dd>" . PHP_EOL;
		$html .= "<dt>column count</dt><dd>{$this->countColumn()}</dd>" . PHP_EOL;
		$html .= '</dl>' . PHP_EOL;
		$html .= '<table>' . PHP_EOL;
		$html .= "<caption>データテーブル名：{$tableName}</caption>" . PHP_EOL;
		$html .= "<thead><tr>{$th}</tr><thead>" . PHP_EOL;
		$html .= '<tbody>' . PHP_EOL;

		$isWarning = FALSE;
		foreach ($rows as $row) {
			$classNameTr = '';
			$keys = array_keys($row);
			// カラム構成チェック：正常
			if ($columns == $keys) {
				// 何もしない
			}
			// カラム構成チェック：異常
			else {
				$classNameTr = ' warning_row ';
				$isWarning = TRUE;
			}
			$html .= "<tr class='{$classNameTr}'>" . PHP_EOL;
			foreach ($row as $column) {
				$classNameTd = '';
				if ($column === NULL) {
					$value = '(NULL)';
					$classNameTd .= ' null_value ';
				}
				else {
					$value = var_export($column, TRUE);
				}
				if (mb_strpos($value, "'") !== 0) {
					$classNameTd .= ' number_value ';
				}
				$html .= "<td class='{$classNameTd}'>{$value}</td>" . PHP_EOL;
			}
			$html .= '</tr>' . PHP_EOL;
		}

		$html .= '</tbody>' . PHP_EOL;
		$html .= '</table>' . PHP_EOL;

		if ($isWarning === TRUE) {
			$html .= '<p class="warning_message">警告：レコードのカラム構成に問題があります！</p>';
		}
		$html .= '</div>';

		// 変数として返す場合
		if ($return === TRUE) {
			return $html;
		}
		// 変数として返さない場合
		else {
			// 出力
			echo $html;
		}
	}

	/**
	 * 比較用書式を取得する
	 *
	 * @param boolean $sortColumn カラムのソートを行うか否か
	 * @param boolean $return 変数として返すか出力するか
	 * @param string $delimiter 区切り文字
	 * @param string $lineFeed 改行コード
	 * @return NULL|string $returnに依存する (TRUE：string, FALSE：NULL)
	 * @see original
	 */
	public function compareFormat($sortColumn = TRUE, $return = FALSE, $delimiter = ',', $lineFeed = "\r\n")
	{
		$rows = $this->_rows;
		$columns = $this->_columns;

		// カラムのソートを行う場合
		if ($sortColumn === TRUE) {
			foreach ($rows as $key => $row) {
				ksort($rows[$key]);
			}
			sort($columns);
		}

		$output = implode($delimiter, $columns) . $lineFeed;
		foreach ($rows as $row) {
			$output .= implode($delimiter, $row) . $lineFeed;
		}

		// 変数として返す場合
		if ($return === TRUE) {
			return $output;
		}
		// 変数として返さない場合
		else {
			// 出力
			echo $output;
		}
	}
}

// DataTableForPHP では長いので DTP で使えるように別名定義
class_alias('DataTableForPHP', 'DTP');
// DataTableも使えるように別名定義
class_alias('DataTableForPHP', 'DataTable');