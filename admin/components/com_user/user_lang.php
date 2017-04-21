<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


////////////
// Language

define( 'LANG_ADMIN_COM_USER_INDEX_TITLE'							, "Gestion des utilisateurs" );

define( 'LANG_ADMIN_COM_USER_CONFIG_TITLE_START'					, "Configuration des utilisateurs" );
define( 'LANG_ADMIN_COM_USER_LIST_TITLE_START'						, "Liste des utilisateurs" );
define( 'LANG_ADMIN_COM_USER_CRYPT_TITLE_START'						, "Cryptage des données sensibles des utilisateurs" );
define( 'LANG_ADMIN_COM_USER_MESSAGE_TITLE_START'					, "Envoyer un message aux utilisateurs" );



// user_field table
define( 'LANG_ADMIN_COM_USER_FIELD_FIELD'							, "Champs" );
define( 'LANG_ADMIN_COM_USER_FIELD_ACTIVATED'						, "Activé" );
define( 'LANG_ADMIN_COM_USER_FIELD_REQUIRED'						, "Obligatoire" );
define( 'LANG_ADMIN_COM_USER_FIELD_ORDER'							, "Ordre" );

// user_config table
define( 'LANG_ADMIN_COM_USER_REGISTRATION_SILENT'					, "Inscription silencieuse (partie public uniquement)" );
define( 'LANG_ADMIN_COM_USER_ALLOW_DUPLICATE_EMAIL'					, "Autoriser les Emails en doublons (partie public uniquement)" );
define( 'LANG_ADMIN_COM_USER_ACTIVATION_METHOD_SELECT'				, "Les nouveaux comptes utilisateurs sont activés" );
define( 'LANG_ADMIN_COM_USER_ACTIVATION_METHOD_AUTO'				, "automatiquement" );
define( 'LANG_ADMIN_COM_USER_ACTIVATION_METHOD_EMAIL'				, "par code d'activation" );
define( 'LANG_ADMIN_COM_USER_ACTIVATION_METHOD_ADMIN'				, "manuellement par l'administrateur" );
define( 'LANG_ADMIN_COM_USER_CRYPT_USER_INFO'						, "Crypter les données sensibles de la base de données" );
define( 'LANG_ADMIN_COM_USER_SESSION_MAXLIFETIME'					, "Temps d'inactivité avant déconnexion forcée" );
define( 'LANG_ADMIN_COM_USER_VISIT_COUNTER'							, "Compteur de visites" );

define( 'LANG_ADMIN_COM_USER_EMAIL_REQUIRED_FOR_PARAMETERS' 		, "<span style=\"color:green;\">Champs <strong>email</strong> obligatoire pour l'envoi des nom d'utilisateur et mot de passe.</span>" );
define( 'LANG_ADMIN_COM_USER_EMAIL_REQUIRED_FOR_ACTIVATION' 		, "<span style=\"color:green;\">Champs <strong>email</strong> obligatoire pour l'envoi du code d'activation.</span>" );
define( 'LANG_ADMIN_COM_USER_EMAIL_REQUIRED_FOR_INFORMATION'		, "<span style=\"color:green;\">Champs <strong>email</strong> obligatoire pour informer de l'activation effective.</span>" );



// user_session table
define( 'LANG_ADMIN_COM_USER_SESSION_SID'							, "Identifiant de session" );
define( 'LANG_ADMIN_COM_USER_SESSION_LAST_ACTIVITY'					, "Dernière activité" );
define( 'LANG_ADMIN_COM_USER_SESSION_BACKEND'						, "Admin" );
define( 'LANG_ADMIN_COM_USER_SESSION_UID'							, "Utilisateur" );

define( 'LANG_ADMIN_COM_USER_SESSION_TITLE'							, "Activité des utilisateurs authentifiés" );



// config.php
define( 'LANG_ADMIN_COM_USER_CONFIG_FIELDSET_CONFIG'				, "Paramètres généraux" );
define( 'LANG_ADMIN_COM_USER_CONFIG_FIELDSET_FIELD' 				, "Champs utilisateurs" );

define( 'LANG_ADMIN_COM_USER_CONFIG_FIELD_ALWAYS_REQUIRED' 			, "<span style=\"color:#AAA;font-weight:bold;\">OUI</span>" );
define( 'LANG_ADMIN_COM_USER_CONFIG_SUBMIT_FAILED' 					, "L'opération n'a pu être complétée avec succès !" );

define( 'LANG_ADMIN_COM_USER_CONFIG_PREVIEW' 						, "Prévisualisation<br /><span style=\"font-weight:normal;\">(<span style=\"color:red;font-weight:bold;font-size:14px;\">*</span> = Obligatoire)</span>" );

define( 'LANG_ADMIN_COM_USER_CONFIG_MINUTES' 						, "minutes" );
define( 'LANG_ADMIN_COM_USER_CONFIG_LEAVE_EMPTY_TO_DISABLE' 		, "optionnel" );

define( 'LANG_ADMIN_COM_USER_CONFIG_BUTTON_MODIFY_VISIT_COUNTER' 	, "Modifier le compte" );



// List.php
define( 'LANG_ADMIN_COM_USER_LIST_NEW' 								, "Nouveau compte" );
define( 'LANG_ADMIN_COM_USER_LIST_UPD' 								, "Mise à jour d'un compte" );
define( 'LANG_ADMIN_COM_USER_LIST_DEL' 								, "Suppression d'un compte" );
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE' 						, "Activation d'un compte utilisateur" );

define( 'LANG_ADMIN_COM_USER_LIST_FIELDSET_MAIN' 					, "Informations principales" );
define( 'LANG_ADMIN_COM_USER_LIST_FIELDSET_STATUS' 					, "Statut" );
define( 'LANG_ADMIN_COM_USER_LIST_FIELDSET_OTHER' 					, "Informations complémentaires" );

define( 'LANG_ADMIN_COM_USER_LIST_ALL_ACCESS_LEVEL' 				, "Tous" );

define( 'LANG_ADMIN_COM_USER_LIST_SELF_DEL_NOT_ALLOWED'				, "Vous ne pouvez effacer votre propre compte !" );
define( 'LANG_ADMIN_COM_USER_LIST_SELF_DEACTIVATE_NOT_ALLOWED'		, "Vous ne pouvez désactiver votre propre compte !" );
define( 'LANG_ADMIN_COM_USER_LIST_SELF_CHANGE_NOT_ALLOWED'			, "Vous ne pouvez désactiver ou changer le niveau d'accès de votre propre compte !" );

define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATION_CODE_REQUIRED' 		, "Code d'activation" ); /* title of img */
define( 'LANG_ADMIN_COM_USER_LIST_USER_ACTIVATION_CODE' 			, "Code d'activation : <strong>{activation_code}</strong>" );

define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_USERNAME'				, "Nom d'utilisateur : <strong>{username}</strong>" );
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_EMAIL'					, "Email : <strong>{email}</strong>" );
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_CODE_EXIST'				, "Code d'activation : {code_exist}" );
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_CODE_EXIST_YES'			, "<span style=\"color:red;\"><strong>OUI</strong> <br />C'est donc à l'utilisateur d'activer son compte. <br />Normalement, l'intervention de l'administrateur n'est pas nécessaire.</span>" );
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_CODE_EXIST_NO'			, "<span style=\"color:green;\"><strong>NON</strong> <br />C'est donc à l'administrateur d'activer le compte. <br />Cette opération ne pouvant être effectuée par l'utilisateur.</span>" );

define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_INFORM_USER'				, "Informer l'utilisateur par Email de l'activation de son compte" );
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_BUTTON_NORMAL'			, "Activer" );
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_BUTTON_SPECIAL'			, "Activer quand même" );

define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_SEND_MAIL_FAILED'		, "L'email d'information n'a pu être envoyé à l'utilisateur.");

/* Emails messages */
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_SEND_MAIL_SUBJECT'		, "Activation de votre compte");
define( 'LANG_ADMIN_COM_USER_LIST_ACTIVATE_SEND_MAIL',
"
<p>Bonjour <b>{username}</b>,</p>
<p>L&#39;administrateur de <b>{site_name}</b> vient d&#39;activer votre compte utilisateur.<br />Vous pouvez maintenant vous connecter gr&acirc;ce &agrave; vos nom d&#39;utilisateur et mot de passe.</p>
<p>Date d&#39;activation : <b>{activation_date}</b>.</p>
"
);
/* End of mails */



// crypt.php
define( 'LANG_ADMIN_COM_USER_CRYPT_STATUS' 							, "Statut des données : " );
define( 'LANG_ADMIN_COM_USER_CRYPT_STATUS_NOT_CRYPTED' 				, "<span style=\"color:green;font-weight:bold;\">NON cryptées</span>" );
define( 'LANG_ADMIN_COM_USER_CRYPT_STATUS_CRYPTED' 					, "<span style=\"color:red;font-weight:bold;\">Cryptées</span>" );

define( 'LANG_ADMIN_COM_USER_CRYPT_SUBMIT_TIPS' 					, "Il est fortement recommandé de sauvegarder la table <b>'user_info'</b> avant de modifier le statut de cryptage des données utilisateurs." );

define( 'LANG_ADMIN_COM_USER_CRYPT_SUBMIT_ENCRYPTION' 				, "Crypter les données maintenant" );
define( 'LANG_ADMIN_COM_USER_CRYPT_SUBMIT_DECRYPTION' 				, "Décrypter les données maintenant" );

define( 'LANG_ADMIN_COM_USER_CRYPT_ERROR_NOTHING_DONE' 				, "L'opération n'a pas été exécutée. Les données utilisateurs n'ont subies aucunes modifications." );
define( 'LANG_ADMIN_COM_USER_CRYPT_ERROR_SYSTEM_FAILURE' 			, "L'opération a échoué. L'intégrité des données utilisateurs est fortement compromise." );



// message.php
define( 'LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_FROM' 				, "Expéditeur" );
define( 'LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_FROM_TIPS' 			, " <span style=\"color:grey;\">(email du système)</span>" );
define( 'LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_SUBJECT' 			, "Objet" );
define( 'LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_MESSAGE' 			, "Message" );
define( 'LANG_ADMIN_COM_USER_MESSAGE_FORM_LABEL_TO' 				, "Destinataires" );

define( 'LANG_ADMIN_COM_USER_MESSAGE_FORM_SUBJECT_DEFAULT' 			, "Newsletter" );

define( 'LANG_ADMIN_COM_USER_MESSAGE_FORM_SUBMIT' 					, "Envoyer maintenant" );

define( 'LANG_ADMIN_COM_USER_MESSAGE_ERROR_NO_ACCESS_LEVEL_SELECTED', "Aucun groupe sélectionné." );
define( 'LANG_ADMIN_COM_USER_MESSAGE_ERROR_NO_USER_SELECTED'		, "Aucun utilisateur concerné." );
define( 'LANG_ADMIN_COM_USER_MESSAGE_SUCCESS_SEND_AUTHOR' 			, "Pour mémo, une copie du message a été envoyé à son auteur : " );
define( 'LANG_ADMIN_COM_USER_MESSAGE_SUCCESS_SEND_LIST' 			, "Liste des destinataires" );

?>