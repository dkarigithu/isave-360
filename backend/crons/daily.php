<?
//run weekly crons such as calculating rewards for users
require_once("../includes/classes/ez_sql.php");
require_once('../includes/functions.php');


if(get_users_to_send_join_confirmation()){ //called by the weekly cron to go through each user. and see how much we owe them and process other issues
	mail("kawess+msc@gmail.com","Sending join confirmation successful","success");
}else{
	mail("kawess+msc@gmail.com","Sending join confirmation ","failure - check daily cron");
}

?>