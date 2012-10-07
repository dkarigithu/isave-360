<?php
//return results with an option to advertise to them
require_once"includes/classes/ez_sql.php";
require_once('includes/functions.php');
// Get list of tables from current database..

	if(isset($_GET['sendads'])){
		if($_GET['countonly']=='false'){
			if( $_GET['sexmale'] == 'true'){
			$sexvar = " 1";	
		}
		if( $_GET['sexfemale']=='true'){
			$sexvar = " 2";
		}
		if($_GET['sexmale'] == $_GET['sexfemale']){//both unchecked or both checked means sex don't matter
			$sexvar = " (0 OR sex = 1 OR sex = 2)";//unknown, male or female
		}
		$query = "SELECT number, age, sex, location FROM user WHERE";
		$location_clause = ($_GET['location'] == "")? "" : " AND location = '" . $_GET['location'] . "'";
		$whereclause =  " (age >= " . $_GET['agemin'] . " AND age <= " . $_GET['agemax'] . ") AND sex = " . $sexvar   . $location_clause;
		$query .= $whereclause;
	}else{
		$query = "SELECT count(*) AS total_count FROM user WHERE";
	}
	
	if($users = $db->get_results($query)){
		 // Loop through each row of results..
		foreach ($users as $user)
		{	
			if($_GET['countonly']=='false'){
				switch($user->sex){
					case 0:
						$user->sex = "Unknown";
						break;
					case 1:
						$user->sex = "Male";
						break;
					case 2:	
						$user->sex = "Female";
						break;
				}
				if($user->age == NULL) $user->age = "Unknown";
				if($user->location == NULL) $user->location = "Unknown";
				echo "$user->number - age: $user->age - sex: $user->sex - location: $user->location<br>";
			}else{
				echo "Count is: $user->total_count";	
			}
		}
	}else {
		echo 'No Results for :"'.$query.'"';
	}

	
}
?>