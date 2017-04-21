<?php

/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

/**
 * Get quickly a user details (readonly)
 */
class comUser_details
{
	protected $user_details = array();



	public function __construct( $user_id, $debug = false )
	{
		$user_db = new comUser_db();
		$user_details = $user_db->selectUser($user_id);
		if ($user_details)
		{
			// Unset useless fields
			unset($user_details[ 'password'			]); # the password has been encoded by sha1()
			unset($user_details[ 'activation_code'	]); # this is an system information wich is not a really a "user details"
			unset($user_details[ 'user_id'			]); # same as $user_details['id']
	
			// Set property
			$this->user_details = $user_details;
	
			if ($debug) {
				$table = new tableManager($this->user_details);
				echo $table->html(1);
			}
		}
		else {
			#echo "<p style=\"color:red;\">Error occured in <b>".__METHOD__."</b> method : invalid <i>user_id=$user_id</i></p>";
		}
	}



	public function isInvalidUserID()
	{
		if (!count($this->user_details)) {
			return true;
		} else {
			return false;
		}
	}



	public function get( $key, $option = '' )
	{
		if ($this->isInvalidUserID()) {
			return false;
		}

		if (!array_key_exists($key, $this->user_details))
		{
			echo "<p style=\"color:red;\">Error occured in <b>".__METHOD__."</b> method : invalid <i>\$key=$key</i> parameter</p>";
			return false;
		}

		return $this->formatString($this->user_details[$key], $option);
	}



	public function getFullName( $first_name_first = true )
	{
		if ($this->isInvalidUserID()) {
			return false;
		}

		$fullname = '';

		$first_name = $this->user_details['first_name'];
		$last_name 	= $this->user_details['last_name' ];

		if ($first_name || $last_name)
		{
			if ($first_name_first)
			{
				$fullname = "$first_name $last_name";
			} else {
				$fullname = "$last_name $first_name";
			}
		}

		return trim($fullname);
	}



	public function getFullAdress( $sep = "\n", $zip_sep_city = ' ' )
	{
		if ($this->isInvalidUserID()) {
			return false;
		}

		$fulladress = '';

		$adress_1 	= $this->user_details['adress_1'];
		$adress_2 	= $this->user_details['adress_2'];
		$city 		= $this->user_details['city'	];
		$state 		= $this->user_details['state'	];
		$country 	= $this->user_details['country'	];
		$zip 		= $this->user_details['zip'		];

		$adress_1 ? $fulladress .= $adress_1			.$sep : '';

		if ($zip) {
			$fulladress .= $zip;
			$city ? $fulladress .= $zip_sep_city : $fulladress .= $sep;
		}

		$city 		? $fulladress .= $city				.$sep : '';
		$state 		? $fulladress .= $state				.$sep : '';
		$country 	? $fulladress .= $country			.$sep : '';
		$adress_2 	? $fulladress .= '('.$adress_2.')'	.$sep : '';

		return trim($fulladress);
	}



	public function getFullTitle( $sep = ' - ' )
	{
		if ($this->isInvalidUserID()) {
			return false;
		}

		$fulltitle = '';

		$title 		= $this->user_details['title'	];
		$company 	= $this->user_details['company'	];

		if ($title XOR $company) {
			$fulltitle = $title.$company;
		}
		elseif ($title && $company) {
			$fulltitle = "$title{$sep}$company";
		}

		return trim($fulltitle);
	}



	public function getAccessLevelAlias()
	{
		global $db;
		$comment = $db->select('user_status, comment, where: id='.$this->user_details['access_level']);
		return $comment[0]['comment'];
	}



	/*private function cleanString( $string ) # TODO - à virer car inutile apparemment...
	{
		return trim(ereg_replace( '([[:space:]]+)', ' ', ereg_replace("([[:blank:]]+|(\n)+|(\r)+)", ' ', $string)));
	}*/



	private function formatString( $string, $option = '' )
	{
		switch($option)
		{
			case 'upper':
				$string = mb_strtoupper($string);
				break;

			case 'lower':
				$string = mb_strtolower($string);
				break;

			case 'upper-first':
				$string = ucfirst(mb_strtolower($string));
				break;

			case 'upper-words':
				#$string = ucwords(mb_strtolower($string));	# Basic version
				$string = upperCaseWords($string);			# Customized version
				break;
		}
		return $string;
	}

}



/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

## TODO - Upgrade : rajouter des méthodes pour un moteur de recherche des users (complexe car la db peur être cryptée ...)

/**
 * Manage 'user' and 'user_info' tables
 *
 * Notice: 'user_info' table can be encrypted for security reason.
 * Then, this class allow to manage (read and write) this table transparently.
 */

class comUser_db
{
	private	$crypt_user_info;

	private	$field_user 		= array(),
			$field_user_info 	= array();

	public	$debug;



	public function __construct( $debug = false )
	{
		// Debug mode
		$debug ? $this->debug = true : $this->debug = false;

		global $db;

		// Encryption status
		$user_config = $db->selectOne("user_config, crypt_user_info");
		$user_config['crypt_user_info'] ? $this->crypt_user_info = true : $this->crypt_user_info = false;

		// All 'user' table fields (except 'id')
		$fields_details = $db->db_describe('user');
		foreach($fields_details as $field => $type) {
			if ($field == 'id') {
				continue;
			}
			$this->field_user[$type][$field] = NULL; # initAllFields
		}

		// All 'user_info' table fields (except 'user_id')
		$fields_details = $db->db_describe('user_info');
		foreach($fields_details as $field => $type) {
			if ($field == 'user_id') {
				continue;
			}
			$this->field_user_info[$type][$field] = NULL; # initAllFields
		}

		if ($this->debug)
		{
			$user_key_int 			= implode('<br />', array_keys($this->field_user		['int'		]));
			$user_key_string 		= implode('<br />', array_keys($this->field_user		['char'		]));
			echo "<p><b>&gt; 'user' table:</b><br />\n<u>List of 'int' fields (except id):</u><br />\n$user_key_int<br />\n<u>List of 'char' fields:</u>\n<br />$user_key_string</p>\n";

			$user_info_key_int 		= implode('<br />', array_keys($this->field_user_info	['int'		]));
			$user_info_key_string 	= implode('<br />', array_keys($this->field_user_info	['char'		]));
			echo "<p><b>&gt; 'user_info' table:</b><br />\n<u>List of 'int' fields (except user_id):</u><br />\n$user_info_key_int<br />\n<u>List of 'char' fields:</u><br />\n$user_info_key_string</p>\n";
		}
	}



	public function getUserStringfields()
	{
		return array_keys($this->field_user['char']);
	}



	public function getUserInfoStringfields()
	{
		return array_keys($this->field_user_info['char']);
	}



	public function initAllFields()
	{
		reset($this->field_user);
		foreach($this->field_user['int'] as $key => $value)
		{
			$this->field_user['int'][$key] = NULL;
		}
		foreach($this->field_user['char'] as $key => $value)
		{
			$this->field_user['char'][$key] = NULL;
		}

		reset($this->field_user_info);
		foreach($this->field_user_info['int'] as $key => $value)
		{
			$this->field_user_info['int'][$key] = NULL;
		}
		foreach($this->field_user_info['char'] as $key => $value)
		{
			$this->field_user_info['char'][$key] = NULL;
		}
	}



	public function selectUser( $user_id )
	{
		$db = new databaseManager($this->debug);

		// All user infos
		$fields_user      = implode(',', array_keys($db->db_describe('user'		)));
		$fields_user_info = implode(',', array_keys($db->db_describe('user_info')));

		$user_details = $db->selectOne("user, $fields_user, where: id=$user_id, join: id>; user_info, $fields_user_info, join: <user_id");

		if (!$user_details) {
			return false;
		}

		// Decrypt 'user_info' table
		$this->decryptUserInfo($user_details);

		return $user_details;
	}



	public function updateUser( $user_details )
	{
		if (!$user_details) {
			return false;
		}

		// Check user_id
		$user_id = $this->getUserID($user_details);

		if (!$user_id) {
			return false;
		}

		// Encrypt 'user_info' table
		$this->encryptUserInfo($user_details);

		// Split $user_details parameter into $this->field_user and $this->field_user_info properties
		$this->splitUserDetails($user_details);

		$query_user 		= $this->queryUpdate();
		$query_user_info 	= $this->queryUpdate(1);

		$db = new databaseManager($this->debug);

		// Update 'user' table
		$result_user = true;
		if ($query_user)
		{
			$result_user = $db->update("user; $query_user; where: id=$user_id");
		}

		// Update 'user_info' table
		$result_user_info = true;
		if ($query_user_info)
		{
			$result_user_info = $db->update("user_info; $query_user_info; where: user_id=$user_id");
		}

		return ($result_user && $result_user_info);
	}



	public function insertUser( $user_details )
	{
		if (!$user_details) {
			return false;
		}

		// Check username & password
		if (!isset($user_details['username']) || !isset($user_details['password'])) {
			return false;
		}

		// Encrypt 'user_info' table
		$this->encryptUserInfo($user_details);

		// Split $user_details parameter into $this->field_user and $this->field_user_info properties
		$this->splitUserDetails($user_details);

		$query_user 		= $this->queryInsert();
		$query_user_info 	= $this->queryInsert(1);

		$db = new databaseManager($this->debug);

		// Insert 'user' table
		$result = $db->insert("user; col: {$query_user['col']}; {$query_user['val']}");

		if ($result)
		{
			// user_id
			$user_id = $db->insertID();

			$query_user_info_col = $this->mergeQuerys('user_id', $query_user_info['col']);
			$query_user_info_val = $this->mergeQuerys($user_id, $query_user_info['val']);

			// Insert 'user_info' table
			$result = $db->insert("user_info; col: $query_user_info_col; $query_user_info_val");

			// When user successfully created, return his 'id' instead of a simple true
			if ($result) {
				return $user_id;
			}
		}

		return false;
	}



	public function deleteUser( $user_id, $param = '' ) # TODO: Pouvoir détruire un utilisateur sous certaines conditions...
	{
		$db = new databaseManager($this->debug);

		if ($this->selectUser($user_id))
		{
			$result_user 		= $db->delete("user; where: id=$user_id");
			$result_user_info 	= $db->delete("user_info; where: user_id=$user_id");

			// Potential record (if exist)
			$db->delete("user_forget; where: user_id=$user_id");

			return ($result_user && $result_user_info);
		}
		else {
			return false; # Can't delete a user wich doesn't exist !
		}
	}



	private function decryptUserInfo( &$user_details ) # passed by reference
	{
		if ($this->crypt_user_info)
		{
			$crypt = new cryptManager();

			reset($user_details);
			foreach($user_details as $key => $value)
			{
				if (array_key_exists($key, $this->field_user_info['char']))
				{
					$user_details[$key] = $crypt->decrypt($value);
				}
			}

			$crypt->close();
		}
	}



	private function encryptUserInfo( &$user_details ) # passed by reference
	{
		if ($this->crypt_user_info)
		{
			$crypt = new cryptManager();

			reset($user_details);
			foreach($user_details as $key => $value)
			{
				if (array_key_exists($key, $this->field_user_info['char']))
				{
					$user_details[$key] = $crypt->encrypt($value);
				}
			}

			$crypt->close();
		}
	}



	public function getUserID( $user_details )
	{
		$user_id = NULL;

		$error_message = 'Invalid $user_details parameter: the user ID can not be determined.';
	
		if (isset($user_details['id']) && formManager_filter::isInteger($user_details['id']))
		{
			$user_id = $user_details['id'];
		}

		if (isset($user_details['user_id']) && formManager_filter::isInteger($user_details['user_id']))
		{
			if (!isset($user_id))
			{
				$user_id = $user_details['user_id'];
			}
			elseif ($user_id != $user_details['user_id'])
			{
				trigger_error($error_message, E_USER_WARNING);
				return false;
			}
		}

		if (!isset($user_id))
		{
			trigger_error($error_message, E_USER_WARNING);
			return false;
		}

		return $user_id;
	}



	private function splitUserDetails( $user_details )
	{
		// Set NULL for all values
		$this->initAllFields();

		reset($user_details);
		foreach($user_details as $key => $value)
		{
			if (array_key_exists($key, $this->field_user['int']))
			{
				$this->field_user['int'			][$key] = $value;
			}
			elseif (array_key_exists($key, $this->field_user['char']))
			{
				$this->field_user['char'		][$key] = $value;
			}
			elseif (array_key_exists($key, $this->field_user_info['int']))
			{
				$this->field_user_info['int'	][$key] = $value;
			}
			elseif (array_key_exists($key, $this->field_user_info['char']))
			{
				$this->field_user_info['char'	][$key] = $value;
			}
		}
	}



	private function queryUpdate( $field_user_info = false )
	{
		if (!$field_user_info)
		{
			$f = $this->field_user;
		} else {
			$f = $this->field_user_info;
		}

		$query = '';

		global $db;

		reset($f);

		foreach($f['int'] as $key => $value)
		{
			if (isset($value)) {
				$query .= " $key=$value,";
			}
		}

		foreach($f['char'] as $key => $value)
		{
			if (isset($value)) {
				$query .= " $key=".$db->str_encode($value).',';
			}
		}

		$query = preg_replace('~,$~', ' ', $query);

		return $query;
	}



	private function queryInsert( $field_user_info = false )
	{
		if (!$field_user_info)
		{
			$f = $this->field_user;
		} else {
			$f = $this->field_user_info;
		}

		$query_col = '';
		$query_val = '';

		global $db;

		reset($f);

		foreach($f['int'] as $key => $value)
		{
			if (isset($value)) {
				$query_col .= " $key,";
				$query_val .= " $value,";
			}
		}

		foreach($f['char'] as $key => $value)
		{
			if (isset($value)) {
				$query_col .= "$key,";
				$query_val .= $db->str_encode($value).',';
			}
		}

		$query_col = preg_replace('~,$~', ' ', $query_col);
		$query_val = preg_replace('~,$~', ' ', $query_val);

		return
			array(
				'col' => $query_col,
				'val' => $query_val
			);
	}



	private function mergeQuerys( $query1, $query2 )
	{
		$query_merge = $query1;

		$query1 && $query2 ? $query_merge .= ',' : '';

		$query_merge .= $query2;

		return $query_merge;
	}
}



/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

/**
 * Class ( Find a basic code example at: http://www.php.net/manual/en/function.mcrypt-module-open.php )
 */
class cryptManager
{
	private	$td,
			$key,
			$iv;

			/**
			 * Algorithms performances order
			 *
			 *		- MCRYPT_BLOWFISH
			 *		- MCRYPT_RIJNDAEL_128
			 *		- MCRYPT_3DES
			 *		- MCRYPT_RIJNDAEL_256
			 *		- MCRYPT_RIJNDAEL_192 (strangely, _256 seems to be faster than _192)
			 */
	const	DEFAULT_ALGORITHM	= MCRYPT_RIJNDAEL_128, # Find more algorithms at: http://us.php.net/manual/fr/mcrypt.ciphers.php
			DEFAULT_MODE		= MCRYPT_MODE_OFB;

	private $random_iv; # New random iv generated for each encryption and added in the encrypted string

	const	FIXED_IV	= 'Life need a vector !';

	const	DEFAULT_KEY	= 'Keep a secret deep inside !'; # The secret Key!

	public	$debug;
	const	DEBUG_MSG_MAX_OUTPUT = 70; # Set 0 to disable



	public function __construct( $key = '', $random_iv = true, $algorithm = false, $debug = false )
	{
		// Debug mode
		$debug ? $this->debug = true : $this->debug = false;

		// Random IV ?
		$random_iv ? $this->random_iv = true : $this->random_iv = false;

		// Time start
		$this->debug ? $time_start = microtime(true) : '';

		// Open the cipher
		if (!$algorithm) {
			$algorithm = self::DEFAULT_ALGORITHM;
		}
		$this->td = mcrypt_module_open($algorithm, '', self::DEFAULT_MODE, '');

		// Create key
		if (!$key) {
			$key = self::DEFAULT_KEY;

			$key_msg = ' [default]';
		} else {
			$key_msg = '';
		}
		$this->key = substr(md5($key), 0, mcrypt_enc_get_key_size($this->td));

		// Debug
		if ($this->debug)
		{
			$this->debugMessage('ALGORITHM', $algorithm, 'blue', false);
			$this->debugMessage('BLOCK SIZE', mcrypt_enc_get_block_size($this->td), 'blue', false);

			$this->debugMessage('KEY'.$key_msg, $this->key, 'blue');
		}

		if (!$this->random_iv)
		{
			// Create fixed IV
			$this->iv = substr(md5(self::FIXED_IV), 0, mcrypt_enc_get_iv_size($this->td));

			// Debug
			if ($this->debug)
			{
				$this->debugMessage('IV [fixed]', $this->iv, 'blue');

				$this->debugMessage('TIME (open module)', $this->time($time_start, microtime(true)), 'black', false);
			}
		}
	}



	private function init( $iv = false )
	{
		if (!$iv)
		{
			// Create random IV for each encryption
			if ($this->random_iv)
			{
				// Unix has better pseudo random number generator then mcrypt, so if it is available lets use it! 
				if (strstr(PHP_OS, "WIN"))
				{
					srand(); # Seed the random number generator
					$this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_RAND);				
				} else {
					$this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_DEV_URANDOM); # Important: we are using MCRYPT_DEV_URANDOM (instead of MCRYPT_DEV_RANDOM which is producing very slow and unpredictable performances)
				}

				// Debug
				if ($this->debug)
				{
					$this->debugMessage('IV&nbsp;', $this->iv, 'blue');
				}
			}

			$iv = $this->iv;
		}

		mcrypt_generic_init($this->td, $this->key, $iv);
	}



	private function deinit()
	{
		mcrypt_generic_deinit($this->td);
	}



	public function encrypt( $string )
	{
		if (!$string) {
			return '';
		}

		// Time start
		$this->debug ? $time_start = microtime(true) : '';

		$this->init();
		$encrypted = mcrypt_generic($this->td, $string);
		$this->deinit();

		if ($this->random_iv)
		{
			// Add IV to the encrypted string
			$encrypted = $this->iv.$encrypted;
		}

		// Encode
		$encrypted = base64_encode($encrypted);
	
		// Debug
		if ($this->debug)
		{
			$this->debugMessage('SOURCE&nbsp;&nbsp;&nbsp;', $string, 'red');
			$this->debugMessage('ENCRYPTED', $encrypted);

			$this->debugMessage('TIME (encryption)', $this->time($time_start, microtime(true)), 'black', false);
		}

		return $encrypted;
	}



	public function decrypt( $string, $original = NULL )
	{
		if (!$string) {
			return '';
		}

		// Time start
		$this->debug || isset($original) ? $time_start = microtime(true) : '';

		// Decode
		$string = base64_decode($string);

		if ($this->random_iv)
		{
			// Get IV from encrypted string
			$iv 	= substr($string, 0, mcrypt_enc_get_iv_size($this->td));
			$string = substr($string, mcrypt_enc_get_iv_size($this->td));

			// Intialize encryption module for decryption using specified IV
			$this->init($iv);
		} else {
			// Intialize encryption module for decryption using fixed IV
			$this->init();
		}

		$decrypted = mdecrypt_generic($this->td, $string);
		$this->deinit();

		// Debug
		if ($this->debug || isset($original))
		{
			if (isset($original))
			{
				$decrypted === $original ? $result = ' [success]' : $result = ' <span style="color:red;">[failed]</span>';
			} else {
				$result = '';
			}

			$this->debugMessage('DECRYPTED'.$result, $decrypted, 'green');

			$this->debugMessage('TIME (decryption)', $this->time($time_start, microtime(true)), 'black', false);
		}

		return trim($decrypted);
	}



	public function close()
	{
		mcrypt_module_close($this->td);
	}



	protected function debugMessage( $label, $value, $color = 'grey', $strlen = true )
	{
		$html = "\n<div style=\"color:$color;font:normal 12px/24px Monospace;\">";

		if ($strlen) {
			$html .= '('.mb_strlen($value).') ';
		}

		if ( (self::DEBUG_MSG_MAX_OUTPUT) && (mb_strlen($value) > self::DEBUG_MSG_MAX_OUTPUT))
		{
			$value = mb_substr($value, 0, self::DEBUG_MSG_MAX_OUTPUT).' ...';
		}

		$html .= "$label = $value";

		$html .= "</div>\n";

		echo $html;
	}



	static function time($time_start, $time_end)
	{
		return sprintf('%.4f', ($time_end - $time_start)).' seconds';
	}

}



/**
 * Unit test
 */
function cryptManager_unitTest()
{

	function cryptManager_unitTest_oneShot( $string = array(), $key = '', $random_iv = false, $algorithm = false )
	{
		// Encrypt
		$encrypted = array();
		$time_start = microtime(true);

		$crypt = new cryptManager($key, $random_iv, $algorithm, true);
		for ($i=0; $i<count($string); $i++)
		{
			$encrypted[$i] = $crypt->encrypt($string[$i]);
		}
		$crypt->close();

		echo '<h3 style="background-color:yellow;">&nbsp;TOTAL TIME ENCRYPTION: '.cryptManager::time($time_start, microtime(true)).'&nbsp;</h3><br />';

		// Decrypt
		$time_start = microtime(true);
		$crypt = new cryptManager($key, $random_iv, $algorithm, true);

		for ($i=0; $i<count($string); $i++)
		{
			$crypt->decrypt($encrypted[$i], $string[$i]);
		}
		$crypt->close();

		echo '<h3 style="background-color:yellow;">&nbsp;TOTAL TIME DECRYPTION: '.cryptManager::time($time_start, microtime(true)).'&nbsp;</h3>';
	}


	// Data sample (3191 characters)
	$data_sample = 
'Orci convallis et dictumst ac auctor Aenean justo dui Curabitur Curabitur. Vestibulum orci netus gravida nibh quis laoreet Sed purus eu vel. Risus nisl tincidunt tincidunt Pellentesque ac pede elit velit nulla et. Et fringilla fermentum consectetuer tincidunt sed velit auctor pede suscipit tellus. Molestie congue Suspendisse Ut semper nec dui Lorem at Donec.
Curabitur metus sit a tellus Sed vel vitae pede tortor quis. Mauris id pulvinar montes Curabitur Donec malesuada id turpis hendrerit mauris. Aenean Sed ante turpis massa ante cursus quam interdum Pellentesque dolor. Sed sagittis vel Curabitur gravida orci elit a volutpat metus Nulla. Dui dolor morbi odio quam Morbi molestie adipiscing id eget vestibulum. Curabitur Duis sit Nam et tortor platea lacus ac.
Elit at sem hendrerit laoreet laoreet elit orci risus at nulla. Adipiscing Suspendisse condimentum venenatis Integer Duis ipsum netus Morbi accumsan non. Turpis Lorem ut id aliquam montes ut Pellentesque Vestibulum ut et. Aliquet Nulla pellentesque lacinia eros amet et faucibus vel semper tortor. Dolor sollicitudin auctor turpis a id auctor Nam nulla.
Volutpat a nunc Aliquam ante tincidunt velit aliquet arcu id wisi. Id Quisque condimentum cursus hac consectetuer leo et eros vel ut. Ullamcorper risus vitae at dolor tellus consectetuer nec non urna quam. Odio interdum semper Aenean Nam dui nec elit dictum laoreet eget. Cursus Nam risus Nulla adipiscing nunc Curabitur nec Morbi Quisque Cras. Adipiscing justo urna velit ipsum.
Nunc pretium Aenean eros pellentesque elit augue mauris morbi eget vitae. Cursus pretium Aliquam ut urna felis Curabitur dignissim elit nibh id. Nulla ligula condimentum pellentesque neque Pellentesque at tempor Pellentesque consequat tincidunt. Euismod eu purus senectus vitae eros Lorem Nullam et lacinia lacinia. Turpis sed pede tortor Donec felis Nam consequat Donec id sem. Semper non nibh velit ante nibh dis condimentum non dui et. Semper enim.
Orci congue Nam id et porttitor sociis Morbi Vestibulum eu velit. Quisque ipsum accumsan malesuada platea sit Suspendisse pretium risus tempor wisi. Iaculis wisi Pellentesque id Phasellus sociis laoreet lorem lorem hendrerit auctor. Sollicitudin fringilla nibh Donec Aenean id fringilla Nulla sapien dui neque. Ante Aliquam enim non Vestibulum interdum eros dapibus justo Nam urna. Ante odio Vivamus nulla nibh sagittis ridiculus ridiculus est ut mauris.
Enim Nulla dictumst wisi ornare eu Ut Aliquam eu faucibus malesuada. Odio Integer eu accumsan Cum orci Curabitur fringilla porttitor Morbi vitae. Sed Vestibulum Vestibulum risus justo semper pharetra Phasellus Suspendisse sed eros. Vivamus ornare leo ut semper quis metus sem lacus egestas lorem. Laoreet et Nulla tellus leo eleifend tempor fringilla id ut neque. Suspendisse Nam Curabitur accumsan eget.
Est nibh semper tristique Sed accumsan tempus lacinia ipsum a tempus. Pretium orci feugiat ipsum nibh nibh lacinia at turpis adipiscing purus. Molestie Nam ligula turpis magna consequat wisi mattis justo In ante. Dictum elit Vivamus est tellus eros a Donec consequat adipiscing Ut. Elit id sollicitudin gravida tellus auctor egestas Integer congue Nam nunc. Quis Sed fringilla.';

	// Strings list
	$string = array('&é"\'(-è_çà)=~#{[|`\^@]}$£¤*µù%<>,?;.:/!§+²'); # special characters

	for ($i=1; $i<15; $i++) {
		$string[] = mb_substr($data_sample, 0, ($i*$i*$i));
	}


	/////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////
	echo "<hr /><hr /><h1>Class cryptManager : Basic Unit Test</h1>";

	//
	echo "<br /><hr /><h2>Default Key, Fixed IV</h2>";
	$key = '';
	$random_iv = false;
	cryptManager_unitTest_oneShot($string, $key, $random_iv);

	//
	echo "<br /><hr /><h2>User Key, Fixed IV</h2>";
	$key = 'My secret key';
	$random_iv = false;
	cryptManager_unitTest_oneShot($string, $key, $random_iv);

	//
	echo "<br /><hr /><h2>Default Key, Random IV</h2>";
	$key = '';
	$random_iv = true;
	cryptManager_unitTest_oneShot($string, $key, $random_iv);

	//
	echo "<br /><hr /><h2>User Key, Random IV</h2>";
	$key = 'My secret key';
	$random_iv = true;
	cryptManager_unitTest_oneShot($string, $key, $random_iv);




	/////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////
	echo "<br /><br /><hr /><hr /><h1>Class cryptManager : Algorithms Unit Test</h1>";

	//
	echo "<br /><hr />";
	$algorithm = MCRYPT_BLOWFISH;
	cryptManager_unitTest_oneShot($string, 'My secret key', true, $algorithm);

	//
	echo "<br /><hr />";
	$algorithm = MCRYPT_RIJNDAEL_128;
	cryptManager_unitTest_oneShot($string, 'My secret key', true, $algorithm);

	//
	echo "<br /><hr />";
	$algorithm = MCRYPT_3DES;
	cryptManager_unitTest_oneShot($string, 'My secret key', true, $algorithm);

	//
	echo "<br /><hr />";
	$algorithm = MCRYPT_RIJNDAEL_256;
	cryptManager_unitTest_oneShot($string, 'My secret key', true, $algorithm);

	//
	echo "<br /><hr />";
	$algorithm = MCRYPT_RIJNDAEL_192;
	cryptManager_unitTest_oneShot($string, 'My secret key', true, $algorithm);

}

?>