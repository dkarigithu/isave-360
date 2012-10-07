<?
require_once("includes/classes/ez_sql.php");
require_once('includes/functions.php');



//file_get_contents(SMS_BASE_URL ."sendsms?phone=254722709972&text=".urlencode('this is a test message'));
//die('here');
$_GET['phone'] = "+254722709972";
$_GET['text'] = 'wassssap';
$phone_number = ltrim($_GET['phone'],'+');//remove the plus from 254
$message = $_GET['text'];

//die('here');
save_smsc($phone_number,ltrim($_GET['smscenter'],'+'));//save sms center in a separate table

//mail("kawess@gmail.com","sms details" . $_GET['device'],'get:'.print_r($_GET,true));
data_from_sms($phone_number,$message);//process this and save it

//phpinfo(); 
?>