<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Schedule of Shabbat
$name_1				= 'Horaires de Shabbat';

$row_key_1			= 'Parasha';
$row_val_1			= 
	"Bereshit\nNoa\'h\nLekh lekha\nVayera\nHaye Sarah\nToledot\nVayetse\nVayishla\'h\nVayeshev\nMikets\nVayigash\nVaye\'hi\n".	# Bereshit
	"Shemot\nVa\'era\nBo\nBeshalakh\nYitro\nMishpatim\nTeroumah\nTetzave\nKi Tissa\nVayaqhel\nPeqoudei\n".						# Shemot
	"Vayikra\nTsav\nShemini\nTazria\nMetzora\nA\'harei Mot\nKedoshim\nEmor\nBehar\nBe\'houkotaï\n".								# Vayikra
	"Bamidbar\nNasso\nBeha\'alot\'kha\nShla\'h lekha\nKora\'h\nHoukat\nBalak\nPin\'has\nMatot\nMassei\n".						# Bamidbar
	"Devarim\nVa\'et\'hanan\nEikev\nRe\'eh\nShoftim\nKi Tetze\nKi Tavo\nNitzavim\nVayelekh\nHaazinou\nVèzot HaBerakha";			# Devarim

$minha_shavoua		= 'Minha de la semaine';
$minha_erev_shabbat	= 'Minha veille de Shabbat';
$hadlaka_begin		= 'Allumage des bougies (début)';
$hadlaka_end		= 'Allumage des bougies (fin)';
$shema				= 'Limite du Shema';
$minha				= 'Minha de Shabbat';
$motse				= 'Sortie de Shabbat';


// Perpetual calendar
$name_2				= 'Calendrier perpétuel';

$row_key_2			= '';
$row_val_2			= "";

$amoud				= 'Lever du jour';
$tefilin			= 'Pose des tefilin';
$nets				= 'Lever du soleil';
$chema1				= 'Limite shema (Rama)';
$chema2				= 'Limite shema (Gaon)';
$tefila1			= 'Limite tefila (Rama)';
$tefila2			= 'Limite tefila (Gaon)';
$hatsot				= 'Milieu de la journée';
$minha1				= 'Minha guedola';
$minha2				= 'Minha ketana';
$plag				= 'Plag';
$adlaka				= 'Allumage des bougies';
$chekia				= 'Coucher du soleil';
$laila				= 'Tombée de la nuit';
$motse1				= 'Sortie de Shabbat';
$motse2				= 'Sortie de Shabbat (Rabbenou Tam)';



/////////////////
// schedule_tmpl
$table_suffix = 'schedule_tmpl';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				name				VARCHAR(90),

				row_key				VARCHAR(90),
				row_val				MEDIUMTEXT,

				show_year			TINYINT(4)		NOT NULL DEFAULT 1,

				col_0				VARCHAR(100),
				col_1				VARCHAR(100),
				col_2				VARCHAR(100),
				col_3				VARCHAR(100),
				col_4				VARCHAR(100),
				col_5				VARCHAR(100),
				col_6				VARCHAR(100),
				col_7				VARCHAR(100),
				col_8				VARCHAR(100),
				col_9				VARCHAR(100),
				col_10				VARCHAR(100),
				col_11				VARCHAR(100),
				col_12				VARCHAR(100),
				col_13				VARCHAR(100),
				col_14				VARCHAR(100),
				col_15				VARCHAR(100),
				col_16				VARCHAR(100),
				col_17				VARCHAR(100),
				col_18				VARCHAR(100),
				col_19				VARCHAR(100)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

				VALUES	( 1, '$name_1', '$row_key_1', '$row_val_1', 1, '$minha_shavoua', '$minha_erev_shabbat', '$hadlaka_begin', '$hadlaka_end', '$shema', '$minha', '$motse', '', '', '', '', '', '', '', '', '', '', '', '', '' ),
						( 2, '$name_2', '$row_key_2', '$row_val_2', 0, '$amoud', '$tefilin', '$nets', '$chema1', '$chema2', '$tefila1', '$tefila2', '$hatsot', '$minha1', '$minha2', '$plag', '$adlaka', '$chekia', '$laila', '$motse1', '$motse2', '', '', '', '' ) ";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// schedule_sheet
$table_suffix = 'schedule_sheet';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				title				VARCHAR(100)	NOT NULL,

				tmpl_id				INT(11)			NOT NULL,

				sheet_order			INT(11)			NOT NULL,
				published			TINYINT(4)		NOT NULL DEFAULT 0,

				header				MEDIUMTEXT,
				footer				MEDIUMTEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////
// schedule
$table_suffix = 'schedule';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,
				sheet_id			INT(11)			NOT NULL,

				row_title			VARCHAR(100),
				time				INT(11),

				col_0				VARCHAR(4),
				col_1				VARCHAR(4),
				col_2				VARCHAR(4),
				col_3				VARCHAR(4),
				col_4				VARCHAR(4),
				col_5				VARCHAR(4),
				col_6				VARCHAR(4),
				col_7				VARCHAR(4),
				col_8				VARCHAR(4),
				col_9				VARCHAR(4),
				col_10				VARCHAR(4),
				col_11				VARCHAR(4),
				col_12				VARCHAR(4),
				col_13				VARCHAR(4),
				col_14				VARCHAR(4),
				col_15				VARCHAR(4),
				col_16				VARCHAR(4),
				col_17				VARCHAR(4),
				col_18				VARCHAR(4),
				col_19				VARCHAR(4)

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



//////////////////
// schedule_addon
$table_suffix = 'schedule_addon';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				id					INT(11)			NOT NULL AUTO_INCREMENT PRIMARY KEY,

				schedule_id			INT(11)			NOT NULL,
				col_n				SMALLINT(6)		NOT NULL,

				type				VARCHAR(25),
				content				TEXT

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "";


db_install::process($table_suffix, $db_create, $db_insert);



///////////////////
// schedule_config
$table_suffix = 'schedule_config';


$db_create = "CREATE TABLE ".DB_TABLE_PREFIX."$table_suffix
			  (
				use_sheet_menu		TINYINT(4)		NOT NULL DEFAULT 0

			  ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT = '' ";


$db_insert = "INSERT INTO ".DB_TABLE_PREFIX."$table_suffix

				VALUES	() ";


db_install::process($table_suffix, $db_create, $db_insert);



?>