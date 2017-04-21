<?php
/* Copyright (C) 2008. All Rights Reserved. @license - Copyrighted Software. Author: Stéphane Francel */


// No direct access
defined( '_DIRECT_ACCESS' ) or die( 'Restricted access' );


/**
 * Class
 *
 * Use this class to manipulate the arrays you need to display on your pages.
 * 
 * This is a non destructive class, and the original array() will not be affected.
 * Instead, the methods have effect on '$this->table' property.
 */
class tableManager
{
	/**
	 * You can manage arrays of 1 or 2 dimensions: $this->table[$row] and $this->table[$row][$col].
	 * '$this->header'	  contain: the header of [$col] columns only !
	 * '$this->key_header' contain: the header if [$row] column (where 'key' means: first key of the array).
	 *
	 * Explanation:
	 *
	 * -> if you have:	$this->table[0]['column1'], $this->table[0]['column2'], ...
	 *					$this->table[1]['column1'], $this->table[1]['column2'], ...
	 *					..., 						..., 						...
	 *
	 *  -> then:			$this->header = array( 'Column1-header', 'Column2-header' )
	 *
	 *  -> and:			$this->key_header = 'n°'
	 */

	public	$table;

	public	$header,
			$key_header; 					# Header of the KEY of the $this->table

	public	$table_width = '', 	 			# ex: '100%' (same as '100') or '400px' 	=> work with: % or px
			$cell_width = array(); 			# ex: '20, 25, 25, 30' 						=> work only with: %

	public	$css_class = 'table-manager';	# Default css class

	public	$new_row_counter = 0, 
			$new_col_counter = 0;



	public function __construct( $table = array(), $header = array(), $key_header = '-' ) # You can set the header right here
	{
		if (!is_scalar($table))
		{
			$this->table 		= $table;
		} else {
			$this->table[0] 	= $table;
		}

		if (!is_scalar($header))
		{
			$this->header 		= $header;
		} else {
			$this->header[0] 	= $header;
		}

		$this->key_header = $key_header;
	}



	public function header( $header = '?', $key_header = '?' ) # You can also update the header here
	{
		if ($header == '?')
		{
			$this->header = array();  # Reset the header 
		} else {
			$this->header = $header;  # Update the header
		}

		if ($key_header == '?')
		{
			$this->key_header = '-';
		} else {
			$this->key_header = $key_header;
		}
	}



	// Call this just before the $this->html() method
	public function widthsPercent( $table_width = '', $cell_width = array() )
	{
		$table_width = trim($table_width);
		if ((!strstr($table_width, 'px')) && (!strstr($table_width, '%'))  && ($table_width != '')  && ($table_width != 0))
		{
			$table_width .= '%'; # Default extension is percent
		}
		$this->table_width = $table_width;

		/**
		 * ex.: $cell_width[$i] = 33.5;  without any extension (like 'px' '%')
		 * But the extension will be automaticaly inserted in the html-code: width="33.5%"
		 * (remember the cells width are only in percent)
		 */
		$this->cell_width = $cell_width;
	}



	public function cssClass( $class )
	{
		$this->css_class = $class;
	}



	/**
	 * When display $this->table, if this->header is set, it will be automaticaly displayed (X orientation).
	 * 
	 * But you can choose if you want to display the column of the first key (Y orientation) by using '$show_key_col'.
	 * Explanation: with '$this->table[$row][$col]', '[$row]' is the column of the first key.
	 */
	public function html( $show_key_col = 0 )
	{
		$dim = $this->dim();

		if ($dim != 0)
		{
			// Remember: $this->table_width is a string wich contain the extension '%' (default) or 'px'
			if ($this->table_width != 0)
			{
				$width = 'width="'.$this->table_width.'"';
			} else {
				$width = '';
			}

			if ($this->css_class)
			{
				$css_class = 'class="'.$this->css_class.'"';
			} else {
				$css_class = '';
			}

			$html = "\n<div $css_class><table $width border=\"1\" cellspacing=\"0\"><!-- Table Begin -->\n";

			// Header
			if ($this->header != array())
			{
				$html .= "<thead><tr>\n";

				if ($show_key_col == 1) {
					$html .= "\t<th>".$this->nbsp($this->key_header)."</th>\n";
				}

				for ($i=0; $i<count($this->header); $i++) {
					$html .= "\t<th>".$this->nbsp($this->header[$i])."</th>\n";
				}

				$html .= "</tr></thead>\n";
			}

			// Cell_width
			if (($dim == 1) || ($this->cell_width == array()))
			{
				$width_key_col = '';
			} else {
				$width_key_col = 'width="5%"';
			}
			//
			if ($dim == 2)
			{
				if ($show_key_col == 1)
				{
					$delta = 0.95;
				} else {
					$delta = 1;
				}

				for ($i=0; $i<count($this->cell_width); $i++)
				{
					if ($this->cell_width[$i] != 0)
					{
						$cell_width_attr[$i] = 'width="'.round($this->cell_width[$i]*$delta, 2).'%"'; 
					} else {
		    			$cell_width_attr[$i] = '';
		    		}
				}
			}

			// Body
			$html .= "<tbody>\n";
			if (count($this->table))
			{
				$i = 0;
				$tr_odd = 0;
				$insert_width = 1;

				reset($this->table);
				while (list($row, $values) = each($this->table))
				{
					if ($tr_odd%2 == 0)
					{
						$html .= "<tr><!-- New table line -->\n";
					} else {
						$html .= "<tr class=\"table-tr-odd\"><!-- New table line -->\n";
					}

					if ($show_key_col == 1) {
						$html .= "\t<th $width_key_col>".$this->nbsp($row)."</th>\n";
					}

					if ($dim == 1) {
						$html .= "\t<td>\n".$this->nbsp($values)."\n\t</td>\n";
					}          
					else
					{
						while (list($key, $value) = each($values))
						{
							if ($this->cell_width != array())
							{
								isset($cell_width_attr[$i]) ? $aaa = $cell_width_attr[$i] : $aaa = '';
								$html .= "\t<td ".$aaa.">\n".$this->nbsp($value)."\n\t</td>\n";
								$i++;
							} else {
								$html .= "\t<td>\n".$this->nbsp($value)."\n\t</td>\n";
							}
						}
					}
					$html .= "</tr>\n"; $tr_odd++;
				}
				$insert_width = 0;
			}
			else # $this->header is not empty, but this->table is empty!
			{
				$html .= "<tr>";

				$colspan = count($this->header);
				if ($show_key_col == 1) {
					$colspan++;
				}
				$html .= "<td colspan=\"$colspan\" align=\"center\"><div class=\"table-manager-message\">".LANG_TABLE_MANAGER_EMPTY."</div></td>";

				$html .= "</tr>\n";
			}

			$html .= "</tbody>\n</table></div><!-- Table End -->\n\n";
		}
		else {
			$html = "<div class=\"table-manager-message\">".LANG_TABLE_MANAGER_NOT_DEFINED."</div>";
		}

		return $html;
	}



	public function nbsp($content_cell)
	{
		if ($content_cell == "")
		{
			return '&nbsp;'; # For ie6 compatibility - do not allow empty cell when display the table
		} else {
			return $content_cell;
		}
	}



	/**
	 * if current num. of cols >= 2          : $col_values must be an array()
	 * if current num. of cols  = 1 (or = 0) : $col_values can be simply a value, but also an array() of 1 cell
	 */
	public function addRow($row_values, $row_pos = -1, $new_row_key = '') 
	{
		// Special case: if (dim == 1) then addRow('Element') and also addRow(array('Element')) will works!
		if ((!is_scalar($row_values)) && (count($row_values) == 1))
		{
			$row_values = $row_values[0];
		}

		if ($new_row_key == '')
		{
			$new_row = 'newrow_'.$this->new_row_counter;
			$this->new_row_counter++;
		} else {
			$new_row = $new_row_key;
		}

		$temp = array();
		if (count($this->table))
		{
			$i = 0; # (*) Index of the rows

			reset($this->table);
			while (list($row, $values) = each($this->table))
			{
				if ($i == $row_pos) {
					$temp[$new_row] = $row_values; 	# New row at the begining or in the middle
				}
				$temp[$row] = $values;
				$i++;
			}

			if (($row_pos == -1) || ($row_pos >= $i)) {
				$temp[$new_row] = $row_values; 		# New row at the end (default)
			}
		}
		else {
			$temp[$new_row] = $row_values;
		}
	
		$this->table = $temp;
	}



	###
	### TODO - known limitation addCol() and delCol() methods doesn't work well when the table is currently empty (the problems occure with the header)
	###

	public function addCol($col_values, $col_pos = -1, $new_header = '') 
	{
		/**
		 * if current num. of rows >= 2          : $col_values must be an array()
		 * if current num. of rows  = 1 (or = 0) : $col_values can be simply a value, but also an array() of 1 cell
		 */

		// First of all, know that adding a column, automaticaly reset all widths informations
		$this->table_width = '';
		$this->cell_width  = array(); 

		if (is_scalar($col_values))
		{
			$is_scalar = 1;
		} else {
			$is_scalar = 0;
		}

		$dim = $this->dim();

		$new_col = 'newcol_'.$this->new_col_counter; # The new key is automaticaly formated
		$this->new_col_counter++;

		if (count($this->table))
		{
			// Body
			$i = 0; # (*) Index of the rows ( in each row we will insert $col_values[$i] )
			$temp = array();
			reset($this->table);
			while (list($row, $values) = each($this->table))
			{
				if ($dim == 1) # Dim = 1
				{
					if (($col_pos == -1) || (($col_pos == 1))) # New col added at the end (default) = col 1
					{
						$temp[$row][0] = $values;
						if (!$is_scalar)
						{
							$temp[$row][1] = $col_values[$i];
						} else {
							$temp[$row][1] = $col_values;
						}
					} 
					else # New col added at the begining = col 0
					{
						if (!$is_scalar)
						{
							$temp[$row][0] = $col_values[$i];
						} else {
							$temp[$row][0] = $col_values;
						}
						$temp[$row][1] = $values;
					}
				}
				else # Dim = 2
				{
					$j = 0; # (*) Index of the col ( to know where will be inserted $col_values[$i] in the row )
					while (list($key, $value) = each($values))
					{
						if ($j == $col_pos)
						{
							if (!$is_scalar)
							{
								$temp[$row][$new_col] = $col_values[$i]; # New col at the begining or in the middle
							} else {
								$temp[$row][$new_col] = $col_values;
							}
						}
						$temp[$row][$key] = $value;
						$j++;
					}
					if (($col_pos == -1) || ($col_pos >= $j))
					{
						if (!$is_scalar)
						{
							$temp[$row][$new_col] = $col_values[$i]; # New col at the end (default)
						} else {
							$temp[$row][$new_col] = $col_values;
						}
					}
				}
				$i++;
			}
			$this->table = $temp;

			// Header
			if (($this->header != array()) || ($new_header != ''))
			{
				$temp = array();
	  
				reset($this->table);
				list($row, $values) = each($this->table);
				$j = 0;
				for ($i=0; $i<count($values)-1; $i++) # -1 : because we just add a col, then count(values) has been updated ! 
				{
					if ($j == $col_pos) {
						$temp[$j] = $new_header;
						$j++;
					}

					$temp[$j] = $this->header[$i]; # Even if $this->header[$i] not defined yet, this is not a problem.
					$j++;
				}

				if (($col_pos == -1) || ($col_pos >= $j)) {
					$temp[$j] = $new_header;
				}

				$this->header = $temp;
			}
		}
		else # that mean: $this->table is empty! Then simply create an array() wich have : dim = 1
		{
			// Body
			if (!$is_scalar)
			{
				for ($i=0; $i<count($col_values); $i++) {
					$this->table['newrow_'.$i] = $col_values[$i];
				}
				$this->new_row_counter++; 
			} else {
				$this->table['newrow_0'] = $col_values;
				$this->new_row_counter++;
			}

			// Header
			if ($new_header != '') {
				$this->header[0] = $new_header;
			}
		}
	}



	public function delCol( $col_pos )
	{
		// First of all, know that adding a column, automaticaly reset all widths informations
		$this->table_width = '';
		$this->cell_width  = array(); 

		$col_pos = explode(',', $col_pos); 	# It's possible to delete many columns!
		rsort($col_pos); 					# Important! Delete columns from the end table.

		$dim = $this->dim();

		if (count($this->table))
		{
			if ($dim == 1) # Dim = 1
			{
				$this->table  = array(); # Delete one column of a one column table!
				$this->header = array();
			}
			else # Dim = 2
			{
				for ($p=0; $p<count($col_pos); $p++)
				{
					// Body
					$temp = array();
					reset($this->table);
					while (list($row, $values) = each($this->table))
					{
						if ($col_pos[$p] <  0) {
							$col_pos[$p] = 0; 					# Delete the first column
						}

						if ($col_pos[$p] >= count($values)) {
							$col_pos[$p] = count($values)-1; 	# Delete the last column
						}

						$i = 0;
						while (list($key, $value) = each($values))
						{
							if ($i != $col_pos[$p]) {
								$temp[$row][$key] = $value;
							}
							$i++;
						}
					}
					$this->table = $temp;

					// Header
					$j = 0;
					if ($this->header != array())
					{
						$temp = array();
						for ($i=0; $i<count($this->header); $i++)
						{
							if ($i != $col_pos[$p]) {
								$temp[$j] = $this->header[$i];
								$j++;
							}
						}
						$this->header = $temp;
					}
				}

				if ($this->dim() == 1) # that mean : we delete so many columns that now $this->table have $this->dim() = 1 !
				{
					$temp = array();
					reset($this->table);
					while (list($row, $values) = each($this->table)) {
						while (list($key, $value) = each($values)) {
							$temp[$row] = $value;
						}
					}
					$this->table = $temp;
				}
			}
		}
		else # that mean: $this->table is empty! Then simply delete column from header if exist
		{
			for ($p=0; $p<count($col_pos); $p++)
			{
				// Header
				$j = 0;
				if ($this->header != array())
				{
					$temp = array();
					for ($i=0; $i<count($this->header); $i++)
					{
						if ($i != $col_pos[$p]) {
							$temp[$j] = $this->header[$i];
							$j++;
						}
					}
					$this->header = $temp;
				}
			}
		}
	}



	public function dim( $show_dim = 0 )
	{
		if (count($this->table)) // We have a table
		{
			reset($this->table);
			$num_rows = count($this->table);

			list($row, $values) = each($this->table);

			$num_cols = count($values);
			if ($num_cols == 1)
			{
				$dim = 1;
			} else {
				$dim = 2; # We can manage arrays of 1 or 2 dimenssions
			}

			if ($show_dim == 1) echo "Table infos : rows=$num_rows | columns=$num_cols (dim=$dim)";
		}
		elseif ($this->header != array()) 
		{
			$num_cols = count($this->header);

			if ($num_cols == 1)
			{
				$dim = 1;
			} else {
				$dim = 2;
			}

			if ($show_dim == 1) {
				echo "Table infos : rows=0 | columns=$num_cols (dim=$dim)";
			}
		}
		else
		{
			$dim = 0;
	  
			if ($show_dim == 1) {
				echo "Table infos : Unknown!";
			}
		}
	
		return $dim;
	}



	// Use this to know how many $col_values you have to enter into addCol() function
	public function numRows()
	{
		if (count($this->table))
		{
			reset($this->table);

			return count($this->table);
		} else {
			return 0;
		}
	}



	// Use this to know how many $row_values you have to enter into addRow() function
	public function numCols()
	{
		if (count($this->table))
		{
			reset($this->table);
			list($row, $values) = each($this->table);

			return count($values);
		} else {
			return count($this->header); # Works even if $this->header is empty !
		}
	}



	// Check the table validity (if you have some problem when display a table, this function will help you to find where's the problem)
	public function checkup () # Works only if $this->dim() == 2
	{
		$cols_keys = array();

		// Html ergonomy
		$td_init = 'style="text-align:center;font-weight:bold;background-color:#F1F8FC;"';
		$td_rowspan = 'rowspan="2" style="text-align:center;font-weight:bold;"';
		$sep = ' <span style="color:#66A8D9;">|</span> ';

		$html = '<table border="0" cellspacing="0" class="table-manager" style="font:10px/18px Verdana"><tr><th>Row key</th><th>Cols Keys</th><th>Cols number</th></tr>';

		$ini = true;
		reset($this->table);
		while (list($rows, $cols) = each($this->table))
		{
			$j = 0;
			if ($ini) {
				$html .= "<tr><td $td_init>Ref.</td>";
			} else {
				$html .= "<tr><td $td_rowspan>$rows</td>";
			}

			$keys = $sep; $values = '';
			while (list($key, $value) = each($cols))
			{
				if ($ini) {
					$cols_keys[$j] = $key;
					$keys .= $key.$sep;
				}
				else {
					if ($key == $cols_keys[$j]) {
						$keys .= $key.$sep;
					} else {
		  				$keys .= '<span style="color:red;">'.$key.'</span>, ';
		  			}
					$values .= '<span style="color:#2C79B3;">['.$key.']</span> &nbsp; '.htmlspecialchars($value).'<hr />';
				}
				$j++;
			}

			// $html .= "<td>".ereg_replace('[[:space:]]\|[[:space:]]$', '', $keys  )."</td>";
			if ($ini)
			{
				$html .= "<td $td_init>$keys</td>";
			} else {
				$html .= "<td>$keys</td>";
			}

			if ($ini)
			{
				$html .= "<td $td_init>$j</td></tr>";
			}
			else
			{
				if ($j == count($cols_keys))
				{
					$html .= "<td $td_rowspan>$j</td></tr>";
				} else {
					$html .= "<td $td_rowspan><span style=\"color:red;\">$j</span></td></tr>";
				}
				$html .= "<tr style=\"background-color:#FAFAFA;\"><td>$values</td></tr>";
			}

			// Restart
			if ($ini) {
				$ini = false;
				reset($this->table);
			}
		}

		$html .= '</table>';

		echo $html;
	}

}

?>