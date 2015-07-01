<?php /*

--------------------------------------
------ About the UI_Table Class ------
--------------------------------------

This class works with UI Tables.


--------------------------------
------ Setting up a Table ------
--------------------------------

To build a UI Table, there must be an array provided with one of two formats.

The first format is very simple:

	$table = [
		['data1a', 'data2a', 'data3a', '...'],
		['data1b', 'data2b', 'data3b', '...'],
		['data1c', 'data2c', 'data3c', '...']
	];
	
Each data field can also have a key, which assigns a CSS class to it.
	
	$table = [
		['someClass' => 'data1a', 'secondClass' => 'data2a', 'data3a', '...']
	];

The second format allows for additional options:

	$table = [
		'head'			=> ["Header 1", "Header 2", "Header 3", "..."],
		'data'			=> [
			['data1a', 'data2a', 'data3a', '...'],
			['data1b', 'data2b', 'data3b', '...'],
			['data1c', 'data2c', 'data3c', '...']
		],
	];

*/

abstract class UI_Table {
	
	
/****** Convert an array to a table ******/
	public static function draw
	(
		$tableArray				// <str:array> The table that you'd like to have built.
	,	$cssClass = "genTable"	// <str> The class name for the table.
	)							// RETURNS <str> HTML of the table being constructed.
	
	// echo UI_Table::draw($tableArray, $cssClass = "genTable");
	{
		// The array must contain the "data" element to process correctly
		if(!isset($tableArray['data']))
		{
			$tableArray = ['data' => $tableArray];
		}
		
		// Display the Table
		$tableHTML = '
<table class="' . $cssClass . '">';
		
		// Loop through the table headers, if present
		if(isset($tableArray['head']))
		{
			$tableHTML .= '
	<tr class="' . $cssClass . '-header">';
			
			foreach($tableArray['head'] as $key => $data)
			{
				$tableHTML .= '
		<th>' . $data . '</th>';
			}
			
			$tableHTML .= '
	</tr>';
		}
		
		// Prepare Values
		$currentRow = 0;
		
		// Loop through each row in the table to output the content
		foreach($tableArray['data'] as $row => $columnData)
		{
			$currentRow++;
			$colCount = 0;
			
			$tableHTML .= '
	<tr class="' . $cssClass . '-row-' . ($currentRow % 2 == 0 ? "even" : "odd") . '">';
			
			foreach($columnData as $key => $data)
			{
				$colCount++;
				
				// If the key is a string, the field has a specific class.
				// The left column also has a special class.
				$tableHTML .= '
		<td class="' . (is_string($key) ? $key . " " : '') . ($colCount == 1 ? $cssClass . '-left-column' : '') . '">' . $data . '</td>';
			}
			
			$tableHTML .= '
	</tr>';
		}
		
		// If there's a footer, display it
		if(isset($tableArray['footer']))
		{
			$tableHTML .= '
	<tr><td colspan="' . (isset($tableArray['data'][0]) ? count($tableArray['data'][0]) : 1) . '">' . $tableArray['footer'] . '</td></tr>';
		}
		
		$tableHTML .= '
</table>';
		
		return $tableHTML;
	}
}
