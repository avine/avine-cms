<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Language
define( 'LANG_FORM_MANAGER_FILTER_ERROR_MESSAGE_TITLE'					, "Formulaire non valide ! ");

define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_FILLED'						, "Non renseigné. ");

define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_INTEGER'						, "{input} n'est pas un entier. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_SIGNED_INTEGER'				, "{input} n'est pas un entier signé. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_REAL'							, "{input} n'est pas un nombre réel. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_SIGNED_REAL'					, "{input} n'est pas un nombre réel signé. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_VAR'							, "{input} n'est pas valide comme variable. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_ID'							, "{input} n'est pas valide comme identifiant. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_MD5'							, "{input} n'est pas valide comme chaine md5. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_FILE'							, "{input} n'est pas valide comme nom de fichier. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_PATH'							, "{input} n'est pas valide comme chemin de répertoire. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_FILE_PATH'						, "{input} n'est pas valide comme chemin de fichier. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_EMAIL'							, "{input} n'est pas valide comme email. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_USERPASS'						, "{input} n'est pas valide comme nom d'utilisateur ou mot de passe. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_USERPASS_LENGTH_MIN_NOT_REACHED'	, "{input} doit avoir au moins {length_min} caractères pour être valide comme nom d'utilisateur ou mot de passe. ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_FORMATED_DATE_DDMMYYYY'		, "{input} n'est pas une date valide (JJ/MM/AAAA). ");
define( 'LANG_FORM_MANAGER_FILTER_IS_NOT_FORMATED_DATE_YYYYMMDD'		, "{input} is not a formated date (YYYY/MM/DD). ");
define( 'LANG_FORM_MANAGER_FILTER_IS_FORMATED_DATE_OUT'					, "{input} est en dehors de la période de validité supportée (de 1902 à 2038). ");
define( 'LANG_FORM_MANAGER_FILTER_IS_EMPTY'								, "Le champ ne peut rester vide. ");

define( 'LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_OK'						, "Téléchargement réussi. ");
define( 'LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_INI_SIZE'					, "Taille du fichier {input} supérieure au maximum autorisé par le serveur ({upload_max_filesize}). ");
define( 'LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_FORM_SIZE'					, "Taille du fichier {input} supérieure au maximum autorisé par le formulaire. ");
define( 'LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_PARTIAL'					, "Fichier {input} partiellement téléchargé. ");
define( 'LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_NO_FILE'					, "Aucun fichier téléchargé. ");
define( 'LANG_FORM_MANAGER_FILTER_UPLOAD_ERR_OTHER'						, "Une erreur s'est produite lors du téléchargement du fichier {input}. ");

define( 'LANG_FORM_MANAGER_FILTER_UPLOAD_MAX_FILESIZE'					, "(taille max : {upload_max_filesize}) ");


define( 'LANG_FORM_MANAGER_RANDOM_ID_NOT_MATCH'							, "<strong>L'identification du formulaire a échoué !</strong><br /> Conseils pour remplir les formulaires en toute sécurité :<br /> (1) Ne pas laisser un temps trop long entre la demande et l'envoi.<br /> (2) Ne pas cliquer sur le bouton 'Actualiser' de votre Navigateur. ");
define( 'LANG_FORM_MANAGER_HTTP_REFERER_NOT_MATCH'						, "<strong>L'identification du formulaire a échoué !!</strong><br /> Conseils pour remplir les formulaires en toute sécurité :<br /> (1) Ne pas laisser un temps trop long entre la demande et l'envoi.<br /> (2) Ne pas cliquer sur le bouton 'Actualiser' de votre Navigateur. ");


define( 'LANG_FORM_MANAGER_DELETE_CONFIRM'								, "Attention l'opération est définitive !");



##############################
# FILTERS : PREVIOUS VERSION #
##############################

define( 'LANG_FORM_MANAGER_IS_NOT_INTEGER'							, "'<b>{input}</b>' n'est pas un entier. ");
define( 'LANG_FORM_MANAGER_IS_NOT_SIGNED_INTEGER'					, "'<b>{input}</b>' n'est pas un entier signé. ");
define( 'LANG_FORM_MANAGER_IS_NOT_REAL'								, "'<b>{input}</b>' n'est pas un nombre réel. ");
define( 'LANG_FORM_MANAGER_IS_NOT_ID'								, "'<b>{input}</b>' n'est pas valide comme identifiant. ");
define( 'LANG_FORM_MANAGER_IS_NOT_LARGE_ID'							, "'<b>{input}</b>' n'est pas valide comme identifiant élargi. ");
define( 'LANG_FORM_MANAGER_IS_NOT_EXTRA_LARGE_ID'					, "'<b>{input}</b>' n'est pas valide comme identifiant extra-élargi. ");
define( 'LANG_FORM_MANAGER_IS_NOT_MD5'								, "'<b>{input}</b>' n'est pas valide comme chaine md5. ");
define( 'LANG_FORM_MANAGER_IS_NOT_FILE'								, "'<b>{input}</b>' n'est pas valide comme nom de fichier. ");
define( 'LANG_FORM_MANAGER_IS_NOT_PATH'								, "'<b>{input}</b>' n'est pas valide comme chemin de répertoire. ");
define( 'LANG_FORM_MANAGER_IS_NOT_FILE_PATH'						, "'<b>{input}</b>' n'est pas valide comme chemin de fichier. ");
define( 'LANG_FORM_MANAGER_IS_EMAIL'								, "'<b>{input}</b>' n'est pas valide comme email. ");
define( 'LANG_FORM_MANAGER_IS_USERPASS'								, "'<b>{input}</b>' n'est pas valide comme nom d'utilisateur ou mot de passe. ");
define( 'LANG_FORM_MANAGER_IS_FORMATED_DATE'						, "'<b>{input}</b>' n'est pas valide comme date (JJ/MM/AAAA). ");
define( 'LANG_FORM_MANAGER_IS_FORMATED_DATE_OUT'					, "'<b>{input}</b>' est en dehors de la période de validité supportée (de 1902 à 2038). ");
define( 'LANG_FORM_MANAGER_IS_EMPTY'								, "Le champ ne peut rester vide. ");
// End of : previous version

?>