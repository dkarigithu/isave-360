<?
//run hourly crons such as fetching data from the shortcode company
require_once("../includes/classes/ez_sql.php");
require_once('../includes/functions.php');

if(download_user_data()){//download and organize data texted in by users
	mail("kawess+msc@gmail.com","Downloading data successful","success");
}else{
	mail("kawess+msc@gmail.com","Downloading data failure","failure - check hourly cron");
}

?>