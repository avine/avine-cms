<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Language
define( 'LANG_COM_USER_ID'										, "UID" );
define( 'LANG_COM_USER_USERNAME'								, "Nom d'utilisateur" );
define( 'LANG_COM_USER_PASSWORD'								, "Mot de passe" );
define( 'LANG_COM_USER_PASSWORD_NEW'							, "Nouveau mot de passe" );
define( 'LANG_COM_USER_EMAIL'									, "Email" );
define( 'LANG_COM_USER_ACCESS_LEVEL'							, "Niveau d'accès" );
define( 'LANG_COM_USER_ACTIVATED'								, "Activé" );
define( 'LANG_COM_USER_ACTIVATED_YES'							, "Oui" );
define( 'LANG_COM_USER_ACTIVATED_NO'							, "Non" );
define( 'LANG_COM_USER_ACTIVATION_CODE'							, "Code d'activation" );
define( 'LANG_COM_USER_REGISTRATION_DATE'						, "Date d'enregistrement" );
define( 'LANG_COM_USER_LAST_VISIT'								, "Dernière visite" );

define( 'LANG_COM_USER_GENDER'									, "Sexe" );
define( 'LANG_COM_USER_GENDER_NULL'								, "Sélectionner" );
define( 'LANG_COM_USER_GENDER_0'								, "Femme" );
define( 'LANG_COM_USER_GENDER_1'								, "Homme" );
define( 'LANG_COM_USER_LAST_NAME'								, "Nom" );
define( 'LANG_COM_USER_FIRST_NAME'								, "Prénom" );
define( 'LANG_COM_USER_AGE'										, "Age" );
define( 'LANG_COM_USER_TITLE'									, "Titre" );
define( 'LANG_COM_USER_COMPANY'									, "Entreprise" );
define( 'LANG_COM_USER_ADRESS_1'								, "Adresse" );
define( 'LANG_COM_USER_ADRESS_2'								, "(suite)" );
define( 'LANG_COM_USER_CITY'									, "Ville" );
define( 'LANG_COM_USER_STATE'									, "Département" ); # Etat
define( 'LANG_COM_USER_COUNTRY'									, "Pays" );
define( 'LANG_COM_USER_ZIP'										, "Code postal" );
define( 'LANG_COM_USER_PHONE_1'									, "Téléphone" );
define( 'LANG_COM_USER_PHONE_2'									, "Mobile" );
define( 'LANG_COM_USER_FAX'										, "Fax" );
define( 'LANG_COM_USER_EXTRA_FIELD_1'							, "Champ suppl. 1" );
define( 'LANG_COM_USER_EXTRA_FIELD_2'							, "Champ suppl. 2" );
define( 'LANG_COM_USER_EXTRA_FIELD_3'							, "Champ suppl. 3" );
define( 'LANG_COM_USER_EXTRA_FIELD_4'							, "Champ suppl. 4" );
define( 'LANG_COM_USER_EXTRA_FIELD_5'							, "Champ suppl. 5" );

define( 'LANG_COM_USER_FIELD_REQUIRED'							, "Champ obligatoire" );
define( 'LANG_COM_USER_FIELD_REQUIRED_STAR'						, '<img src="'.WEBSITE_PATH.'/components/com_user/images/com-user-required.png" alt="'.LANG_COM_USER_FIELD_REQUIRED.'" title="'.LANG_COM_USER_FIELD_REQUIRED.'" />' );
define( 'LANG_COM_USER_FIELD_IF_NECESSARY'						, "<span style=\"color:grey;\"> [Si besoin] </span>" );

define( 'LANG_COM_USER_DUPLICATE_USERNAME'						, "Ce nom d'utilisateur est déjà enregistré." );
define( 'LANG_COM_USER_DUPLICATE_EMAIL'							, "Cet email est déjà enregistré. <br /><a href=\"{href}\">Obtenir mes identifiants de connexion</a>" );
define( 'LANG_COM_USER_DUPLICATE_SYSTEM_EMAIL'					, "Vous ne disposez pas des droits suffisants pour utiliser cet email." );

define( 'LANG_COM_USER_GENDER_NOT_SELECTED'						, "Champs non sélectionné." );
define( 'LANG_COM_USER_NUMERCAL_ONLY'							, "N'entrer que des caractères numériques." );

define( 'LANG_COM_USER_INVALID_LOGIN'							, "Données&nbsp;invalides&nbsp;!" );
define( 'LANG_COM_USER_NOT_ACTIVATED'							, "Compte&nbsp;non&nbsp;activé&nbsp;!" );
define( 'LANG_COM_USER_NO_ACCESS_LEVEL'							, "Droits&nbsp;insuffisants&nbsp;!" );

define( 'LANG_COM_USER_REMEMBER_ME'								, "Se souvenir de moi" );
define( 'LANG_COM_USER_BUTTON_LOGIN'							, "Se connecter" );
define( 'LANG_COM_USER_HELLO'									, "Bonjour" );
define( 'LANG_COM_USER_BUTTON_LOGOUT'							, "Se déconnecter" );

define( 'LANG_COM_USER_MODIFY_ACCOUNT_LINK'						, "Mon compte" );
define( 'LANG_COM_USER_MODIFY_ACCOUNT_LINK_TIPS'				, "Informations personnelles" );
define( 'LANG_COM_USER_LOGIN_ACCOUNT_TITLE'						, "<span>Mon compte :</span> S'identifier" );
define( 'LANG_COM_USER_MODIFY_ACCOUNT_TITLE'					, "<span>Mon compte :</span> Détails" );
define( 'LANG_COM_USER_MODIFY_ACCOUNT_FORM'						, "<span>Mon compte :</span> Mettre à jour" );
define( 'LANG_COM_USER_MODIFY_ACCOUNT_NOT_CONNECTED'			, "Vous n'êtes pas connecté." );

define( 'LANG_COM_USER_CREATE_ACCOUNT_LINK'						, "Inscription" );
define( 'LANG_COM_USER_CREATE_ACCOUNT_LINK_TIPS'				, "Créer un compte utilisateur" );
define( 'LANG_COM_USER_CREATE_ACCOUNT_TITLE'					, "<span>Mon compte :</span> Inscription" );
define( 'LANG_COM_USER_CREATE_ACCOUNT_ALREADY_CONNECTED'		, "Vous êtes déjà connecté." );

// Behaviour: one message for all missing
#define( 'LANG_COM_USER_CREATE_ACCOUNT_ERROR_REQUIRED'			, "Les champs marqués du signe {star} ne peuvent rester vides." );
// Alternative behaviour: one message for each missing
define( 'LANG_COM_USER_CREATE_ACCOUNT_ERROR_REQUIRED'			, "Champ obligatoire ({star}) non renseigné" );
define( 'LANG_COM_USER_CREATE_ACCOUNT_ERROR_USERNAME'			, "Nom d'utilisateur invalide" );
define( 'LANG_COM_USER_CREATE_ACCOUNT_ERROR_PASSWORD'			, "Mot de passe invalide" );
define( 'LANG_COM_USER_CREATE_ACCOUNT_ERROR_EMAIL'				, "Adresse Email invalide" );

define( 'LANG_COM_USER_CREATE_ACCOUNT_CREATE_SUCCESS'			, "Votre compte utilisateur a bien été créé." );
define( 'LANG_COM_USER_CREATE_ACCOUNT_CREATE_FAILED_EMAIL'		, "Nous sommes désolés, mais un problème est survenu, lors de l'envoi de l'email de confirmation d'inscription.<br />Merci de contacter le l'administrateur du site." );

define( 'LANG_COM_USER_CREATE_ACCOUNT_CREATE_AUTO'				, "Vous êtes actuellement connecté." );
define( 'LANG_COM_USER_CREATE_ACCOUNT_CREATE_EMAIL'				, "Pour finaliser votre inscription, il ne vous reste plus qu'à l'activer !<br /> Pour cela, cliquez dans le <strong>lien d'activation</strong> présent dans l'email que nous venons d'envoyer à votre adresse : <strong>{email}</strong>." );
define( 'LANG_COM_USER_CREATE_ACCOUNT_CREATE_ADMIN'				, "Vous serez prochainement informé de son activation par l'administrateur du site. <br />A cet effet, Vous <strong>recevrez un email</strong>, à l'adresse : <strong>{email}</strong>." );

define( 'LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_SUCCESS'			, "Votre compte utilisateur a bien été activé." );
#define( 'LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_SUCCESS_TIPS'	, "Utilisez dés à présent votre <strong>nom d'utilisateur</strong> et votre <strong>mot de passe</strong> pour vous <a href={login}>connecter</a>." );	# user is not logged after it's account activation
define( 'LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_SUCCESS_TIPS'	, "Vous êtes désormais inscrit sur <strong>\"{site_name}\"</strong>. Nous espérons que les services proposés vous apporterons satisfaction. Gardez précieusemment votre <b>nom d'utilisateur</b> et votre <b>mot de passe</b>, ces identifiants vous serons demandés lors de vos prochaines visites." );																													# (or) user is automatically logged after it's account activation
define( 'LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_NOT_REQUIRED'	, "Votre compte utilisateur est déjà activé." );
define( 'LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_NOT_REQUIRED_TIPS', "Utilisez dés à présent votre <strong>nom d'utilisateur</strong> et votre <strong>mot de passe</strong> pour vous <a href={login}>connecter</a>." );
define( 'LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_FAILED'			, "Nous sommes désolés, mais l'activation de votre compte utilisateur a échouée.<br /> Merci de contactez l'administrateur du site." );
define( 'LANG_COM_USER_CREATE_ACCOUNT_ACTIVATE_ERROR'			, "Votre demande ne peut être traitée." );

define( 'LANG_COM_USER_CREATE_ACCOUNT_LAW',
"<strong>\"{site_name}\"</strong> s'engage à respecter la loi <strong>\"Informatique et Libertés\"</strong> :
en application de cette loi n°78-17 du 6 janvier 1978 relative à l'informatique, aux fichiers et aux libertés,
les internautes disposent des droits d'opposition (art.26 de la loi), d'accès (art.34 à 38 de la loi), et de rectification (art.36 de la loi)
des données les concernant et figurant sur le site Internet \"{site_name}\"."
);



define( 'LANG_COM_USER_SESSION_TRAFFIC'							, "Trafic : " );

define( 'LANG_COM_USER_SESSION_COUNTER_GUEST'					, "invité{s}" );
define( 'LANG_COM_USER_SESSION_COUNTER_USER'					, "utilisateur{s} authentifié{s}" );

define( 'LANG_COM_USER_SESSION_COUNTER_GUESTS'					, "Invités : " );
define( 'LANG_COM_USER_SESSION_COUNTER_USERS'					, "Utilisateurs authentifiés : " );

define( 'LANG_COM_USER_CONFIG_VISIT_COUNTER'					, "Nombre total de visites : " );



define( 'LANG_COM_USER_CAPTCHA'									, "Code sécurité" );
define( 'LANG_COM_USER_CAPTCHA_ERROR'							, "Le code entré est erroné !" );
define( 'LANG_COM_USER_CAPTCHA_ERROR_TIPS'						, "Conseil : si le code demandé n'est pas assez lisible, utilisez le bouton \"Rafraîchir\" pour en obtenir un autre." );



/**
 * Emails
 */
define('LANG_COM_USER_CREATE_SEND_MAIL_SUBJECT', "Inscription");
define('LANG_COM_USER_CREATE_SEND_MAIL_ADMIN',
"
<p>Bonjour Administrateur de <b>{site_name}</b>,</p>
<p>Un nouvel utilisateur s&#39;est enregistr&eacute; en ligne.</p>
<p>Nom d&#39;utilisateur : <b>{username}</b><br />
M&eacute;thode d&#39;activation : <b>{activation_method}</b><br />
<i>Date d&#39;enregistrement : <b>{registration_date}</b></i></p>
"
);
define('LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER',
"
<p>Bonjour,</p>
<p>Et bienvenue sur <b>{site_name}</b>.</p>
<p>Informations concernant votre compte utilisateur :<br />
Nom d&#39;utilisateur : <b>{username}</b><br />
Mot de passe : <b>{password}</b><br />
<i>Date d&#39;enregistrement : {registration_date}</i></p>
"
);
define('LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER_AUTO',
"<p style=\"color:green;\">Vous pouvez d&egrave;s &agrave; pr&eacute;sent vous connecter gr&acirc;ce &agrave; votre nom d&#39;utilisateur et votre mot de passe.</p>"
);
define('LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER_EMAIL',
"<p style=\"color:green;\">Cliquez dans le lien suivant pour activer votre compte :<br />{activation_link}</p>"
);
define('LANG_COM_USER_CREATE_SEND_MAIL_NEW_USER_ADMIN',
"<p style=\"color:red;\">Vous serez prochainement inform&eacute; de son activation par l&#39;administrateur du site.<br />A cet effet, Vous recevrez un email, &agrave; cette adresse.</p>"
); // End of mails


define( 'LANG_COM_USER_FORGET_ACCOUNT_LINK'						, "Identifiants perdus ?" );
define( 'LANG_COM_USER_FORGET_ACCOUNT_LINK_TIPS'				, "Retrouver mon nom d'utilisateur ou mon mot de passe" );
define( 'LANG_COM_USER_FORGET_ACCOUNT_TITLE'					, "<span>Mon compte :</span> Nom d'utilisateur ou mot de passe oublié ?" );
define( 'LANG_COM_USER_FORGET_TIPS'								, "Veuillez saisir votre <strong>Nom d'utilisateur</strong>, ou votre <strong>Email</strong>. <br />Vous recevrez par Email les informations de connexion à votre compte." );
define( 'LANG_COM_USER_FORGET_ENTRY'							, "Vous vous rappelez de" );

define( 'LANG_COM_USER_FORGET_UNKNOWN_EMAIL'					, "Cet Email ne correspond à aucun compte." );
define( 'LANG_COM_USER_FORGET_UNKNOWN_USERNAME'					, "Ce nom d'utilisateur ne correspond à aucun compte." );
define( 'LANG_COM_USER_FORGET_UNKNOWN'							, "Format de l'entrée invalide." );

define( 'LANG_COM_USER_FORGET_EMAIL_UNDEFINED'					, "Opération impossible : l'email de votre compte utilisateur n'est pas défini." );

define( 'LANG_COM_USER_FORGET_CODE_REQUEST_TITLE'				, "Votre demande a bien été enregistrée !" );
define( 'LANG_COM_USER_FORGET_CODE_REQUEST_TIPS' 				, "{username}, pour répondre à votre demande, un email vous a été envoyé à l'adresse suivante : {email}. <br />Pour des raisons de sécurité, il contient un <strong>lien de confirmation</strong> de votre demande." );
define( 'LANG_COM_USER_FORGET_CODE_REQUEST_SUCCESS_TITLE'		, "Votre demande a bien été traitée !" );
define( 'LANG_COM_USER_FORGET_CODE_REQUEST_SUCCESS_TIPS' 		, "{username}, pour répondre à votre demande, un email vous a été envoyé à l'adresse suivante : {email}. <br />Il contient les <strong>nouveaux identifiants</strong> de connexion à votre compte." );

define( 'LANG_COM_USER_FORGET_ERROR_OCCRURED'					, "Nous sommes désolés, mais votre demande n'a pu être traitée." );

define( 'LANG_COM_USER_FORGET_REQUEST_ERROR'					, "<span style=\"color:red;\">Votre demande ne peut être traitée.</span>" );


/**
 * Emails
 */
define('LANG_COM_USER_FORGET_SEND_MAIL_REQUEST_SUBJECT', "Demande de confirmation");
define( 'LANG_COM_USER_FORGET_SEND_MAIL_REQUEST',
"
<p>Bonjour <b>{username}</b>,</p>
<p>Vous avez oubli&eacute; votre nom d&#39;utilisateur ou votre mot de passe sur <b>{site_name}</b>,<br />
et vous nous avez demand&eacute; de vous fournir ces informations en date du <b>{request_date}</b>.</p>
<p>Si vous &ecirc;tes bien l&#39;auteur de cette demande, cliquez dans le lien suivant :<br />
{request_link}</p>
<p>Sinon, vous pouvez ignorer ce message.</p>
"
);
define('LANG_COM_USER_FORGET_SEND_MAIL_NEW_PASSWORD_SUBJECT', "Nouveau mot de passe");
define( 'LANG_COM_USER_FORGET_SEND_MAIL_NEW_PASSWORD',
"
<p>Bonjour <b>{username}</b>,</p>
<p>Votre compte utilisateur sur <b>{site_name}</b> a &eacute;t&eacute; modifi&eacute;.</p>
<p>Vous aviez oubli&eacute; votre nom d&#39;utilisateur ou votre mot de passe ?</p>
<p>Voici les nouveaux identifiants de connexion votre compte :<br />
Nom d&#39;utilisateur : <b>{username}</b><br />
Nouveau mot de passe : <b>{new_password}</b><br />
<i>Date de la mise &agrave; jour : {new_date}</i></p>
"
); // End of mails

?>