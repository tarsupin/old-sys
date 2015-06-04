<?php /*

-------------------------------------------
------ About the Database_Meta Class ------
-------------------------------------------

Provides tools for looking at metadata in the database and dealing with administrative functions.

Some of these options require privileged access to the database.


-------------------------------
------ Methods Available ------
-------------------------------

// Database Permissions
Database_Meta::createDBUser($username, $password, [$host]);		// Create a database user.
Database_Meta::getUserPrivileges();								// Returns user privileges.

// Displays (Output)
Database_Meta::showTable($table)					// Displays the SQL table in HTML format.

// Tables
Database_Meta::getTableList()						// Returns a list of the tables in the database.
Database_Meta::getTableSchema($table, $column = "")	// Returns the schema for a table, or a column if provided.
Database_Meta::tableExists($table)					// Checks if the table listed exists.
Database_Meta::dropTable($table)					// Drops a table.
Database_Meta::renameTable($table, $newName)		// Rename a table.
Database_Meta::copyTable($table, $copyTable)		// Copies a table.

// Columns
Database_Meta::getColumns($table)								// Returns the columns for a table.
Database_Meta::getColumnData($table, $column);					// Return column data.
Database_Meta::columnExists($table, $column)					// Checks if the column listed exists within the table.
Database_Meta::columnsExists($table, $columns)					// Checks if the columns listed exist within the table.
Database_Meta::addColumn($table, $column, $colData, $def = "")	// Adds a new column to the table
Database_Meta::editColumn($table, $column, $colData, $def = "")	// Modifies an existing column.
Database_Meta::dropColumn($table, $column)						// Drops a single column from the table.
Database_Meta::dropColumns($table, [...$column])				// Drops multiple columns from the table.
Database_Meta::renameColumn($table, $columnName, $newName)		// Change the name of a column.

// Indexes
Database_Meta::addIndex($table, $indexData, $type = "INDEX")	// Creates an index.
Database_Meta::dropIndex($table, $index)						// Drops a table index.

// Partitions
Database_Meta::showPartitions($table)							// Returns an array of partition counts.
Database_Meta::setPartitions($table, $type, $column, $number)	// Sets the partitions for the table.
Database_Meta::removePartitions($table)							// Removes all partitions.
Database_Meta::generateDatePartitions($timeSegment, $part);		// Generate date partitions.

// Other
Database_Meta::changeCollation($table, $type = "utf8");			// Change the collation of a table
Database_Meta::setEngine($table, $type = "INNODB")				// Updates the DB engine for a table.

Database::createHashColumn($table, $columnName)					// Creates a hash column

*/

abstract class Database_Meta {
	
	
/****** Retrieve a Table Schema ******/
	public static function createDBUser
	(
		$username			// <str> The name of the database user to create.
	,	$password = ""		// <str> The password that the database user will connect with.
	,	$host = "localhost"	// <str> The host to create the database user on.
	)						// RETURNS <bool> TRUE if user was created, FALSE if not.
	
	// Database_Meta::createDBUser($username, $password, [$host]);
	{
		if(!IsSanitized::variable($username)) { return false; }
		if(!IsSanitized::variable($host, ".:")) { return false; }
		
		// Note: If you're getting an "access violation" error here, you can post this exact query to another system
		// (such as Navicat) and it will work fine. Not sure why it's failing here.
		Database::query('GRANT SELECT, INSERT, UPDATE, DELETE ON *.* TO "' . $username . '"@"' . $host . '" IDENTIFIED BY ? WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;', array($password));
		
		$user = Database::selectValue("SELECT user FROM mysql.user WHERE user=?", array($username));
		
		return $user !== false ? true : false;
	}
	
	
/****** Get the User's Privileges ******/
	public static function getUserPrivileges (
	)			// RETURNS <int:str> the list of privileges for a database user.
	
	// $userPrivileges = Database_Meta::getUserPrivileges();
	{
		$privList = array();
		
		$grants = Database::selectValue("SHOW GRANTS FOR CURRENT_USER()", array());
		
		// Remove unnecessary string content
		$pos = strpos($grants, " ON ");
		$grants = substr($grants, 0, $pos);
		$grants = str_replace("GRANT ", "", $grants);
		
		// Display Grants
		$grants = explode(",", $grants);
		
		foreach($grants as $grant)
		{
			$privList[] = trim($grant);
		}
		
		return $privList;
	}
	
	
/****** Show a Table Schema ******/
	public static function showTable
	(
		$table			// <str> The table that you're retrieving the schema of.
	)					// RETURNS <void> OUTPUTS the table.
	
	// Database_Meta::showTable("users");
	{
		// Display the Table
		$values = self::getTableSchema($table);
		
		if($values !== false)
		{
			echo '
			<div style="margin-top:20px;">
			' . $table . '
			<table border="0" cellpadding="6" cellspacing="0" style="border:solid black 1px;">';
			
			foreach($values as $value)
			{
				echo '
				<tr>
					<td>' . $value['column_name'] . '</td>
					<td>' . $value['column_type'] . '</td>
					<td>default "' . $value['column_default'] . '"</td>
				</tr>';
			}
			
			echo '
			</table>
			</div>';
		}
	}
	
	
/****** Retrieve a Table Schema ******/
	public static function getTableList (
	)					// RETURNS <int:str> the list of tables in the database.
	
	// $tableList = Database_Meta::getTableList();
	{
		$tableList = array();
		$tables = Database::selectMultiple("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", array(Database::$databaseName));
		
		foreach($tables as $table)
		{
			$tableList[] = $table['table_name'];
		}
		
		return $tableList;
	}
	
	
/****** Retrieve a Table Schema ******/
	public static function getTableSchema
	(
		$table			// <str> The table that you're retrieving the schema of.
	,	$column = ""	// <str> If set, this indicates the specific column schema you'd like to view.
	)					// RETURNS <array> the table schema.
	
	// $tableSchema = Database_Meta::getTableSchema("users");
	{
		// Prepare Variable
		$sqlArray = array(Database::$databaseName, $table);
		
		if($column != "")
		{
			$sqlArray[] = $column;
			
			// Run the query to retrieve the schema
			return Database::selectOne("SELECT column_type, column_name, data_type, character_maximum_length, column_default, is_nullable FROM information_schema.columns WHERE table_schema = ? and table_name=? and column_name=?", $sqlArray);
		}
		
		// Run the query to retrieve the schema
		return Database::selectMultiple("SELECT column_type, column_name, data_type, character_maximum_length, column_default, is_nullable FROM information_schema.columns WHERE table_schema = ? and table_name=?", $sqlArray);
	}
	
	
/****** Check if a Table exists in the Database ******/
	public static function tableExists
	(
		$table			// <str> The name of the table that you'd like to check if it exists.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::tableExists("users");		// Checks if the table "users" exists or not
	{
		return (bool) Database::selectValue("SELECT COUNT(*) as doesExist FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1;", array(Database::$databaseName, $table));
	}
	
	
/****** Drop Table ******/
	public static function dropTable
	(
		$table		// <str> The table that you're dropping.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::dropTable("dumb_table");
	{
		return Database::exec("DROP TABLE IF EXISTS `" . Sanitize::variable($table, '-') . "`;");
	}
	
	
/****** Rename a Table ******/
	public static function renameTable
	(
		$table			// <str> The table that you're adding a column to.
	,	$newName		// <str> The name of the column you're adding.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::renameTable("users", "users_backup");
	{
		// Sanitize the table
		$table = Sanitize::variable($table);
		
		// Make sure this table exists
		if(!self::tableExists($table))
		{
			return false;
		}
		
		return Database::exec("RENAME TABLE `" . $table . "` TO `" . Sanitize::variable($newName) . "`");
	}
	
	
/****** Copy a Table ******/
	public static function copyTable
	(
		$table			// <str> The table that you're adding a column to.
	,	$copyTable		// <str> The name of the column you're adding.
	,	$andData = true	// <bool> If FALSE this will only copy the structure, not the data.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::copyTable("users", "users_backup");
	{
		$success = Database::exec("CREATE TABLE IF NOT EXISTS " . Sanitize::variable($copyTable) . " LIKE " . Sanitize::variable($table));
		
		if($success and $andData)
		{
			Database::exec("INSERT " . Sanitize::variable($copyTable) . " SELECT * FROM " . Sanitize::variable($table));
		}
		
		return true;
	}
	
	
/****** Retrieve Columns From Table ******/
	public static function getColumns
	(
		$table			// <str> The table that you're retrieving the columns of.
	)					// RETURNS <int:str> the list of columns in the table.
	
	// $columns = Database_Meta::getColumns("users");
	{
		$columns = array();
		$colData = self::getTableSchema($table);
		
		foreach($colData as $col)
		{
			$columns[] = $col['column_name'];
		}
		
		return $columns;
	}
	
	
/****** Retrieve column data from a table ******/
	public static function getColumnData
	(
		$table			// <str> The table that you're retrieving the column data of.
	,	$column			// <str> The column that you're getting data of.
	)					// RETURNS <int:str> the list of columns in the table.
	
	// $columnData = Database_Meta::getColumnData("users", "display_name");
	{
		return Database::selectOne("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?;", array(Database::$databaseName, $table, $column));
	}
	
	
/****** Check if a Column exists within a Table ******/
	public static function columnExists
	(
		$table			// <str> The name of the table that we're testing (to see if the column exists).
	,	$column			// <str> The name of the column to check exists.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::columnExists("users", "address");		// Checks if "address" column exists in "users" table
	{
		return (bool) Database::selectValue("SELECT COUNT(*) as doesExist FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1;", array(Database::$databaseName, $table, $column));
	}
	
	
/****** Checks if a list of columns all exist within a Table ******/
	public static function columnsExist
	(
		$table		// <str> The name of the table that we're testing (to see if the column exists).
	,	$columns	// <int:str> The list of columns to verify if they exist in the table or not.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::columnExists("users", array("id", "username"));
	{
		$checks = Database_Meta::getColumns($table);
		
		foreach($columns as $column)
		{
			if(!in_array($column, $checks))
			{
				return false;
			}
		}
		
		return true;
	}
	
	
/****** Add Column to Table ******/
	public static function addColumn
	(
		$table			// <str> The table that you're adding a column to.
	,	$columnToAdd	// <str> The name of the column you're adding.
	,	$columnData		// <str> The remaining column data to insert (e.g. int(10) unsigned not null)
	,	$default = ""	// <mixed> The default value you'd like to set.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::addColumn("users", "mailing_goodies", "tinyint(1) unsigned not null", 0);
	{
		// If the column already exists, return true
		if(self::columnExists($table, $columnToAdd))
		{
			return true;
		}
		
		// Prepare Default
		if($default !== "")
		{
			$default = " default " . (is_numeric($default) ? ($default + 0) : "'" . Sanitize::variable($default) . "'");
		}
		
		// Run the column alter
		return Database::exec("ALTER TABLE `" . Sanitize::variable($table) . "` ADD COLUMN `" . Sanitize::variable($columnToAdd) . "` " . Sanitize::variable($columnData, " ,()") . $default);
	}
	
	
/****** Edit Column on Table ******/
	public static function editColumn
	(
		$table			// <str> The table that you're adding a column to.
	,	$columnToEdit	// <str> The name of the column you're adding.
	,	$columnData		// <str> The remaining column data to insert (e.g. int(10) unsigned not null)
	,	$default = ""	// <mixed> The default value you'd like to set.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::editColumn("users", "mailing_goodies", "tinyint(1) unsigned not null", 0);
	{
		// If the column doesn't exist, return true
		if(!self::columnExists($table, $columnToEdit))
		{
			return false;
		}
		
		// Prepare Default
		$default = " default " . (is_numeric($default) ? ($default + 0) : "'" . Sanitize::variable($default) . "'");
		
		// Run the column alter
		return Database::exec("ALTER TABLE `" . Sanitize::variable($table) . "` MODIFY COLUMN `" . Sanitize::variable($columnToEdit) . "` " . Sanitize::variable($columnData, " ,()") . $default);
	}
	
	
/****** Drop Column from Table ******/
	public static function dropColumn
	(
		$table			// <str> The table that you're going to drop a column from.
	,	$columnToDrop	// <str> The name of the column you're going to drop.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::dropColumn("users", "unnecessary_column");
	{
		return self::dropColumns($table, $columnToDrop);
	}
	
	
/****** Drop Columns from Table ******/
	public static function dropColumns
	(
		$table			// <str> The table that you're adding a column to.
		// ARGS			// <str> Each extra argument is the name of a column you're dropping.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::dropColumns("users", "unnecessary_column", "extra_stuff");
	{
		// Prepare Variables
		$colSQL = "";
		$args = func_get_args();
		
		for($i = 1;$i < count($args);$i++)
		{
			// If the column doesn't exist, skip
			if(!self::columnExists($table, $args[$i]))
			{
				continue;
			}
			
			$colSQL .= ($colSQL != "" ? ',' : '') . " DROP COLUMN `" . Sanitize::variable($args[$i], '-') . "`";
		}
		
		if($colSQL == "") { return false; }
		
		// Run the column drop
		//echo "ALTER TABLE `" . Sanitize::variable($table, '-') . "` " . $colSQL . ';';
		return Database::exec("ALTER TABLE `" . Sanitize::variable($table, '-') . "` " . $colSQL . ';');
	}
	
	
/****** Drop Column from Table ******/
	public static function renameColumn
	(
		$table			// <str> The table that you're changing the name of a column in.
	,	$columnName		// <str> The name of the column you're going to rename.
	,	$newName		// <str> The new name of the column.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::renameColumn("users", "unnecessary_column", "better_name");
	{
		// If the column doesn't exist, skip
		if(!self::columnExists($table, $columnName))
		{
			return true;
		}
		
		// Gather the Schema
		$schema = self::getTableSchema($table, $columnName);
		
		// Prepare the Default Value
		$default = "";
		
		if($schema['column_default'] != null)
		{
			$default = " DEFAULT '" . $schema['column_default'] . "'";
		}
		
		// Run the Column Rename Query
		return Database::query("ALTER TABLE `" . Sanitize::variable($table, "-") . "` CHANGE `" . Sanitize::variable($columnName, "-") . "` `" . Sanitize::variable($newName, "-") . "` " . $schema['column_type'] . ($schema['is_nullable'] == "NO" ? " NOT NULL " : "") . $default, array());
	}
	
	
/****** Add an index to Table ******/
	public static function addIndex
	(
		$table				// <str> The table that you're modifying.
	,	$indexData			// <str> The index data that you're adding.
	,	$type = "INDEX"		// <str> The type of index you're adding to the table.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::addIndex($table, $indexData, $type = "INDEX")
	// Database_Meta::addIndex($table, "column, next_column", "UNIQUE")
	{
		// Prepare Values
		$type = strtoupper($type);
		$inData = explode($indexData);
		$name = $inData[0];
		
		if(!in_array($type, array("INDEX", "UNIQUE", "PRIMARY")))
		{
			return false;
		}
		
		return Database::exec("CREATE" . ($type == "INDEX" ? "" : " " . Sanitize::word($type)) . " INDEX " . $name . " ON `" . Sanitize::variable($table, '-') . "` (" . Sanitize::variable($indexData, " _,`") . ");");
	}
	
	
/****** Drop Index from Table ******/
	public static function dropIndex
	(
		$table		// <str> The table that you're modifying.
	,	$index		// <str> The name of the index to delete.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::dropIndex($table, $index);
	{
		return Database::exec("DROP INDEX " . Sanitize::variable($index) . " ON `" . Sanitize::variable($table, '-') . "`;");
	}
	
	
/****** Show Partition Distribution on the Table ******/
	public static function showPartitions
	(
		$table		// <str> The table to alter.
	)				// RETURNS <int:[str:mixed]> List of partitions and data.
	
	// Database_Meta::showPartitions($table)
	{
		return Database::selectMultiple("SELECT partition_name, partition_description, table_rows FROM information_schema.partitions WHERE table_schema=schema() AND table_name=?", array($table));
	}
	
	
/****** Set Partitions on the Table ******/
	public static function setPartitions
	(
		$table		// <str> The table to alter.
	,	$type		// <str> The type of partition for the table: "key", "hash"
	,	$column		// <str> The column (or columns) to set on the partition.
	,	$number		// <int> The number of partitions to set the table with.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::setPartitions($table, $type, $column, $number);
	{
		$type = strtoupper(Sanitize::word($type));
		
		if(!in_array($type, array("KEY", "HASH")))
		{
			return false;
		}
		
		return Database::exec("ALTER TABLE `" . Sanitize::variable($table) . "` PARTITION BY " . $type . "(`" . Sanitize::variable($column, " ,") . "`) PARTITIONS " . min($number + 0, 128) . ";");
	}
	
	
/****** Remove Partitions from the Table ******/
	public static function removePartitions
	(
		$table		// <str> The table to remove partitions from.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::removePartitions($table);
	{
		return Database::exec("ALTER TABLE " . Sanitize::variable($table) . " REMOVE PARTITIONING;");
	}
	
	
/****** Generate a range of partitions by date ******/
	public static function generateDatePartitions
	(
		$timeSegment = 8640000	// <int> The duration of time between partitions (default: 100 days).
	,	$totalPartitions = 61	// <int> The number of partitions to generate.
	)							// RETURNS <str> A string that defines date partitions.
	
	// $partitionSQL = Database_Meta::generateDatePartitions($timeSegment, $totalPartitions);
	{
		// Prepare the time ranges for the sync queue
		$timestamp = round(time() + $timeSegment, -4);
		$count = 0;
		
		$range = "";
		
		for($count = 0;$count < $totalPartitions - 1;$count++)
		{
			$range .= ($range == "" ? "" : ",") . '
			PARTITION p' . $count . ' VALUES LESS THAN (' . $timestamp . ')';
			
			$timestamp += $timeSegment;
		}
		
		$range .= ',
			PARTITION p' . $count . ' VALUES LESS THAN MAXVALUE';
		
		return $range;
	}
	
	
/****** Generate a range of partitions by numeric range ******/
	public static function generateNumericRangePartitions
	(
		$numDivision = 1000000	// <int> The numeric range to section into - default, 1 million entries.
	,	$totalPartitions = 61	// <int> The number of partitions to generate.
	)							// RETURNS <bool> A string that defines range partitions.
	
	// $partitionSQL = Database_Meta::generateNumericRangePartitions($numDivision, $totalPartitions);
	{
		// Prepare the time ranges for the sync queue
		$count = 0;
		$range = "";
		
		for($count = 0;$count < $totalPartitions - 1;$count++)
		{
			$range .= ($range == "" ? "" : ",") . '
			PARTITION p' . $count . ' VALUES LESS THAN (' . $numDivision . ')';
			
			$numDivision += $numDivision;
		}
		
		$range .= ',
			PARTITION p' . $count . ' VALUES LESS THAN MAXVALUE';
		
		return $range;
	}
	
	
/****** Remove Partitions from the Table ******/
	public static function changeCollation
	(
		$table			// <str> The table to remove partitions from.
	,	$type = "utf8"	// <str> The type of collation to change to.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::changeCollation($table, $type = "utf8");
	{
		return Database::exec("ALTER TABLE " . Sanitize::variable($table) . " CONVERT TO CHARACTER SET " . Sanitize::variable($type, "-") . ";");
	}
	
	
/****** Set Engine for a Table ******/
	public static function setEngine
	(
		$table				// <str> The table that you're setting the engine for.
	,	$engine = "INNODB"	// <str> The engine to use.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database_Meta::setEngine($table, $type = "INNODB")
	{
		$engine = strtoupper($engine);
		
		return Database::query("ALTER TABLE `" . Sanitize::variable($table, "-") . "` ENGINE = " . Sanitize::word($engine), array());
	}
	
	
/****** Create a Hash Column ******/
# This changes string indexes with crc32() hash indexes. This can improve lookup speed since it's an integer.
# It creates a new column named {origColumn}_crc, along with a self-updating trigger during inserts and updates.
	public static function createHashColumn
	(
		$table			// <str> The table that you're changing the name of a column in.
	,	$columnName		// <str> The name of the column you're going to rename.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Database::createHashColumn("users", "my_url");
	{
		// Sanitize
		$table = Sanitize::variable($table);
		$columnName = Sanitize::variable($columnName);
		
		$prefix = Sanitize::word(substr($table, 0, 4) . ucfirst(substr($columnName, 0, 6)));
		
		// Make sure table exists
		if(Database::tableExists($table))
		{
			$colExists = false;
			
			// Add the hash column if it doesn't exist
			if(!Database::columnExists($table, $columnName))
			{
				$colExists = Database::addColumn($table, $columnName . '_crc', "int(10) unsigned not null", 0);
			}
			
			if($colExists)
			{
				// Create a Trigger
				self::exec('CREATE TRIGGER ' . $prefix . '_ins BEFORE INSERT ON ' . $table . ' FOR EACH ROW BEGIN SET NEW.' . $columnName . '_crc=crc32(NEW.' . $columnName . '); END;');
				
				return self::exec('CREATE TRIGGER ' . $prefix . '_upd BEFORE UPDATE ON ' . $table . ' FOR EACH ROW BEGIN SET NEW.' . $columnName . '_crc=crc32(NEW.' . $columnName . '); END; ');
			}
		}
		
		return false;
	}
}
