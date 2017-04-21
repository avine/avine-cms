<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );



class comSchedule_
{
	private	$col_max;



	public function __construct()
	{
		global $db;

		// col_* number (col_0, col_1, col_2, ...)
		$this->col_max = count($db->db_describe('schedule_tmpl')) -5; # 5 excluded fields : id, name, row_key, row_val, show_year
	}



	public function getColMax()
	{
		return $this->col_max;
	}



	// Get the list of the col_* fields from 'schedule_tmpl' table (for the specified $id template)
	public function getTmplColList( $id )
	{
		$col_list = array();

		global $db;
		if ($tmpl = $db->selectOne("schedule_tmpl, *, where: id=$id"))
		{
			// Shift 5 fields
			array_shift($tmpl); # id
			array_shift($tmpl); # name
			array_shift($tmpl); # row_key
			array_shift($tmpl); # row_val
			array_shift($tmpl); # show_year

			list($col, $title) = each($tmpl);
			while ($title) {
				$col_list[] = $title;
				list($col, $title) = each($tmpl);
			}
		}
		else {
			trigger_error("There's no record id=$id in 'schedule_tmpl' table");
		}

		return $col_list;
	}



	public function prepareTmplColQueryInsert( $col_list = array() )
	{
		return $this->prepareTmplColQuery($col_list, false);
	}



	public function prepareTmplColQueryUpdate( $col_list = array() )
	{
		return $this->prepareTmplColQuery($col_list, true);
	}



	private function prepareTmplColQuery( $col_list = array(), $update = false )
	{
		$query = '';

		global $db;
		for ($i=0; $i<$this->col_max; $i++)
		{
			isset($col_list[$i]) ? $col = $db->str_encode($col_list[$i]) : $col = "''";

			$update ? $field = "col_$i=": $field = '';

			$query .= " $field{$col},";
		}
		$query = preg_replace('~,$~', '', $query);

		return $query;
	}



	public function getTmplName( $id )
	{
		global $db;

		if ($name = $db->selectOne("schedule_tmpl, name, where: id=$id", 'name'))
		{
			return $name;
		} else {
			trigger_error("There's no record id=$id in 'schedule_tmpl' table");
			return false;
		}
	}



	public function getTmplOptions( $selected_id = '' )
	{
		global $db;
		$tmpl = $db->select("schedule_tmpl, id,name(asc)");

		$tmpl_options[''] = LANG_SELECT_OPTION_ROOT;
		for ($i=0; $i<count($tmpl); $i++) {
			$tmpl_options[ $tmpl[$i]['id'] ] = $tmpl[$i]['name'];
		}

		if ((count($tmpl_options)>1) && $selected_id) {
			$tmpl_options = formManager::selectOption($tmpl_options, $selected_id);
		}

		return $tmpl_options;
	}



	public function cleanRowValList( $row_val_list )
	{
		$row_val_list = preg_replace('~(\t|\n|\r)+~', "\n"	, $row_val_list);
		$row_val_list = preg_replace('~(^\n|\n$)~'	, ''	, $row_val_list);

		return $row_val_list;
	}



	public function explodeRowValList( $row_val_list )
	{
		if ($row_val_list) {
			return explode("\n", $row_val_list);
		}

		return false;
	}



	public function sheetsOptions( $selected_id = '', $backend = true )
	{
		$sheet_options = array();

		if ($backend)
		{
			$sheet_options[''] = LANG_SELECT_OPTION_ROOT;
			$delta = 1;
			$published = '';
		} else {
			$delta = 0;
			$published = ', where: published=1';
		}

		global $db;
		$sheet = $db->select("schedule_sheet, id,title, sheet_order(asc)$published");

		for ($i=0; $i<count($sheet); $i++) {
			$sheet_options[ $sheet[$i]['id'] ] = $sheet[$i]['title'];
		}

		if ((count($sheet_options)>$delta) && $selected_id) {
			$sheet_options = formManager::selectOption($sheet_options, $selected_id);
		}

		return $sheet_options;
	}



	public function sheetInfos( $sheet_id )
	{
		global $db;
		if ($infos = $db->selectOne("schedule_sheet, title,tmpl_id, where: id=$sheet_id, join: tmpl_id>; schedule_tmpl, row_key,row_val,show_year, join: <id"))
		{
			$return['title'		] = $infos['title'];
			$return['row_key'	] = $infos['row_key'];
			$return['row_val'	] = $this->explodeRowValList($infos['row_val']);
			$return['show_year'	] = $infos['show_year'];
			$return['col_list'	] = $this->getTmplColList($infos['tmpl_id']);

			return $return;
		}

		return false;
	}



	public function scheduleDBtoHTML( $time )
	{
		if ($time)
		{
			$t = str_split($time, 2);
			return $t[0].':'.$t[1]; # HH:MM
		}

		return '';
	}



	public function scheduleHTMLtoDB( $time )
	{
		$time = preg_replace('~[^0-9]~', ':', $time);
		$t = explode(':', $time);

		// No separator between HH and MM
		if (count($t) == 1 && formManager_filter::isInteger($time))
		{
			switch(strlen($time))
			{
				case 1:
					$return = '0'.$time.'00';
					break;

				case 2:
				case 3:
				case 4:
					$return = sprintf('%-04s', $time);
					break;
			}
		}
		elseif (count($t) == 2 && formManager_filter::isInteger($t[0]) && formManager_filter::isInteger($t[1]))
		{
			$return = sprintf('%02s', $t[0]).sprintf('%02s', $t[1]);
		}

		// Check the result
		if (isset($return))
		{
			$t = str_split($return, 2);
			if ( $t[0] <= 23 && $t[1] <= 59 ) {
				return $return;
			}
		}

		return false;
	}



	public function timeToTimeStamp( $time, $sheet_id )
	{
		if ($this->showYearStatus($sheet_id))
		{
			// DD/MM/YYYY expected
			if ($mktime = formManager_filter::isFormatedDate($time)) { # Specified year
				return $mktime;
			}
		}
		else
		{
			// DD/MM expected
			if (formManager_filter::DATE_FORMAT == FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY) {
				$time = "$time/2010"; # Fixed year
			} else {
				$time = "2010/$time"; # Fixed year
			}

			if ($mktime = formManager_filter::isFormatedDate($time)) {
				return $mktime;
			}
		}

		return false;
	}



	public function timeStampToTime( $mktime, $sheet_id )
	{
		if (!$mktime) {
			return '';
		}

		$time = getTime($mktime, 'format=short;time=no');

		// Remove YYYY ?
		if (!$this->showYearStatus($sheet_id))
		{
			if (formManager_filter::DATE_FORMAT == FORM_MANAGER_FILTER_DATE_FORMAT_DDMMYYYY) {
				$time = preg_replace('~/[0-9]+$~', '', $time);
			} else {
				$time = preg_replace('~^[0-9]/+~', '', $time);
			}
		}

		return $time;
	}



	public function showYearStatus( $sheet_id )
	{
		global $db;
		if ($show_year = $db->selectOne("schedule_sheet, where: id=$sheet_id, join: tmpl_id>; schedule_tmpl, show_year, join: <id"))
		{
			if ($show_year['show_year']) {
				return true;
			} else {
				return false;
			}
		}
		else {
			trigger_error("There's no record id=$id in 'schedule_sheet' table");
			return NULL;
		}
	}



	public function displaySheet( $sheet_id, $only_table = false )
	{
		global $db;

		$html = '';
		if ($this->getSheet($sheet_id, $title, $schedules, $headers))
		{
			$only_table or $html .= "<h2>$title</h2>\n";

			// Html header and footer
			$sheet = $db->selectOne("schedule_sheet, header, footer, where: id=$sheet_id, join: tmpl_id>; schedule_tmpl, row_key, join: <id");
			$only_table or $html .= $sheet['header'];

			$html .= $this->html($schedules, $headers, $sheet['row_key']);
			/*// Table
			$table = new tableManager($schedules);
			$table->header($headers);
			$table->delCol(0); # Delete the 'id' column
			#$table->widthsPercent('', array(0, 10,10,10,10,10,10,10,10)); # TODO...
			$table->cssClass('table-manager schedule-table');
			$html .= $table->html();*/

			$only_table or $html .= $sheet['footer'];
		}
		return $html;
	}



	function displaySheetModule( $sheet_id )
	{
		global $db;

		$html = '';
		if ($this->getSheet($sheet_id, $title, $schedules, $headers) && count($schedules))
		{
			$time = time();

			// Find the next schedule
			$i = 0;
			do {
				$timestamp = $db->selectOne("schedule, time, where: id=".$schedules[$i]['id'], 'time');

				if ( (date('d/m/Y', $timestamp) == date('d/m/Y')) || ($timestamp >= $time) ) {
					$founded = true;
				} else {
					 $i++;
				}
			}
			while ($i<count($schedules) && !isset($founded));

			if (isset($founded))
			{
				$schedule = $schedules[$i];
				array_shift($schedule); # Remove 'id'

				$i = 1;
				foreach($schedule as $key => $value)
				{
					if ($value) {
						$html .= "<p><span>{$headers[$i]} :</span><br />$value</p>";
					}
					$i++;
				}
			}
			else {
				$html = '<div style="color:grey;font-style:italic;">'.LANG_COM_SCHEDULE_MODULE_NOT_AVAILABLE.'</div>';
			}
			return "<div class=\"schedule-module\">$html</div>";
		}
		return $html;
	}



	private function getSheet( $sheet_id, &$title, &$schedules, &$headers)
	{
		global $db;

		if ($sheet_infos = $this->sheetInfos($sheet_id))
		{
			$title = $sheet_infos['title'];

			$headers = array('ID'); # Header (id)

			// Add row_key ?
			if ($sheet_infos['row_key']) {
				$headers[] = $sheet_infos['row_key']; # Header (row_key)
				$row_title_query = " row_title,";
			} else {
				$row_title_query = '';
			}

			// col_* query select
			$col_query = '';
			for ($i=0; $i<count($sheet_infos['col_list']); $i++) {
				$col_query .= "col_$i, ";
			}

			$headers[] = LANG_COM_SCHEDULE_SCHEDULE_TIME; # Header (time)
			$headers = array_merge($headers, $sheet_infos['col_list']); # Header (col_0, col_1, ...)

			// Current schedules list
			$schedules = $db->select("schedule, id,$row_title_query time(asc),$col_query where: sheet_id=$sheet_id");

			for ($i=0; $i<count($schedules); $i++)
			{
				$schedules[$i]['time'] = $this->timeStampToTime($schedules[$i]['time'], $sheet_id);

				for ($j=0; $j<count($sheet_infos['col_list']); $j++) {
					$schedules[$i]["col_$j"] = $this->scheduleDBtoHTML($schedules[$i]["col_$j"]);
				}
			}
			return true;
		}
		return false;
	}


	public function html( $schedules, $headers, $row_key )
	{
		$html = "\n<div class=\"table-manager schedule-table\"><table border=\"1\" cellspacing=\"0\"><!-- Table Begin -->\n";

		// Header
		$html .= "<thead><tr>\n";
		array_shift($headers); # Remove ID
		for ($i=0; $i<count($headers); $i++) {
			$html .= "\t<th>".$headers[$i]."</th>\n";
		}
		$html .= "</tr></thead>\n";

		// Body
		$html .= "<tbody>\n";
		if (count($schedules))
		{
			for ($i=0; $i<count($schedules); $i++) {
				$html .= "\t<tr".($i%2 ? ' class="table-tr-odd"' : '').'>';
				array_shift($schedules[$i]); # Remove ID
				$first = true;
				foreach ($schedules[$i] as $key => $value) {
					$row_key && $first ? $class = ' class="schedule-table-first"' : $class = '';
					$first = false;
					$html .= "<td$class>".($value ? $value : '&nbsp;').'</td>';
				}
				$html .= "</tr>\n";
			}
		}
		else
		{
			$html .= "\t<tr>";
			$html .= '<td colspan="'.count($headers).'" align="center"><div class="table-manager-message">'.LANG_TABLE_MANAGER_EMPTY.'</div></td>';
			$html .= "</tr>\n";
		}
		$html .= "</tbody>\n</table></div><!-- Table End -->\n\n";

		return $html;
	}


}


?>