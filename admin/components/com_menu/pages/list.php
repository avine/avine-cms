<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


// Path info
$path = admin_getPathway();


// Configuration
$start_view = true;


///////////
// Process

$filter = new formManager_filter();
$filter->requestVariable('post');


// Posted forms possibilities
$submit = formManager::isSubmitedForm('menu_', 'post'); // (0)
if ($submit)
{
	$del = $filter->requestName ('del_'	)->getInteger(); // (3)
	$upd = $filter->requestName ('upd_'	)->getInteger(); // (2)
	$new = $filter->requestValue('new'	)->get(); // (1)
} else {
	$del = false;
	$upd = false;
	$new = false;
}

$del_submit = formManager::isSubmitedForm('del_', 'post'); // (2)
$upd_submit = formManager::isSubmitedForm('upd_', 'post'); // (2)
$new_submit = formManager::isSubmitedForm('new_', 'post'); // (1)



// (3) Case 'del'
if ($del_submit)
{
	$del_submit_validation = true;

	$filter->reset();

	// Fields validation
	$id = $filter->requestValue('id')->getInteger();

	// Database Process
	if ($del_submit_validation)
	{
		$menu_name = $db->selectOne("menu, name, where: id=$id", 'name');

		// Delete module from FTP
		if ($filter->requestValue('del_mod_file')->get())
		{
			$module_file = "/modules/menu_$menu_name.php";

			$del_mod_success = str_replace('{file}', $module_file, LANG_ADMIN_COM_MENU_DEL_MOD_SUCCESS);
			$del_mod_failed  = str_replace('{file}', $module_file, LANG_ADMIN_COM_MENU_DEL_MOD_FAILED );

			admin_informResult(unlink(sitePath().$module_file), $del_mod_success, $del_mod_failed);
		}

		// Delete menu from DB
		$result = $db->delete("menu; where: id=$id");
		admin_informResult($result, str_replace('{menu}', $menu_name, LANG_ADMIN_COM_MENU_DEL_MENU_SUCCESS));
	}
}
if ($del && $del == 1) # Notice: the menu 'mainmenu' is required and can't be deleted !
{
	admin_message(LANG_ADMIN_COM_MENU_MAIN_MENU_REQUIRED, 'error');
	$del = false;
}
if ($del)
{
	if (!$db->select("menu_link, id, where: menu_id=$del")) # No links in this menu, then ok!
	{
		// Get the associated module name
		$menu_name = $db->selectOne("menu, name, where: id=$del", 'name');
		$module_file = "/modules/menu_$menu_name.php";

		// Delete menu from DB and module-file from FTP
		clearstatcache();
		if (is_file(sitePath().$module_file))
		{
			$start_view = false;

			// Title
			echo '<h2>'.LANG_ADMIN_COM_MENU_TITLE_DELETE.'</h2>';

			$html = '';
			$form = new formManager();
			$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'del_');
			$html .= $form->hidden('id', $del);

			$del_mod_file = str_replace('{file}', $module_file, LANG_ADMIN_COM_MENU_DEL_MOD_FILE);
			$html .= $form->checkbox('del_mod_file', 0, $del_mod_file).'<br /><br />';

			$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
			$html .= $form->end();
			echo $html;
		}
		else
		{
			// Simply delete menu from DB
			$result = $db->delete("menu; where: id=$del");
			admin_informResult($result);
		}
	}
	else {
		admin_message(LANG_ADMIN_COM_MENU_NOT_EMPTY_MENU, 'error'); # This menu contain links!
	}
}



// (2) Case 'upd'
if ($upd_submit)
{
	$upd_submit_validation = true;

	$filter->reset();

	// Fields validation
	$upd_id = $filter->requestValue('id')->getInteger();
	$desc   = $filter->requestValue('desc')->getNotEmpty();

	// Database Process
	if ($upd_submit_validation = $filter->validated())
	{
		$result = $db->update("menu; comment=".$db->str_encode($desc)."; where: id=$upd_id");
		admin_informResult($result);
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($upd) || (($upd_submit) && (!$upd_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_MENU_TITLE_UPDATE.'</h2>';

	// Id
	$upd ? $upd_id = $upd : '';

	$current = $db->selectOne("menu, *, where: id=$upd_id");

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'upd_');
	$html .= $form->hidden('id', $current['id']);

	// Meu details
	$fieldset = '';
	$fieldset .= '('.LANG_ADMIN_COM_MENU_ID.' : <strong>'.$current['id'].'</strong>)&nbsp; &nbsp;';
	$fieldset .= LANG_ADMIN_COM_MENU_NAME.' : <strong>'.$current['name'].'</strong>&nbsp; &nbsp;';
	$fieldset .= $form->text('desc', $current['comment'], LANG_ADMIN_COM_MENU_DESC, '', 'maxlength=255');
	$fieldset .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_MENU_MENU_FIELD_MENU);

	$html .= $form->end().'<br />';

	// Module file details																				# TODO - Restore the module if not exists !!!
	$fieldset = '';
	$module_file = "/modules/menu_{$current['name']}.php";
	$on_ftp  = str_replace('{file}', $module_file, LANG_ADMIN_COM_MENU_UPD_MOD_FILE_ON_FTP );
	$off_ftp = str_replace('{file}', $module_file, LANG_ADMIN_COM_MENU_UPD_MOD_FILE_OFF_FTP);
	is_file(sitePath().$module_file) ? $fieldset .= $on_ftp : $fieldset .= $off_ftp;
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_MENU_MENU_FIELD_MODULE);

	echo $html;
}



// (1) Case 'new'
if ($new_submit)
{
	$new_submit_validation = true;

	$filter->reset();

	// Name & Description
	$name = $filter->requestValue('name')->getVar(1, '', LANG_ADMIN_COM_MENU_NAME);
	$desc = $filter->requestValue('desc')->getNotEmpty(1, '', LANG_ADMIN_COM_MENU_DESC);

	// Menu behaviour for the associated module
	$menu_behaviour = $filter->requestValue('menu_behaviour')->getID(0);
	$menu_behaviour ? $menu_behaviour = "'$menu_behaviour'" : $menu_behaviour = '';

	// Duplicate menu name ?
	if ($name && $db->select('menu, id, where: name='.$db->str_encode($name))) {
		$filter->set(false, 'name')->getError(LANG_ADMIN_COM_MENU_NAME_ALREADY_EXIST, LANG_ADMIN_COM_MENU_NAME);
	}

	if ($new_submit_validation = $filter->validated())
	{
		// Database Process
		if ($result = $db->insert("menu; NULL, ".$db->str_encode($name).', '.$db->str_encode($desc)))
		{
			$new_module_file = "/modules/menu_$name.php"; # Path of the module file for this menu
			$ftp = new ftpManager(sitePath());
			if (!($module_exists = $ftp->isFile($new_module_file)) || $filter->requestValue('overwrite_module')->get())
			{
				$new_menu_id = $db->insertID();

				// PHP script to display a menu
				$menu_php_code = 
					'<?php'																."\n\n".
					'// No direct access'												."\n".
					'defined( \'_DIRECT_ACCESS\' ) or die( \'Restricted access\' );'	."\n\n".
					'$menu_id = '.$new_menu_id.';'										."\n".
					'$param = comMenu_behaviour('.$menu_behaviour.');'					."\n\n".
					'echo comMenu_menu($menu_id, $param);'								."\n\n".
					'?>';

				// FTP Process
				if ($result = $ftp->write($new_module_file, $menu_php_code))
				{
					if ($module_exists) {
						admin_message(str_replace('{input}', $new_module_file, LANG_ADMIN_COM_MENU_NEW_MOD_FILE_OVERWRITTEN), 'warning');
					} else {
						admin_message(str_replace('{input}', $new_module_file, LANG_ADMIN_COM_MENU_NEW_MOD_FILE_CREATED), 'info');
					}
				}
				else {
					// Remove menu !
					$db->delete("menu; where: id=$new_menu_id");
					$result = false;
				}
			}
			else {
				admin_message(str_replace('{input}', $new_module_file, LANG_ADMIN_COM_MENU_NEW_MOD_FILE_CONSERVED), 'info');
			}
		}

		admin_informResult($result, str_replace('{input}', $name, LANG_ADMIN_COM_MENU_NEW_MENU_SUCCESS));
	}
	else {
		echo $filter->errorMessage();
	}
}
if (($new) || (($new_submit) && (!$new_submit_validation)))
{
	$start_view = false;

	// Title
	echo '<h2>'.LANG_ADMIN_COM_MENU_TITLE_NEW.'</h2>';

	$html = '';
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'new_');

	$fieldset = '';
	$fieldset .= $form->text('name', '', LANG_ADMIN_COM_MENU_NAME, '', 'maxlength=100').'&nbsp; &nbsp;';
	$fieldset .= $form->text('desc', '', LANG_ADMIN_COM_MENU_DESC, '', 'maxlength=255');
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_MENU_MENU_FIELD_MENU);

	$fieldset = '';
	$fieldset .= LANG_ADMIN_COM_MENU_NEW_MOD_FILE_NAME.'<br /><br />';
	$fieldset .= $form->checkbox('overwrite_module', 0, LANG_ADMIN_COM_MENU_NEW_MOD_FILE_OVERWRITE.'(left)').'<br /><br />';
	$fieldset .= $form->select('menu_behaviour', comMenu_behaviourList(), LANG_COM_MENU_BEHAVIOUR);
	$html .= admin_fieldset($fieldset, LANG_ADMIN_COM_MENU_MENU_FIELD_MODULE);

	$html .= $form->submit('submit', LANG_ADMIN_BUTTON_SUBMIT);
	$html .= $form->end();
	echo $html;
}



//////////////
// Start view

if ($start_view)
{
	// Title
	echo '<h2>'.LANG_ADMIN_COM_MENU_TITLE_START.'</h2>';

	$html = '';

	// Database
	$menu_list = $db->select('menu, id, name(asc), comment');

	// Form
	$form = new formManager();
	$html .= $form->form('post', $_SERVER['PHP_SELF'].$path, 'menu_');

	for ($i=0; $i<count($menu_list); $i++)
	{
		$update[$i] = $form->submit('upd_'.$menu_list[$i]['id'], LANG_ADMIN_BUTTON_UPDATE, '', 'image=update'); // (2)
		$delete[$i] = $form->submit('del_'.$menu_list[$i]['id'], LANG_ADMIN_BUTTON_DELETE, '', 'image=delete'); // (3)

		$menu_list[$i]['id'] = "<span style=\"color:#999;\">{$menu_list[$i]['id']}</span>"; # Carefull : ID info no more available
	}

	// Table
	$table = new tableManager($menu_list);

	if (count($menu_list)) {
		$table->addCol($delete, 0);
		$table->addCol($update, 999);
	}

	$table->header(array('', LANG_ADMIN_COM_MENU_ID, LANG_ADMIN_COM_MENU_NAME, LANG_ADMIN_COM_MENU_DESC, ''));
	//$table->delCol(1); # Delete the 'id' column

	$html .= $table->html();

	$html .= $form->submit('new', LANG_ADMIN_BUTTON_CREATE); // (1)

	$html .= $form->end(); // End of Form

	echo $html;
}


echo '<br /><br /><a href="'.$_SERVER['PHP_SELF'].$path.'">'.LANG_ADMIN_BUTTON_REFRESH.'</a>';

//$path_back = admin_getPathway(2); // Button exemple to go back !
//echo '<br /><a href="'.$_SERVER['PHP_SELF'].$path_back.'">Retour</a>';

?>