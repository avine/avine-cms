<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



// Buttons
define( 'LANG_BUTTON_CREATE'				, "Nouveau");
define( 'LANG_BUTTON_UPDATE'				, "Mettre à jour");
define( 'LANG_BUTTON_DELETE'				, "Effacer");
define( 'LANG_BUTTON_SUBMIT'				, "Valider");
define( 'LANG_BUTTON_RECORD'				, "Enregistrer les modifications");
define( 'LANG_BUTTON_RESET' 				, "&gt; Annuler &lt;");
define( 'LANG_BUTTON_REFRESH' 				, "Rafraichir la page");



define( 'LANG_SELECT_OPTION_ROOT' 			, "Sélectionner");



if (defined('WEBSITE_PATH'))
{
	define( 'LANG_GO_TO_HOME_PAGE'			, '<a href="'.WEBSITE_PATH.'/">Aller à l\'accueil</a>');
}



define( 'LANG_DATE_FORMAT'					, "<span style=\"font:normal 10px Verdana;color:#444;\"><span style=\"color:#888;\">(</span>JJ<span style=\"color:red;font-weight:bold;\">/</span>MM<span style=\"color:red;font-weight:bold;\">/</span>AAAA<span style=\"color:#888;\">)</span></span>" );

define( 'LANG_TIME_DAYS' 					, "jours");
define( 'LANG_TIME_DAY' 					, "jour");
define( 'LANG_TIME_HOURS' 					, "heures");
define( 'LANG_TIME_HOUR' 					, "heure");
define( 'LANG_TIME_MIN' 					, "min");
define( 'LANG_TIME_SEC' 					, "sec");
define( 'LANG_TIME_LESS_THAN_1_SEC' 		, "moins d'une seconde");



define( 'LANG_ERROR_404'					, "Nous sommes désolés, mais le document que vous cherchez n'a pas pu être trouvé. <br />Cela vient peut-être du fait qu'il a été déplacé ou qu'il a été retiré du serveur. <br />");



define( 'LANG_CAPTCHA_BUTTON_PLAY' 			, "Ecouter"); # Not used
define( 'LANG_CAPTCHA_BUTTON_REFRESH' 		, "Rafraîchir");


?>