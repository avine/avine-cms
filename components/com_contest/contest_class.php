<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Class
class comContest
{
	protected	$config,
				$project_id;

	const		RESOURCE_QUERY = 'code';



	/**
	 * Find the user's project in the current year of contest !
	 * Limitation: for now, a user can have only one project_id per config_year
	 */
	static public function findProjectID( $user_id )
	{
		global $db;

		$config_year = $db->selectOne('contest_config, year', 'year');

		if ($user_id && $config_year && ($project_id = $db->selectOne("contest_project, id, where: user_id=$user_id AND, where: config_year=$config_year", 'id')))
		{
			return $project_id;
		} else {
			return false;
		}
	}



	public function __construct( $project_id = false )
	{
		global $db;

		// Get config
		$this->config = $db->selectOne('contest_config, *');

		// Check config
		foreach($this->config as $key => $value) {
			$value or trigger_error("The 'contest_config' table is not configured", E_USER_ERROR);
		}

		// Check 'resource_path' availability
		is_dir($this->config['resource_path']) or trigger_error("The directory resource_path={$this->config['resource_path']} doesn't exists", E_USER_ERROR);

		// Set project_id
		$this->project_id = $project_id;
	}



	public function getConfig( $key = '' )
	{
		if (!$key) {
			return $this->config;							# array
		}
		elseif (array_key_exists($key, $this->config)) {
			return $this->config[$key];						# string
		}
		else {
			trigger_error("Invalid \$key=$key parameter", E_USER_ERROR);
		}
	}



	public function isDeadlineExpired( &$message )
	{
		if (time() > $this->config['deadline'])
		{
			$message = userMessage(LANG_COM_CONTEST_DEADLINE_EXPIRED.getTime($this->config['deadline'], 'format=long;time=no'), 'warning');
			return true;
		} else {
			return false;
		}
	}



	public function isProjectValidated( &$message )
	{
		if ($this->project_id)
		{
			global $db;
			if ($db->selectOne("contest_project, user_validation, where: id=$this->project_id", 'user_validation'))
			{
				$message = userMessage(LANG_COM_CONTEST_PROJECT_USER_VALIDATED_YES, 'info');
				return true;
			} else {
				$message = userMessage(LANG_COM_CONTEST_PROJECT_USER_VALIDATED_NO, 'error');
				return false;
			}
		}
		return NULL;
	}



	public function isFormAuthorized( &$message )
	{
		$deadline	= $this->isDeadlineExpired	($mess_deadline	);
		$validated	= $this->isProjectValidated	($mess_validated);

		if ($deadline || $validated)
		{
			$message = $mess_deadline.$mess_validated;
			return false;
		}
		return true;
	}



	public function getProject()
	{
		global $db;

		if (!$this->project_id)
		{
			return false;
		}
		elseif ($project = $db->selectOne("contest_project, *, where: id=$this->project_id"))
		{
			return $project;
		}

		trigger_error("Invalid \$project_id=$project_id property", E_USER_ERROR);
	}



	static public function emptyProject()
	{
		global $db;
		$project = array_keys($db->db_describe('contest_project'));

		return array_fill_keys($project, '');
	}



	/**
	 * Create/update a project in the database
	 */
	public function updateProject( $project )
	{
		global $db;

		// $project struture
		$model = self::emptyProject();

		// Get the fields of the 'contest_project' table which are type of INT
		$fields_typeof_int = array();
		$fields = $db->db_describe('contest_project');
		foreach ($fields as $field => $type) {
			if ($type == 'int') {
				$fields_typeof_int[] = $field;
			}
		}

		foreach($project as $key => $value)
		{
			array_key_exists($key, $model) or trigger_error("Invalid key=$key in the \$project parameter", E_USER_ERROR);

			if ($key != 'id') # The 'id' is defined by the $project_id property
			{
				in_array($key, $fields_typeof_int) or $value = $db->str_encode($value);

				// For insert query
				$col[] = $key;
				$val[] = $value;

				// For update query
				$values[]= "$key=$value";
			}
		}

		// Create project
		if (!$this->project_id)
		{
			if (!in_array('user_id', $col))
			{
				global $g_user_login;
				($user_id = $g_user_login->userID()) or trigger_error("Missing 'user_id' information to insert new project", E_USER_ERROR);

				// Associate the new project to the logged user
				$col[] = 'user_id';
				$val[] = $user_id;
			}

			if (!in_array('config_year', $col))
			{
				// Associate the new project to the current year's contest
				$col[] = 'config_year';
				$val[] = $this->config['year'];
			}

			$result = $db->insert('contest_project; col: '.implode(',', $col).'; '.implode(',', $val));

			// Set project_id property
			$result ? $this->project_id = $db->insertID() : '';
		}
		// Update project
		else
		{
			$result = $db->update('contest_project; '.implode(',', $values).'; where: id='.$this->project_id);
		}

		return $result;
	}



	static public function yearOptions( $first, $last, $selected = false )
	{
		// Check $selected
		if ($selected) {
			$first	<= $selected or $first	= $selected;
			$last	>= $selected or $last	= $selected;
		}

		$options[''] = LANG_SELECT_OPTION_ROOT;
		for ($i=$first; $i<=$last; $i++) {
			$options[$i] = self::viewYear($i);
		}

		if ($selected) {
			$options = formManager::selectOption($options, $selected);
		}
		return $options;
	}



	static public function viewYear( $year )
	{
		return "$year / ".($year+1);	# Academic year

		#return $year;					# Classic year
	}



	public function getResource( $online = false )
	{
		$online ? $online = ' AND, where: verified=1 AND, where: published=1' : $online = '';

		global $db;
		if ($this->project_id && ($resource = $db->select("contest_resource, *, resource_order(asc), [id(asc)], where: project_id=$this->project_id{$online}")))
		{
			$ftp = new ftpManager($this->projectResourcePath());
			foreach($resource as $id => $info)
			{
				// Add info
				$resource[$id]['file_exists'] = $ftp->isFile($resource[$id]['file_name']);
			}
			return $resource;
		}
		return NULL;
	}



	/**
	 * Add a new resource (in FTP and DB)
	 * 
	 * @param $file must match the return of the method comManager_filter::getUploadedfile()
	 */
	public function addResource( $file, $title )
	{
		// Check project_id
		$this->project_id or trigger_error('Undefined $project_id property', E_USER_ERROR);

		// Check $file parameter
		!array_diff(array('name','type','tmp_name','error','size'), array_keys($file)) or trigger_error('Invalid parameter $file', E_USER_ERROR);

		// Check $title
		$title or trigger_error('Missing parameter $title', E_USER_ERROR);

		// Debug mode
		#echo "Ready to upload file in : ".$this->projectResourcePath(); alt_print_r($file); return;

		// FTP : connexion
		clearstatcache();
		$ftp = new ftpManager($this->projectResourcePath());

		// FTP : Move uploaded file
		if ($file_name = $ftp->moveUploadedFile($file['tmp_name'], $file['name'], false, true))
		{
			global $db;

			// DB : Prevent duplicate file_name for this project
			$db->delete("contest_resource; where: project_id=$this->project_id AND file_name=".$db->str_encode($file_name));

			// DB : insert new resource
			if ($db->insert(
					"contest_resource; col: project_id, file_name, code, title; ".
					$this->project_id				.', '.
					$db->str_encode($file_name)		.', '.
					$db->str_encode(md5(rand()))	.', '.
					$db->str_encode($title)
			)) {
				$insert_id = $db->insertID();
			} else {
				$insert_id = false;
			}
		}
		else {
			$insert_id = NULL;
		}

		// Result
		return
			array(
				'ftp'	=> $file_name,
				'db'	=> $insert_id
			);
	}



	/**
	 * Delete a resource (from FTP and DB)
	 */
	public function delResource( $resource_id, $remove_from_ftp = true )
	{
		global $db;

		// Check project_id
		$this->project_id or trigger_error('Undefined $project_id property', E_USER_ERROR);

		// FTP
		if ($remove_from_ftp && ($file_name = $db->selectOne("contest_resource, file_name,  where: id=$resource_id", 'file_name')))
		{
			// Debug mode
			#echo "Ready to delete file : ".$this->projectResourcePath().$file_name; return;

			clearstatcache();
			$ftp = new ftpManager($this->projectResourcePath());

			if ($ftp->isFile($file_name))
			{
				$result['ftp'] = $ftp->delete($file_name);
			} else {
				$result['ftp'] = NULL;
			}
		}

		// DB
		$result['db'] = $db->delete("contest_resource; where: id=$resource_id");

		// Return
		if ($remove_from_ftp)
		{
			return $result;			# array
		} else {
			return $result['db'];	# boolean
		}
	}



	/**
	 * Here is the location of the current project resources
	 */
	public function projectResourcePath()
	{
		if ($this->project_id)
		{
			return $this->config['resource_path'].$this->project_id.'/';
		} else {
			return false;
		}
	}



	static public function resourceHref( $resource_id )
	{
		global $db;

		if ($resource = $db->selectOne("contest_resource, file_name,code, where: id=$resource_id"))
		{
			$pathinfo = pathinfo($resource['file_name']);

			$code = $resource['code'].'.'.$pathinfo['extension'];

			return siteUrl().'/components/com_contest/view.php?'.self::RESOURCE_QUERY."=$code";
		}

		trigger_error("Invalid parameter \$resource_id=$resource_id", E_USER_ERROR);
	}



	static public function resourceID( $code )
	{
		global $db;

		// Check $code
		$code = explode('.', $code);
		if (!formManager_filter::isMd5($code[0]) || ($code[1] && !formManager_filter::isVar($code[1]))) {
			return false;
		}

		// Match code
		if ($resource = $db->selectOne('contest_resource, id,file_name, where: code='.$db->str_encode($code[0])))
		{
			// Match extension
			$pathinfo = pathinfo($resource['file_name']);
			if ($code[1] == $pathinfo['extension'])
			{
				return $resource['id'];
			}
		}

		return false;
	}

}


?>