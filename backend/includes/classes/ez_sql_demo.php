<?php

	// Include ezSQL in order to use it..
	include_once "ez_sql.php";

	// Get list of tables from current database..
	$my_tables = $db->get_results("SHOW TABLES",ARRAY_N);

	// Print out last query and results..
	$db->debug();
	
	// Loop through each row of results..
	foreach ( $my_tables as $table )
	{
		// Get results of DESC table..
		$db->get_results("DESC $table[0]");
		
		// Print out last query and results..
		$db->debug();
	}
	
?>