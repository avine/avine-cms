<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define('LANG_INSTALL_PAGE_TITLE'					, 'Installation');
define('LANG_INSTALL_COPYRIGHT'						, '&copy; <a href="http://avine.io/">avine.io</a>');



// manage_config.php
define('LANG_INSTALL_FIELDSET_CONFIG'				, 'Fichier de configuration');

define('LANG_INSTALL_DB_HOST'						, "Nom de l'hôte");
define('LANG_INSTALL_DB_USER'						, "Nom d'utilisateur");
define('LANG_INSTALL_DB_PASS'						, "Mot de passe");
define('LANG_INSTALL_DB_NAME'						, "Nom de la base");
define('LANG_INSTALL_DB_TABLE_PREFIX'				, "Préfixe des tables");
define('LANG_INSTALL_WEBSITE_PATH'					, "Répertoire du site");
define('LANG_INSTALL_TIME_ZONE'						, "Fuseau horaire");

define('LANG_INSTALL_CONFIG_BUTTON_CREATE'			, 'Enregistrer le fichier de configuration');
define('LANG_INSTALL_CONFIG_BUTTON_MODIFY'			, 'Modifier');

define('LANG_INSTALL_CONFIG_ERROR_TITLE'			, "Informations incorrectes");
define('LANG_INSTALL_CONFIG_ERROR_WEBSITE_PATH'		, "Répertoire du site introuvable : ");
define('LANG_INSTALL_CONFIG_ERROR_DB_CONNECTION'	, "La connexion à la base de données a échoué.");

define('LANG_INSTALL_CONFIG_FAILED'					, "L'opération a échoué.");



// manage_db.php
define('LANG_INSTALL_FIELDSET_DATABASE'				, 'Base de données');

define('LANG_INSTALL_DATABASE_FILES_PATH'			, 'Répertoire des fichiers d\'installation : ');

define('LANG_INSTALL_DATABASE_FILE_NAME'			, 'Fichier');
define('LANG_INSTALL_DATABASE_ASSOCIATED_TABLES'	, 'Tables');

define('LANG_INSTALL_DATABASE_BUTTON_INSTALL'		, 'Installer les tables');
define('LANG_INSTALL_DATABASE_BUTTON_UNINSTALL'		, 'Désinstaller les tables');

define('LANG_INSTALL_DATABASE_PROCESS'				, 'Opération effectuée&nbsp;: ');
define('LANG_INSTALL_DATABASE_PROCESS_INSTALL'		, 'Installation');
define('LANG_INSTALL_DATABASE_PROCESS_UNINSTALL'	, 'Désinstallation');

define('LANG_INSTALL_DATABASE_ERROR_VALUES'			, '(Echec à l\'insertion des données)');

define('LANG_INSTALL_DATABASE_CHECKED_STATUS'		, 'Modifier toutes les cases à cocher');



// manage_default.php
define('LANG_INSTALL_FIELDSET_DEFAULT'				, 'Personnalisation');

define('LANG_INSTALL_DEFAULT_ADMIN'					, 'Compte administrateur');
define('LANG_INSTALL_DEFAULT_ADMIN_USERNAME'		, 'Nom d\'utilisateur');
define('LANG_INSTALL_DEFAULT_ADMIN_PASSWORD'		, 'Mot de passe');
define('LANG_INSTALL_DEFAULT_ADMIN_EMAIL'			, 'Email');

define('LANG_INSTALL_DEFAULT_SITE'					, 'Informations générales');
define('LANG_INSTALL_DEFAULT_SITE_NAME'				, 'Nom du site');
define('LANG_INSTALL_DEFAULT_SYSTEM_EMAIL'			, 'Email du système');

define('LANG_INSTALL_DEFAULT_BUTTON_SUBMIT'			, 'Enregistrer');
define('LANG_INSTALL_DEFAULT_BUTTON_MODIFY'			, 'Modifier');



define('LANG_INSTALL_NAVIG_DISSATISFID'				, 'Pas satisfait ? ');
define('LANG_INSTALL_NAVIG_INSTALLATION'			, 'Reprendre l\'installation du système');
define('LANG_INSTALL_NAVIG_ADMINISTRATOR'			, 'Se connecter à l\'administration du système');

define('LANG_INSTALL_NAVIG_TIPS'					,
"<p style=\"font-size:120%;\"><b>L'installation est presque terminée !</b></p>
<ol>
	<li>Par sécurité, supprimez le répertoire <b>'/installation'</b> du serveur FTP.</li>
	<li>Dans l'administration visitez les onglets suivants :<br />
		- <b>'Contenus statiques'</b><br />
		- <b>'Modules &gt; Liste'</b><br />
		Le système va scanner les éléments correspondants disponibles sur le serveur FTP.</li>
</ol>");

/*define('LANG_INSTALL_NAVIG_TIPS'					,
"<p style=\"font-size:120%;\"><b>L'installation est presque terminée !</b></p>
<p>(1) Effacez le répertoire <b>'/installation'</b> du serveur FTP.</p>
<p>(2) Dans l'Administration visitez les onglets suivants :<br />
  - <b>'Contenus statiques'</b><br />
  - <b>'Modules &gt; Liste'</b><br />
  Le système va scanner les éléments correspondants disponibles sur le serveur FTP.</p>");*/

/*define('LANG_INSTALL_NAVIG_TIPS'					,
"<p style=\"font-size:120%;\"><b>L'installation est presque terminée !</b></p>
<p>(1) Effacez le répertoire <b>'/installation'</b> du serveur FTP.</p>
<p>(2) Dans l'administration visitez les onglets suivants, afin de permettre au système de s'initialiser&nbsp;:<br />
  -&nbsp;<b>'Modèle' :</b> liste les modèles disponibles<br />
  -&nbsp;<b>'Contenus statiques' :</b> détecte automatiquement les contenus disponibles sur le serveur</p>");*/



/////////////
// Functions

function timeZoneSelection()
{
	$time_zone[0] =
		'Europe/Amsterdam, Europe/Andorra, Europe/Athens, Europe/Belfast, Europe/Belgrade, '.
		'Europe/Berlin, Europe/Bratislava, Europe/Brussels, Europe/Bucharest, Europe/Budapest, '.
		'Europe/Chisinau, Europe/Copenhagen, Europe/Dublin, Europe/Gibraltar, Europe/Guernsey, '.
		'Europe/Helsinki, Europe/Isle_of_Man, Europe/Istanbul, Europe/Jersey, Europe/Kaliningrad, '.
		'Europe/Kiev, Europe/Lisbon, Europe/Ljubljana, Europe/London, Europe/Luxembourg, '.
		'Europe/Madrid, Europe/Malta, Europe/Mariehamn, Europe/Minsk, Europe/Monaco, '.
		'Europe/Moscow, Europe/Nicosia, Europe/Oslo, Europe/Paris, Europe/Podgorica, '.
		'Europe/Prague, Europe/Riga, Europe/Rome, Europe/Samara, Europe/San_Marino, '.
		'Europe/Sarajevo, Europe/Simferopol, Europe/Skopje, Europe/Sofia, Europe/Stockholm, '.
		'Europe/Tallinn, Europe/Tirane, Europe/Tiraspol, Europe/Uzhgorod, Europe/Vaduz, '.
		'Europe/Vatican, Europe/Vienna, Europe/Vilnius, Europe/Volgograd, Europe/Warsaw, '.
		'Europe/Zagreb, Europe/Zaporozhye, Europe/Zurich';

	$time_zone[1] =
		'America/Adak, America/Anchorage, America/Anguilla, America/Antigua, America/Araguaina, '.
		'America/Argentina/Buenos_Aires, America/Argentina/Catamarca, America/Argentina/ComodRivadavia, America/Argentina/Cordoba, America/Argentina/Jujuy, '.
		'America/Argentina/La_Rioja, America/Argentina/Mendoza, America/Argentina/Rio_Gallegos, America/Argentina/Salta, America/Argentina/San_Juan, '.
		'America/Argentina/San_Luis, America/Argentina/Tucuman, America/Argentina/Ushuaia, America/Aruba, America/Asuncion, '.
		'America/Atikokan, America/Atka, America/Bahia, America/Barbados, America/Belem, '.
		'America/Belize, America/Blanc-Sablon, America/Boa_Vista, America/Bogota, America/Boise, '.
		'America/Buenos_Aires, America/Cambridge_Bay, America/Campo_Grande, America/Cancun, America/Caracas, '.
		'America/Catamarca, America/Cayenne, America/Cayman, America/Chicago, America/Chihuahua, '.
		'America/Coral_Harbour, America/Cordoba, America/Costa_Rica, America/Cuiaba, America/Curacao, '.
		'America/Danmarkshavn, America/Dawson, America/Dawson_Creek, America/Denver, America/Detroit, '.
		'America/Dominica, America/Edmonton, America/Eirunepe, America/El_Salvador, America/Ensenada, '.
		'America/Fort_Wayne, America/Fortaleza, America/Glace_Bay, America/Godthab, America/Goose_Bay, '.
		'America/Grand_Turk, America/Grenada, America/Guadeloupe, America/Guatemala, America/Guayaquil, '.
		'America/Guyana, America/Halifax, America/Havana, America/Hermosillo, America/Indiana/Indianapolis, '.
		'America/Indiana/Knox, America/Indiana/Marengo, America/Indiana/Petersburg, America/Indiana/Tell_City, America/Indiana/Vevay, '.
		'America/Indiana/Vincennes, America/Indiana/Winamac, America/Indianapolis, America/Inuvik, America/Iqaluit, '.
		'America/Jamaica, America/Jujuy, America/Juneau, America/Kentucky/Louisville, America/Kentucky/Monticello, '.
		'America/Knox_IN, America/La_Paz, America/Lima, America/Los_Angeles, America/Louisville, '.
		'America/Maceio, America/Managua, America/Manaus, America/Marigot, America/Martinique, '.
		'America/Matamoros, America/Mazatlan, America/Mendoza, America/Menominee, America/Merida, '.
		'America/Mexico_City, America/Miquelon, America/Moncton, America/Monterrey, America/Montevideo, '.
		'America/Montreal, America/Montserrat, America/Nassau, America/New_York, America/Nipigon, '.
		'America/Nome, America/Noronha, America/North_Dakota/Center, America/North_Dakota/New_Salem, America/Ojinaga, '.
		'America/Panama, America/Pangnirtung, America/Paramaribo, America/Phoenix, America/Port-au-Prince, '.
		'America/Port_of_Spain, America/Porto_Acre, America/Porto_Velho, America/Puerto_Rico, America/Rainy_River, '.
		'America/Rankin_Inlet, America/Recife, America/Regina, America/Resolute, America/Rio_Branco, '.
		'America/Rosario, America/Santa_Isabel, America/Santarem, America/Santiago, America/Santo_Domingo, '.
		'America/Sao_Paulo, America/Scoresbysund, America/Shiprock, America/St_Barthelemy, America/St_Johns, '.
		'America/St_Kitts, America/St_Lucia, America/St_Thomas, America/St_Vincent, America/Swift_Current, '.
		'America/Tegucigalpa, America/Thule, America/Thunder_Bay, America/Tijuana, America/Toronto, '.
		'America/Tortola, America/Vancouver, America/Virgin, America/Whitehorse, America/Winnipeg, '.
		'America/Yakutat, America/Yellowknife';

	$array = array();
	for ($i=0; $i<count($time_zone); $i++) {
		$array = array_merge($array, explode(', ', $time_zone[$i]));
	}
	sort($array);

	foreach($array as $v) {
		$select[$v] = $v;
	}
	return $select;
}



/////////
// Class

class db_install
{
	const INSTALL	= 'install';
	const UNINSTALL	= 'uninstall';



	public function __construct()
	{
		
	}



	public static function process( $table_suffix, $db_create, $db_insert )
	{
		if (isset($_POST[self::INSTALL])) {
			db_install::createTable($table_suffix, $db_create, $db_insert);
		}

		if (isset($_POST[self::UNINSTALL])) {
			db_install::dropTable($table_suffix);
		}
	}



	private static function createTable( $table_suffix, $db_create, $db_insert )
	{
		global $db;
		$db_result = $db->sendMysqlQuery($db_create);

		if ($db_result)
		{
			echo '<span style="color:green;">'.DB_TABLE_PREFIX.$table_suffix.'</span>';
	
			if ($db_insert != "")
			{
				$db_result = $db->sendMysqlQuery($db_insert);

				if (!$db_result) {
					echo ' <span style="color:red;">'.LANG_INSTALL_DATABASE_ERROR_VALUES.'</span>';
					db_install::dropTable($table_suffix, 0); 
				}
			}
			echo "<br />\n";
		}
		else {
			echo '<span style="color:red;">'.DB_TABLE_PREFIX.$table_suffix."</span><br />\n";
		}
	}



	private static function dropTable( $table_suffix, $view = 1 )
	{
		$db_drop = " DROP TABLE ".DB_TABLE_PREFIX."$table_suffix ";

		global $db;
		$db_result = $db->sendMysqlQuery($db_drop);

		if ($view)
		{
			if ($db_result)
			{
				echo '<span style="color:green;">'.DB_TABLE_PREFIX.$table_suffix."</span><br />\n";
			} else {
				echo '<span style="color:red;"  >'.DB_TABLE_PREFIX.$table_suffix."</span><br />\n";
			}
		}
	}

}

?>