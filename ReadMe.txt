DataTableForPHP は、
PHP で DBから取得したデータを .NET Framework の DataTable っぽく扱うためのライブラリです。

ﾟ(∀) ﾟ ｴｯ? PHP で .NET Framework 使える？
PHP: DOTNET
http://php.net/manual/ja/class.dotnet.php
(;´A｀)ﾑｼﾑｼｽﾙｰ

doxygen で作成したドキュメントが
./html/index.html にあります。
Webブラウザで御覧ください。

■メソッド対応
DataTableForPHP		.NET Framework DataTable

テーブル関連
getTableName()		DataTable.TableName
setTableName()		DataTable.TableName
setDBData()			-
cloneDataTable()	DataTable.Clone()
dispose()			DataTable.Dispose()
getStrictMode()		-
setStrictMode()		-

カラム系
getAllColumn()		DataTable.Columns
getColumn()			DataTable.Columns()
containsColumn()	DataTable.Columns.Contains()
countColumn()		DataTable.Columns.Count()
addColumn()			DataTable.Columns.Add(), DataTable.Columns.AddRange()
changeColumn()		DataTable.Columns[index].ColumnName
removeColumn()		DataTable.Columns[index].Remove()
removeAtColumn()	DataTable.Columns.RemoveAt()
clearColumn()		DataTable.Columns.Clear()

レコード系
getAllRow()			DataTable.Rows
getRow()			DataTable.Rows[index]
countRow()			DataTable.Rows.Count()
newRow()			DataTable.NewRow()
addRow()			DataTable.Rows.Add(row)
insertAtRow()		DataTable.Rows.InsertAt(row, index)
setRow()			DataTable.Rows[index]
removeRow()			DataTable.Rows[index].Remove()
clearRow()			DataTable.Rows.Clear()

データ系
getData()			DataTable.Rows[rowIndex][columnName]
getDataForIndex()	DataTable.Rows[rowIndex][columnIndex]
setData()			DataTable.Rows[rowIndex][columnName]
setDataForIndex()	DataTable.Rows[rowIndex][columnIndex]

データ操作系
projection()		-
filter()			DataTable.select(whereFunc)
sort()				DataTable.select("", sorts)
select()			DataTable.select(whereFunc, sorts)

Util系
watchTable()		- (DataSet ビジュアライザー)
compareFormat()		-
