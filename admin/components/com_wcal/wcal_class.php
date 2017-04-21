<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


class admin_wcal extends wcal
{
	// Validate a new period candidate
	static public function periodCovering( $wid_begin, $wid_end, $exclude_pid = false ) # "pid" for period_id
	{
		global $db;

		// Exclude a specific period_id (usefull when updating a period, in backend)
		!$exclude_pid or $exclude_pid = " where: id!=$exclude_pid AND,";

		// Begin or end are inside a period
		$begin_inside = $db->selectCount("wcal_period, id,$exclude_pid where: id!=1 AND, where: wid_begin <= $wid_begin AND, where: wid_end >= $wid_begin");
		$end_inside = $db->selectCount("wcal_period, id,$exclude_pid where: id!=1 AND, where: wid_begin <= $wid_end AND, where: wid_end >= $wid_end");

		// Begin and end surrounds a period
		$both_surround = $db->selectCount("wcal_period, id,$exclude_pid where: id!=1 AND, where: wid_begin >= $wid_begin AND, where: wid_end <= $wid_end");

		if ($begin_inside || $end_inside || $both_surround)
		{
			return true;
		} else {
			return false;
		}
	}



	/*
	 * Purge : delete each row of 'wcal_dedicate' table which have not an associated row in the 'donate' table.
	 * This case can happen because of the purge process of the pending donations (see : /admin/components/com_donate/pages/waiting.php)
	 */
	static public function purgeDedicate()
	{
		global $db;

		$dedicate_purge = $db->fetchMysqlResults(
			$db->sendMysqlQuery(
				'SELECT w.`id` FROM `{table_prefix}wcal_dedicate` AS w '.
				'WHERE w.`donate_id` IS NOT NULL AND w.`donate_id` NOT IN (SELECT d.`id` FROM `{table_prefix}donate` AS d)'
			)
		);

		for ($i=0; $i<count($dedicate_purge); $i++)
		{
			$db->delete('wcal_dedicate_details; where: dedicate_id='.$dedicate_purge[$i]['id']);
			$db->delete('wcal_dedicate; where: id='.$dedicate_purge[$i]['id']);
		}

		return count($dedicate_purge);
	}

}


?>