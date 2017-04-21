<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



class databaseManager
{
	private	$db_host = DB_HOST,
			$db_user = DB_USER,
			$db_pass = DB_PASS,
			$db_name = DB_NAME;

	private	$table_prefix = DB_TABLE_PREFIX;

	/*
	 * When using the 'select()' method, you can have a quick html-view of the results.
	 * There are temporarily stored into the $select_results array.
	 * Simply call the 'viewSelect()' method wich display an html-table of the results.
	 */
	private	$select_results = array(); 

	private	$debug,		# boolean
			$log;		# integer (0= display inline; 1= display inlog; 2= display both)



	public function __construct( $debug = false, $log = 1 )
	{
		$this->debugMode($debug);

		/*
		 * If the $log is activated, then the messages are displayed by the debugManager() which needs to be included somewhere...
		 * Notice, that in that case, the messages that might be added after the call of new debugManager(); will not appear...
		 * (example : $db->db_close() method which is called at the end of all scripts)
		 */
		$this->log	= $log;
	}



	public function debugMode( $debug )
	{
		$debug	? $this->debug	= true : $this->debug	= false;
	}



	public function db_connect( $db_host = '', $db_user = '', $db_pass = '', $db_name = '', $table_prefix = '?' )
	{
		$db_host != '' ? $this->db_host = $db_host : '';
		$db_user != '' ? $this->db_user = $db_user : '';
		$db_pass != '' ? $this->db_pass = $db_pass : '';
		$db_name != '' ? $this->db_name = $db_name : '';

		$this->db_changeTablePrefix($table_prefix);

		try {
			$dbh =@ new PDO("mysql:host={$this->db_host};dbname={$this->db_name}", $this->db_user, $this->db_pass);
			#$dbh->setAttribute(); # TODO Add some attributes if necessary...
		}
		catch (PDOException $e)
		{
			# TODO - Record the occured error connection in a file and read it in the home page of the Admin.
			# Or send and email to the admin (but you need to write this email somewhere in a file...)

			// Send charset in the header
			header('Content-Type: text/html; charset=utf-8');

			die(
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n".
				"<html>\n<head>\n".
				'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n".
				'<title>'.LANG_DB_MANAGER_CONNECTION_FAILURE_TITLE."</title>\n".
				'<meta name="robots" content="noindex" />'."\n".
				'<meta name="revisit-after" content="1 day" />'."\n". # Useless against serach-engine but more academic...
				'<link rel="stylesheet" type="text/css" href="'.WEBSITE_PATH.'/libraries/lib_database/database_style.css" />'."\n".
				"</head>\n".'<body id="database-manager-error">'."\n".
				'<p>'.'<img src="'.WEBSITE_PATH.'/libraries/lib_database/images/waiting.gif" alt="" />&nbsp; '.LANG_DB_MANAGER_CONNECTION_FAILURE."</p>\n".
				'<div><img src="'.WEBSITE_PATH.'/libraries/lib_database/images/error.png" alt="" /></div>'.
				#'<div>'.$e->getMessage().'</div>'.
				"\n</body>\n</html>"
			);
		}

		if ($dbh->exec("SET NAMES 'utf8'") === false) {
			echo trigger_error('Error loading character set utf8 in '.__METHOD__);
		}

		// Put the connection in the dbh() singleton
		self::dbh($dbh);

		if ($this->debug) {
			$this->debugMessage('class= databaseManager | method= db_connect {success}', 'open,close');
		}
	}



	// Use this method to access tables in the same database, wich have another prefix
	public function db_changeTablePrefix( $table_prefix )
	{
		if ($table_prefix !== '?') {
			$this->table_prefix = $table_prefix;
		}
	}



	public function db_close()
	{
		if (!self::dbh(NULL)) {
			echo LANG_DB_MANAGER_DISCONNECTION_FAILURE;
		}
		else {
			if ($this->debug) {
				// Notice : this message will not appear if ($this->log == 1), because the debugManager::getMessage() method has been already called
				$this->debugMessage('class= databaseManager | method= db_close {success}', 'open,close');
			}
		}
	}



	// The database connection is stored here in this singleton
	private static function dbh( $connection = false )
	{
		static $dbh = NULL;

		if ($connection === NULL)
		{
			if ($dbh !== NULL)
			{
				$dbh = NULL;					# Close
				return true;
			} else {
				return false;
			}
		}
		elseif ($dbh !== NULL) {
			return $dbh;						# Get
		}
		elseif ($connection !== false) {
			return $dbh = $connection;			# Init
		}
		else {
			trigger_error("Invalid call of ".__METHOD__);
		}
	}



	// Send a regular MySQL query
	public function sendMysqlQuery( $param )
	{
		if ($this->debug)
		{
			$param_debug = str_replace("<", '&lt;', $param);
			$param_debug = str_replace(">", '&gt;', $param_debug);
			$this->debugMessage("class= databaseManager | method= sendMysqlQuery | param= $param_debug", 'open,title');
		}

		$query = str_replace('{table_prefix}', $this->table_prefix, $param);

		$results = self::dbh()->query($query); # FIXME - Actually it's not possible to choose between ->query() and ->exec()...

		if ($this->debug)
		{
			if ($results) {
				$this->debugMessage("query= $query {success}", 'content,close');
			} else {
				$this->debugMessage("query= $query {failed}", 'content,close');
			}
		}

		return $results;
	}



	// Fetch the return of sendMysqlQuery() method (for a SELECT for example)
	public function fetchMysqlResults( $results, $key_assoc = '' )
	{
		$select_results = array();

		if ($key_assoc == '')	# The first key of the array is simply an integer 0, 1, 2, ... as usual
		{
			$select_results = $results->fetchAll(PDO::FETCH_ASSOC);
		}
		else					# Here the first key of the array is one of the requested fields !
		{
			while($result = $results->fetch(PDO::FETCH_ASSOC))
			{
				$temp_array = array();
				foreach($result as $key => $value)
				{
					if ($key != $key_assoc) {
						$temp_array[$key] = $value;
					} else {
						$value_assoc = $value;
					}
				}
				$select_results[$value_assoc] = $temp_array;
			}
		}

		$this->select_results = $select_results;

		return $select_results;
	}



	public function select( $param )
	{
		if ($this->debug) {
			$param_debug = str_replace("<", '&lt;', $param);
			$param_debug = str_replace(">", '&gt;', $param_debug);
			$this->debugMessage("class= databaseManager | method= select | param= $param_debug", 'open,title');
		}

		/**
		 * This method return an array of the results, one result per row like: $select_result[$i].
		 * Each row contain the requested fields like: $select_result[$i]['field1'], $select_result[$i]['field2'], ...
		 *
		 * By default the KEY of $select_result[$i] is simply: $i = 0, 1, 2, ...
		 * But one of the requested fields can be the KEY like this:
		 * $select_result[ $select_result[0]['field1'] ]['field2']
		 * $select_result[ $select_result[1]['field1'] ]['field2']
		 * ...
		 *
		 * This feature use the $key_assoc variable
		 */
		$key_assoc = '';

 
		////////////////////
		// Create the query
 
		$select  = ' SELECT ';
		$from    = ' FROM ';
		$alias = 0;

		$join    = ''; # Optional
		$where   = ''; # idem
		$orderby = ''; # idem
		$limit   = ''; # idem

		/**
		 * Careful: ';' is our separator!
		 *
		 * If the parameters contains "," or ";" ( ex.:  where:text='this, is a problem;' ) 
		 * then you must use the '$this->str_encode()' method to encode those particular strings
		 * it will replace   ';' by '{semicolon}'   and   ',' by '{comma}'
		 * After that, the class itself will use automaticaly the '$this->str_decode()' method to reverse the endode method and get the original string
		 */
		$param = explode(';', $param); 

		for ($i=0; $i<count($param); $i++)
		{
			$param[$i] = trim($param[$i]);

			// 'LIMIT'
			if ( preg_match('~^(limit:)~', $param[$i]) )
			{
				$param[$i] = preg_replace('~^(limit:)~', '', $param[$i]);
				$param[$i] = trim($param[$i]);

				$limit = " LIMIT $param[$i] ";
			}
			elseif ($param[$i] != "")
			{
				/**
				 * Careful: ',' is also our separator!
				 * See before for details...
				 */
				$table_param = explode(',', $param[$i]);

				// 'FROM'
				$table_name = $this->table_prefix.trim($table_param[0]);
				$from .= $table_name." AS a$alias, ";

				for ($j=1; $j<count($table_param); $j++)
				{
					$table_param[$j] = trim($table_param[$j]);

					// 'WHERE'
					if ( preg_match('~^(where:)~', $table_param[$j]) )
					{
						$table_param[$j] = preg_replace('~^(where:)~', '', $table_param[$j]);
						$table_param[$j] = trim($table_param[$j]);
						$table_param[$j] = $this->str_decode($table_param[$j]);

						$where .= " a$alias.".$table_param[$j].' ';
					}
					// 'JOIN'
					elseif ( preg_match('~^(join:)~', $table_param[$j]) )
					{
						$table_param[$j] = preg_replace('~^(join:)~', '', $table_param[$j]);
						$table_param[$j] = trim($table_param[$j]);

						if ( (preg_match('~^<~', $table_param[$j])) && (preg_match('~>$~', $table_param[$j])) ) 	# Middle join
						{
							if (strstr($table_param[$j], '|'))
							{
								$temp = explode('|', $table_param[$j]);
								$join .= " a$alias.".preg_replace('~^<~', '', $temp[0]).' AND '."a$alias.".preg_replace('~>$~', '', $temp[1]).' = ';
							} else {
								$join .= " a$alias.".$table_param[$j].' AND '."a$alias.".$table_param[$j].' = ';
							}
						}
						elseif (preg_match('~^<~', $table_param[$j])) {
							$join .= " a$alias.".preg_replace('~^<~', '', $table_param[$j]); 						# Last join
						}
						else {
							$join .= " a$alias.".preg_replace('~>$~', '', $table_param[$j]).' = '; 					# First join
						}
					}
					else
					{
						// [KEY_ASSOC] (special feature)
						if ( (preg_match('~^\[~', $table_param[$j])) && (preg_match('~\]$~', $table_param[$j])) )
						{
							$table_param[$j] = preg_replace('~(^\[|\]$)~', '', $table_param[$j]);
							$table_param[$j] = trim($table_param[$j]);

							$key_assoc = -1; 			# Request detected - stand by for processing
						}

						// 'ORDER BY'
						if ( preg_match('~(\(asc\)|\(desc\))$~', $table_param[$j]) )
						{
							if ( preg_match('~\(asc\)$~', $table_param[$j]) ) {
								$asc_desc = ' ASC ';
							}
							else {
								$asc_desc = ' DESC ';
							}

							$table_param[$j] = preg_replace('~(\(asc\)|\(desc\))$~', '', $table_param[$j]);
							$table_param[$j] = trim($table_param[$j]);

							$orderby_detected = true; 	# Request detected - stand by for processing
						}
						else {
							$orderby_detected = false;
						}

						// 'AS'
						if ( preg_match('~(\s)+(AS)(\s)+~i', $table_param[$j]) )
						{
							$table_param[$j] = preg_replace('~(\s)+(AS)(\s)+~i', ' AS ',  $table_param[$j]);

							$name_as_alias = explode(' AS ', $table_param[$j]);
							$field_name  = trim($name_as_alias[0]);
							$field_alias = trim($name_as_alias[1]);
						}
						else {
							$field_name = $table_param[$j];
							$field_alias = $table_param[$j];
						}

						// Process now detected requests (orderby & key_assoc)
						if ($orderby_detected) {
							$orderby .= "a$alias.".$field_name.$asc_desc.', ';
						}
						if ($key_assoc == -1) {
							$key_assoc = $field_alias;
						}
            
						// 'SELECT'
						$select .= $this->addAlias($table_param[$j], " a$alias.").', ';
					}
				}
				$alias++;
			}
		}

		// Final query
		$query  = preg_replace('~,\s*$~', ' ', $select);
		$query .= preg_replace('~,\s*$~', ' ', $from  );

		if ($join != '')
		{
			$query .= ' WHERE '.$join;
			if ($where != '') {
				$query .= ' AND '.$where;
			}
		}
		elseif ($where != '') {
			$query .= ' WHERE '.$where;
		}

		if ($orderby != '') {
			$query .= ' ORDER BY '.preg_replace('~,\s*$~', ' ', $orderby);
		}

		$query .= $limit; # The query is now complete!


		/////////////////////////////
		// Process the query results

		$results = self::dbh()->query($query);

		if ($this->debug)
		{
			if ($results) {
				$this->debugMessage("query= $query {success}", 'content,close');
			} else {
				$this->debugMessage("query= $query {failed}", 'content,close');
			}
		}

		$select_results = array();
		if ($results)
		{
			if ($key_assoc == '') 	# The first key of the array is simply an integer 0, 1, 2, ... as usual
			{
				$select_results = $results->fetchAll(PDO::FETCH_ASSOC);
			}
			else 					# Here the first key of the array is one of the requested fields !
			{
				while($result = $results->fetch(PDO::FETCH_ASSOC))
				{
					$temp_array = array();
					foreach($result as $key => $value)
					{
						if ($key != $key_assoc) {
							$temp_array[$key] = $value;
						} else {
							$value_assoc = $value;
						}
					}
					$select_results[$value_assoc] = $temp_array;
				}
			}
			$this->select_results = $select_results;
		}

		return $select_results;
	}



	private function addAlias($expression, $alias)
	{
		$no_alias = array('*', 'count(*)');

		if (!in_array($expression, $no_alias))
		{
			return $alias.$expression;
		} else {
			return $expression;
		}
	}



	public function viewSelect()
	{
		if (!count($this->select_results)) {
			trigger_error('There\'s is no result to display ! in '.__METHOD__);
			return;
		}

		// Header
		$header = "<tr><th>&nbsp;</th>";

		reset($this->select_results);
		list($row, $values) = each($this->select_results);
		foreach($values as $key => $value) {
			$header .= "<th>$key</th>";
		}
		$header .= "</tr>\n";

		// Body
		$body = "";

		reset($this->select_results);
		foreach($this->select_results as $row => $values)
		{
			$body .= "<tr><th>$row</th>";
			foreach($values as $key => $value) {
				$body .= "<td>$value</td>";
			}
			$body .= "</tr>\n";
		}

		// Html table (Notice that we are using the class=\"table-manager\" wich come from the tableManager class)
		$html = "<div class=\"table-manager\"><table border=\"1\" cellspacing=\"0\">\n".$header.$body."</table></div>\n";

		echo $html;
	}



	/**
	 * Here a short way to get the records number of a table
	 *
	 * Instead of writing:
	 *		$count = $db->select(" my_table, count(*), where: my_field='{value}' ");
	 *		$count = $count[0]['count(*)];
	 *
	 * Simply write:
	 *		$count = $db->selectCount(" my_table, where: my_field='{value}' ");
	 */
	public function selectCount( $param )
	{
		// Include the 'count(*)' part of the query
		$param = explode(';', $param);
		$param[0] .= ', count(*)';
		$param = implode(';', $param);		

		// Process query
		$result = $this->select($param);

		// Return the count
		return $result[0]['count(*)'];
	}



	/**
	 * Here a short way to get one records of a table
	 *
	 * Instead of writing:
	 *		$config = $db->select('my_table, *');
	 *		$config = $config[0];
	 *
	 * Simply write:
	 *		$config = $db->selectOne('my_table, *');
	 */
	public function selectOne( $param, $field = false, $pos = 0 )
	{
		$result = $this->select($param);

		if (  $field && count($result) && array_key_exists($field, $result[$pos]) )
		{
			return $result[$pos][$field];
		}
		elseif ( array_key_exists($pos, $result) )
		{
			return $result[$pos];
		}

		return false;
	}



	public function insert( $param )
	{
		if ($this->debug) {
			$this->debugMessage("class= databaseManager | method= insert | param= $param", 'open,title');
		}

		$columns = "";
		$values = "";

		$param = explode(';', $param);

		$table_name = $this->table_prefix.trim($param[0]); # Table name

		for ($i=1; $i<count($param); $i++)
		{
			$param[$i] = trim($param[$i]);

			if ( preg_match('~^(col:)~', $param[$i]) )
			{
				$columns = ' ('.preg_replace('~^(col:)(\s*)~', '', $param[$i]).') '; # Specified columns
			}
			elseif ($param[$i] != "")
			{
				$values .= "(".$this->str_decode($param[$i])."), "; # Values
			}
		}

		if (!$values)
		{
			$values = '()';
		} else {
			$values = preg_replace('~,\s*$~', '', $values);
		}

		$query = " INSERT INTO $table_name $columns VALUES $values";

		$result = self::dbh()->exec($query);

		if ($this->debug)
		{
			if ($result !== false) {
				$this->debugMessage("query= $query {success}", 'content,close');
			} else {
				$this->debugMessage("query= $query {failed}", 'content,close');
			}
		}

		if ($result === false) {
			return false;
		} else {
			return true; # Simplified
		}
	}



	public function update( $param )
	{
		if ($this->debug) {
			$this->debugMessage("class= databaseManager | method= update | param= $param", 'open,title');
		}

		$param = explode(';', $param);

		$table_name = $this->table_prefix.trim($param[0]); # Table name

		$param[1] = trim($param[1]);
		$values = $this->str_decode($param[1]); # Requested fields

		if (isset($param[2]))
		{
			$param[2] = trim($param[2]);
			$param[2] = preg_replace('~^(where:)~', '', $param[2]);

			$conditions = $this->str_decode($param[2]); # 'where' condition
      
			$query = " UPDATE $table_name SET $values WHERE $conditions ";
		} else {
			$query = " UPDATE $table_name SET $values ";
		}

		$result = self::dbh()->exec($query);

		if ($this->debug)
		{
			if ($result !== false) {
				$this->debugMessage("query= $query {success}", 'content,close');
			} else {
				$this->debugMessage("query= $query {failed}", 'content,close');
			}
		}

		if ($result === false) {
			return false;
		} else {
			return true; # Simplified
		}
	}



	public function insertID()
	{
		return self::dbh()->lastInsertId();
	}



	public function delete( $param )
	{
		if ($this->debug) {
			$this->debugMessage("class= databaseManager | method= delete | param= $param", 'open,title');
		}

		$param = explode(';', $param);

		$table_name = $this->table_prefix.trim($param[0]); # Table name

		if (isset($param[1]))
		{
			$param[1] = trim($param[1]);
			$param[1] = preg_replace('~^(where:)~', '', $param[1]);
	
			$conditions = $this->str_decode($param[1]); # 'where' condition
	
			$query = " DELETE FROM $table_name WHERE $conditions ";
		} else {
			$query = " DELETE FROM $table_name ";
		}

		$result = self::dbh()->exec($query);

		if ($this->debug)
		{
			if ($result !== false) {
				$this->debugMessage("query= $query {success}", 'content,close');
			} else {
				$this->debugMessage("query= $query {failed}", 'content,close');
			}
		}

		if ($result === false) {
			return false;
		} else {
			return true; # Simplified
		}
	}



	/**
	 * (1) Our query analizer is using ',' and ';' as arguments separators.
	 * Then the arguments should never contain those characters!
	 * 
	 * To get that, we use the '$this->str_encode()' method.
	 * It will replace:   ';' by '{semicolon}'   and   ',' by '{comma}'
	 * The '$this->str_decode()' will be automaticaly used by the class itself to reverse the encode method and get the original string
	 *  
	 * So, when using all methods: 'select()', 'insert()', 'update()' or 'delete()', use the str_encode method like this:
	 * 
	 * 			$name = "Stéphane, Francel;";
	 *  		$db->select("my_table, id, where: name=".str_encode($name));
	 * 
	 * This code will be like:  $db->select("my_table, id, where: name='Stéphane{comma} Francel{semicolon}'");
	 * 
	 * (2) Security Verification:
	 * We are checking here that when necessary, our strip_magic_quotes() function is applied (this is a user function).
	 * This function reverse the magic_quotes_gpc() effect (ie: emulate magic_quotes_gpc=off).
	 * For details watch : '/global/functions.php' (we have the same problem into '/libraries/lib_form/form_class.php').
	 * After that to prevent SQL injections, we are using the quote() PDO method.
	 */
	public function str_encode( $string )
	{
		// (1) Separators encodage
		$string = str_replace(';' ,'{semicolon}', $string);
		$string = str_replace(',' ,'{comma}'    , $string);

		// (2) Slashes encodage
		if ( (!get_magic_quotes_gpc()) || ((get_magic_quotes_gpc()) && (defined('STRIP_MAGIC_QUOTES'))) )
		{
			$string = self::dbh()->quote($string);
		} else {
			$string = self::dbh()->quote(stripslashes($string));
			echo '<span style="color:red;">warning: emulate magic_quotes_gpc=false failed! into \'<strong>/libraries/lib_database/database_class.php</strong>\'. &nbsp;The system continue to work, but errors could appears into recorded fields.</span><br />';
		}

		return $string;
	}



	public function str_decode( $string )
	{
		$string = str_replace('{semicolon}', ';', $string);
		$string = str_replace('{comma}'    , ',', $string);

		return $string;
	}



	public function db_show()
	{
		$tables = array();

		$query = " SHOW TABLES ";

		$results = self::dbh()->query($query);

		if ($results) {
			while($result = $results->fetch(PDO::FETCH_NUM)) {
				$tables[] = $result[0]; # Table name
			}
		}

		return $tables;
	}



	public function db_describe( $table_name, $generic_type = true )
	{
		$query = " DESCRIBE ".$this->table_prefix.trim($table_name);

		$results = self::dbh()->query($query);

		if (!$results) {
			echo trigger_error("Invalid \$table_name=$table_name in ".__METHOD__);
			return;
		}

		while($result = $results->fetch(PDO::FETCH_ASSOC))
		{
			if ($generic_type) {
				$result['Type'] = self::db_genericFieldType($result['Type']);
			}
			$fields[$result['Field']] = $result['Type'];
		}

		return $fields;
	}



	public static function db_genericFieldType( $type )
	{
		$type = trim(preg_replace('~\(.+\)~', '', $type));

		switch($type)
		{
			case 'int':
			case 'smallint':
			case 'tinyint':
				return 'int';

			case 'varchar':
			case 'text':
			case 'mediumtext':
			case 'tinytext':
				return 'char';

			case 'enum':
				return 'enum';

			default:
				trigger_error("Method needs upgrade ! No generic field has been defined for \$type= $type in ".__METHOD__);
		}
	}



	private function debugMessage( $message = '', $param )
	{
		static $count = 1;

		// Str_replace
		$str_replace =
			array(
				'{success}' => '<span class="success">(SUCCESS)</span>',
				'{failed}'  => '<span class="failure">(FAILED)</span>',
			);

		foreach($str_replace as $search => $replace) {
			$message = str_replace($search, $replace, $message);
		}

		// Param
		$open  = false;
		$close = false;
		$default = true;
		$param = explode(',', $param);
		for ($i=0; $i<count($param); $i++)
		{
			switch($param[$i])
			{
				case 'open'   :
					$open  = true;
					break;
				case 'close'  :
					$close = true;
					break;
				case 'title'  :
					$current = '<span class="count">('.$count++.')</span> ';
					$message = '<p class="title">' .$current.$message. '</p>';
					$default = false;
					break;
				case 'content':
					$message = '<p class="content">' .$message. '</p>';
					$default = false; break;
			}
		}

		if ($default) {
			$message = '<p class="title">'.$message.'</p>';
		}
		if ($open) {
			$message = "\n".'<div class="database-manager-debug">'.$message;
		}
		if ($close) {
			$message = $message.'</div>';
		}

		// Log message
		if ($this->log)									# == 1 or 2
		{
			debugManager::setMessageAttribute(__CLASS__, 'Database Manager <span>(Queries)</span>');
			debugManager::addMessage(__CLASS__, $message);
		}

		// Display message
		if ($this->log === 0 || $this->log == 2) {		# == 0 or 2
			echo $message;
		}
	}

}

?>