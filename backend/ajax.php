<?
//return results with an option to advertise to them
require_once("includes/classes/ez_sql.php");
require_once('includes/functions.php');

	if (isset($_GET['action'])) {
		$action = $_GET['action'];
		
		switch ($action) {
			case 'get_rows':
				getRows();
				break;
			case 'row_count':
				getRowCount();
				break;
			case 'send_sms':
				if($GET['discount'] == 1){
					$GLOBALS['discount'] = true;	
				}else{
					$GLOBALS['discount'] = false;
				}
				sms_user();
				break;
			default;
				break;
		}
		
		exit;
	} else {
		return (false);
	}
?>