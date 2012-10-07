<?
//run weekly crons such as calculating rewards for users
require_once("../includes/classes/ez_sql.php");
require_once('../includes/functions.php');


if(reset_and_check_rewards()){ //called by the weekly cron to go through each user. and see how much we owe them and process other issues
	mail("kawess+msc@gmail.com","Checking rewards and updating user database successful","success");
}else{
	mail("kawess+msc@gmail.com","Checking rewards and updating user database ","failure - check weekly cron");
}

?>