<?
//GLOBAL DECLARATION SECTION
//These are the variables for sending sms's
/*
	* //Username that is to be used for submission
	* password that is to be used along with username
	* Sender Id to be used for submitting the message
	* Message content that is to be transmitted
	* Mobile No is to be transmitted.
	* What type of the message that is to be sent; 0:means plain text,1:means flash,2:means Unicode (Message content should be in Hex),
		6:means Unicode Flash (Message content should be in Hex)</li>
	* Require DLR or not; 0:means DLR is not Required, 1:means DLR is Required</li>
*/
///
define('SMS_HOST',"");
define('SMS_PORT',"");
define('SMS_USERNAME',"");
define('SMS_PASSWORD',"");
define('SMS_MESSAGE_TYPE',0);//plain text
define('SMS_DLR',"");
define('SMS_SENDER',"");//DO NOT USE A MODIFIED ALPHANUMERIC CODE  AS SAFCOME WILL BLOCK THIS: http://www.humanipo.com/blog/127/Safaricom-Blocking-International-Bulk-SMS
define('SMS_MAX_SENDERS',1);// maximum numbers you can send to per go
define('SITE_URL',"http://isave.co.ke");
define('SITE_ABBREV',"");//this was made so if we're using a shared short-code, it can be inserted into messages from users'
define('SITE_NAME',"iSave");
define('AFF_ID_PREFIX',"x1");//this prefix helps us extract the affiliate id from messages


define('SMS_BASE_URL','http://192.168.109.101:9090/');

define('REGEXP_MALE_PART',"male|m|boy|guy|dude|man");
define('REGEXP_FEMALE_PART',"female|f|chick|girl|woman");
define('REPLY_TO_NUMBER',4444);

define('MAX_ADS_PER_WEEK',2);
define('MINIMUM_REWARD_LEVEL',50);//Miminum amount of cash to send as credit to reward an affiliate

//amount of profits we're making for each text message sent
define('PROFITS_WITHOUT_DISCOUNT',1);
define('PROFITS_WITH_DISCOUNT',2);
//message constants  below must be below other ones above, sincey they use them
define('MESSAGE_NO_INVITE_CODE', "Invalid invite code. Get deals via SMS that you can use anywhere to save money and get into events. Get a valid code from a friend to join the club. Invite Only");
define('MESSAGE_WELCOME',"Welcome to ".SITE_NAME.". Get deals via SMS that you can use anywhere to save money and get into events. Text back your age, town, and gender to customize your deals");


define('MESSAGE_REQUEST_DETAILS',"These details help get you better offers. Text back your *requested_details*" . " to customize your deals" );
define('MESSAGE_CONFIRM_JOINED','Welcome, member! Go to '.SITE_URL.' for details. To invite 3 friends, forward them this code: --> "Text *replace_code* to '.REPLY_TO_NUMBER.' to join the '.SITE_NAME.' VIP club"');
define('MESSAGE_DETAILS_UPDATED_CONFIRMATION', " has been updated. Your deals will now be better customized");




//Downloading the subscribers from the shortcode provider
define('REMOTE_USER_LIST',"http://localhost/msc/subscribers.xls");//path where the file was manually downloaded and stored
//define('LOCAL_USER_LIST', "C:\xampp\htdocs\msc\tmp\subscribers.xls");//path where it's stored in the loca server to be processed
define('LOCAL_USER_LIST', "tmp\subscribers.xls");//path where it's stored in the loca server to be processed
//END GLOBAL DECLARATION SECTION

function data_from_sms($phone_number,$message){
	$mes_num['message'] = $message;
	$mes_num['number'] = $phone_number;
	read_user_data("",$mes_num);
	
}

function download_user_data(){
	//download the text messages and data sent by users to the short code and store it locally
	
	/* get hostname and path of the remote file */
	$download_host = parse_url(REMOTE_USER_LIST, PHP_URL_HOST);
	$path = parse_url(REMOTE_USER_LIST, PHP_URL_PATH);
	
	/* prepare request headers */
	$reqhead = "GET $path HTTP/1.1\r\n"
			 . "Host: $download_host \r\n"
			 . "Connection: Close\r\n\r\n";
	
	/* open socket connection to remote host on port 80 */
	$fp = fsockopen($download_host , 80, $errno, $errmsg, 30);
	
	/* check the connection */
	if (!$fp) {
		print "Cannot connect to $download_host!\n";
		return false;
	}
	
	/* send request */
	fwrite($fp, $reqhead);

	/* read response */
	$res = "";
	while(!feof($fp)) {
		$res .= fgets($fp, 4096);
	}		
	fclose($fp);
	
	/* separate header and body */
	$neck = strpos($res, "\r\n\r\n");
	$head = substr($res, 0, $neck);
	$body = substr($res, $neck+4);
        $m = "";//initialize
	/* check HTTP status */
	$lines = explode("\r\n", $head);
	preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $lines[0], $m);
        
	$status = $m[2];
	if ($status == 200) {
            $fhandle = fopen(LOCAL_USER_LIST,'w');
            $putval = fwrite($fhandle,$body);
            fclose($fhandle);
		//$putval = file_put_contents(LOCAL_USER_LIST, $body);
                //return(false);//CONTINUE FROM HERE
		if($putval != false){
                    if(read_user_data(LOCAL_USER_LIST)){
                            return(true);
                    }else{
			return(false);	
                    }
                }else{
                    return(false);
                }
              
	} else {
		return(false);
	}

}

function read_user_data($path_to_file, $direct_from_message = false){
	//read each line of data and call the parse data to parse it
	require_once 'classes/reader.php';
	$data = new Spreadsheet_Excel_Reader();
	// Set output Encoding.
	//$data->setOutputEncoding('CP1251');
	//By default rows & cols indeces start with 1
	
	if($direct_from_message===false)$data->read($path_to_file);
	/*
	$data->sheets[0]['numRows'] - count rows
	$data->sheets[0]['numCols'] - count columns
	$data->sheets[0]['cells'][$i][$j] - data from $i-row $j-column
	$data->sheets[0]['cellsInfo'][$i][$j] - extended info about cell
	$data->sheets[0]['cellsInfo'][$i][$j]['type'] = "date" | "number" | "unknown"
	if 'type' == "unknown" - use 'raw' value, because  cell contain value with format '0.00';
	$data->sheets[0]['cellsInfo'][$i][$j]['raw'] = value if cell without format 
	$data->sheets[0]['cellsInfo'][$i][$j]['colspan'] 
	$data->sheets[0]['cellsInfo'][$i][$j]['rowspan'] 
	*///
	//error_reporting(E_ALL ^ E_NOTICE);
	global $db;
        $tomessage = ""; $datarow = "";//initialize
	$checked = $db->get_var("SELECT last_checked_record FROM parameters");
        $checked ++;//so we start reading from a fresh record
        if($checked < 2) $checked = 2;//not counting the first heading row, the first row of data is '2'
		if($direct_from_message !== false){
				$data->sheets[0]['numRows'] = 2;
				$data->sheets[0]['numCols'] = 7;
                //NOTE!!!!!!!!!!!!! The second index below, (5 and 4) are reduced to 4 and 3 while been put into datarow)
				$data->sheets[0]['cells'][2][5] = $direct_from_message['number'];//number
				$data->sheets[0]['cells'][2][6] = $direct_from_message['message']; //message
                $checked = 2;
		}
		
	for ($i = $checked; $i <= $data->sheets[0]['numRows']; $i++) {
		unset($datarow);
		for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
			//echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
			$datarow[]=$data->sheets[0]['cells'][$i][$j];
		}
		$response = parse_user_data($datarow);
		if($response != false) $tomessage[] = format_message($response);
	}
	$checked = $i-1;
	if (is_array($tomessage)) sms_user($tomessage,false);
	if($direct_from_message===false) $db->query("UPDATE parameters SET last_checked_record = ".$checked);
	return(true);
}

function save_smsc($unumber, $smscenter){
    global $db;
    $query = "UPDATE user SET smsc = $smscenter WHERE number = $unumber";
    $db->query($query);
}


function format_message($tomessage){//simply return an object that is sent to sms user
    $obj = new stdClass;
    $obj->message = $tomessage['message'];
    $obj->number = $tomessage['number'];	
    return($obj);
        
       
        
	
}

function parse_user_data($datarow){
	global $db;
	/*Data format:
	Date	Service	Operator	SN	Msisdn	Message	Direction	Prefix	Partner	Price	Status
	2012-01-19 10:49:56	mysouktel	Safaricom	6126	0	SAIDI KUTOKA NYAMACHE .NICHEZEE ATAKWETU WAPO.	MO		souktel	0	Error
	//if you need to request data, return it in the form of user->number and user->message
	*/
	//parse data that has been passed to me into descrete fields and call the store_user_data function
	//return to caller whether to request more data from the user
	//parse the user's data - extract age, sex, location, anythng else the user typed	
	$datarow['ref_id'] = 0;
	$datarow[5] = trim(str_replace(SITE_ABBREV,"",$datarow[5]));
	$age_string = '/\b(?: ,;-){0,3}\d{1,2}(?: ,;-){0,3}\b/';

	$sex_string = '/\b(?: ,;-){0,3}(?i:'.REGEXP_MALE_PART.'|'.REGEXP_FEMALE_PART.')(?: ,;-){0,3}\b/';
	$affid_string = '/\b(?: ,;-){0,3}(?i-:'.AFF_ID_PREFIX.'[a-z0-9]+)(?: ,;-){0,3}\b/';
	//location: have to curate manually
	$age = ""; $sex = ""; $location = ""; $affid = ""; $to_remove ="";
	$requested_details="";	$requested_details_example = "";	
	$details_found = false;
    $user_exists = false;
    
    $query = "SELECT invited FROM user WHERE number = " . $datarow[4];
    if($result =  $db->get_row($query)) $user_exists = true;
    
    $invite_code_invalid = false;
    
    
	//first get refferer id - this must be first so search & replace for location removes aff-id, so removing age doesn't partially remove the aff_id before time
	$match = preg_match_all($affid_string,$datarow[5],$matches,PREG_PATTERN_ORDER);
	if($match >= 1){//this is the same as an invite code
        
		$affid = strtolower($matches[0][0]);
		$datarow['ref_id'] = $affid;
		$to_remove[]=$matches[0][0];
		$details_found = true;
        $add_and_text_user = false;
        $valid_invite = false;
        // CHECK DATABASE IF THIS INVITE CODE IS A VALID ONE - REMEMBER TO MAKE CAPITALIZATION OPTIONAL (EVEN IN OTHER CHECKS)
        $query = "SELECT invite_code FROM invites WHERE invite_code = '" .$affid."'";
        if($result2 =  $db->get_row($query)){
            //the invite code is actually valid
            if($user_exists){;	
        		//but have we saved their invite code as valid?
        		if($result->invited == 0){
        		      $add_and_text_user = true;
        		}else{
        			//do nothing and continue - user sent invite code twice for some reason
        		}
            }else{//user hasn't been added yet, so we call add user'
                $add_and_text_user = true;
                
            }
        } else{//invite code was invalid or didn't exist'
            $invite_code_invalid = true;
        }
        
        
	}else{   
       $invite_code_invalid = true;
    }
    
    ///deal with whether this user was invited properly
	if($invite_code_invalid && $result->invited != 1){//he may have sent the code in earlier texts
	       $return_val['message'] = MESSAGE_NO_INVITE_CODE;
           $return_val['number'] = $datarow[4];
           $datarow['ref_id'] = 0;//reset this to zero so when we're calculating affiliate comissions, we don't count this once invites are opend up''
           if($user_exists){
                update_user($datarow,0);//update message sent field - keep invited = 0
           }else{
                add_user($datarow,0,0);//add user phone ans message sent - keep invited = 0
           }
           return($return_val);
	}
    
    if($add_and_text_user == true){ 
         $return_val['message'] = MESSAGE_WELCOME;
         $return_val['number'] = $datarow[4];
         if($user_exists){
            //update user
              update_user($datarow,1);//update invited to 1 and append message sent field
         }else{
            //add user
            add_user($datarow,0,1);//add user number, message and make invited =1
         }
         return($return_val);  
     }
    
    
    
    
	//next get age
	$match = preg_match_all($age_string,$datarow[5],$matches,PREG_PATTERN_ORDER);
	if($match >= 1){
		$age = $matches[0][0];
		$to_remove[]=$matches[0][0];
		$details_found = true;
	}else{
		$requested_details .= ($requested_details != '' ? ' and ' : '') . " age";	
		$requested_details_example .= " 24";
	}
	//next get sex
	$match = preg_match_all($sex_string,$datarow[5],$matches,PREG_PATTERN_ORDER);
	if($match >= 1){
		$sex = $matches[0][0];
		$to_remove[]=$matches[0][0];
		$details_found = true;
	}else{
		$requested_details .= ($requested_details != '' ? ' and ' : '') . " gender";	
		$requested_details_example .= " female";
	}
	
	//next get an approximation of the location
	//if($details_found) 
        $location = trim(str_replace($to_remove,"",$datarow[5]));
	if($location != ""){
	}else{
		$requested_details .= ($requested_details != '' ? ' and ' : '') . " location";	
		$requested_details_example .= " mombasa";
	}
	$datarow['age'] = $age; $datarow['sex'] = $sex; $datarow['location'] = $location; $datarow['affid'] = $affid; $datarow['number'] = $datarow[4];
	$query = "SELECT id, number, age, sex, location, ref_id, join_confirmed, asl_requested, message_sent, invited FROM user WHERE number = " . $datarow['number'];
	
        if($result =  $db->get_row($query)){;	
		//remember to append the recieved message to the current one in the db
		if($result->asl_requested == 0 && $requested_details != ""){
			$request_additional_details = true;
		}else{
			$request_additional_details = false;
		}
		$datarow['message'] = $datarow[5];//($result->message_sent == "" ? "" : $result->message_sent . "*" ). $datarow[5]; 
		update_user($datarow,1,$request_additional_details);
	}else{//user not listed yet
		add_user($datarow,1,1);//this should be redundant since we added invites - user will be added and invited by the time he gets here
		$result =  $db->get_row($query);
		if($requested_details != ""){
			$request_additional_details = true;
		}else{
			$request_additional_details = false;
		}
	}
	$return_val['number'] = $datarow[4];//msisdn
	$return_val['id'] = $result->id;//msisdn
	//send a text below if additional details are required
	if($requested_details !="" && $request_additional_details){
		$return_val['message'] = str_replace("*requested_details*",$requested_details,MESSAGE_REQUEST_DETAILS) . " E.g. ".SITE_ABBREV." $requested_details_example";//change message if user has already sent the message with the required details
	}else{
		get_users_to_send_join_confirmation($return_val);
		$return_val = false;
	}	
        //echo ('texted: ' .$datarow[5] .' message: '.$return_val['message'] . "<br><br><br>");
	return($return_val);
}

function encode_sex($sex){
	$return = 0;//unknown
	$sex_string_male = '/\b(?: ,;-){0,3}(?i:'.REGEXP_MALE_PART.')(?: ,;-){0,3}\b/';
	$sex_string_female = '/\b(?: ,;-){0,3}(?i:'.REGEXP_FEMALE_PART.')(?: ,;-){0,3}\b/';
	if(preg_match_all($sex_string_male,$sex,$matches,PREG_PATTERN_ORDER) >= 1) $return = 1;//male
	if(preg_match_all($sex_string_female,$sex,$matches,PREG_PATTERN_ORDER) >= 1) $return = 2;//female
	return($return);
}

function decode_sex($sex){
	switch($sex){
		case 1:
			$return = "male";
			break;
		case 2:
			$return = "female";
			break;
		default:
			$return = "unknown";
	}
	return($return);
}

function update_user($datarow,$invited,$requested_details = false){
	//see which parts are missing and update those	 (age, sex, location, ref_id,asl_requested
	global $db;
	$datarow['sex'] = encode_sex($datarow['sex']);
	//origianlly was going to see if current data was filled, but instead, will allow user to update their status anytime
	$fields = ""; $field_values = "";
	if($datarow['sex'] != 0){
		 $fields .= " sex,";
		 $field_values .= ($field_values == ""? "" : ",") ." sex = " . $datarow['sex'];
	}
	if($datarow['location'] != ""){
		 $fields .= " location,";
		 $field_values .= ($field_values == "" ? "" : ",") ." location = '". $datarow['location'] . "'";
	}
	if($datarow['ref_id'] != "" && $datarow['ref_id'] != 0){
		 $fields .= " referrer,";
		 $field_values .= ($field_values == "" ? "" : ",") ." ref_id = " . convert_aff_id_to_id($datarow['ref_id']);
	}
        
	if($datarow['age'] != 0 && $datarow['age'] != ""){
		 $fields .= " age,";
		 $field_values .= ($field_values == "" ? "" : ",") . " age = " . $datarow['age'];
	}
	if($datarow[5] != ""){
  		 //$datarow[5] = ($current_data->message_sent == "" ? "" : $current_data->message_sent . "*") . $datarow[5];
		 $field_values .= ($field_values == "" ? "" : ",") . " message_sent =  CONCAT(message_sent,'*','" . $datarow[5] . "')";
	}
	
    //invited status
    $field_values .= ($field_values == "" ? "" : ",") . " invited = " . $invited;
	
    
    if($requested_details)$field_values .= ($field_values == "" ? "" : ",") . " asl_requested = " . 1;
    
	if(trim($field_values) != ""){
		$db->query("UPDATE user SET $field_values WHERE number = " . $datarow[4]);
                //notify user with a generic message that their data has been updated
	//
                $tomessage['number'] = $datarow[4];
                $tomessage['message'] = $fields . MESSAGE_DETAILS_UPDATED_CONFIRMATION;
                if($fields != "")sms_user(format_message($tomessage),false);
	}
	
}



function convert_id_to_aff_id($id){
	return(AFF_ID_PREFIX . base_convert($id,10,36));
}
function convert_aff_id_to_id($id){//convert from 36 to 10
        $to_convert = str_replace(AFF_ID_PREFIX,"",$id);
        $to_convert = base_convert($to_convert,36,10);
		if ($to_convert == '') $to_convert = 0;
	return($to_convert);
}

function add_user($data, $asl_requested,$invited=0){//add a new user into the database
//also record his recommender if they exist	
	global $db;
        $query = "INSERT INTO user (number,ref_id,age,sex,location,asl_requested,message_sent, invited) VALUES (".$data[4].","
	.convert_aff_id_to_id($data['ref_id']).","
	.($data['age'] == '' ? '0' : $data['age']).","
	.encode_sex($data['sex']).","
	."'".$data['location']."'".
	",$asl_requested,'".$data[5]."',$invited)";
	//add provided data, and also say we have requested the user's asl
	//echo ($query);
        $db->query($query);
        $query = "SELECT id FROM user WHERE number = ".$data[4];
		$result = $db->get_row($query);
		$query = "INSERT INTO invites (invite_code) VALUES ('".convert_id_to_aff_id($result->id)."')";//create a new invite code
        $db->query($query);//even if the user isn't officially invited, we can still add his invite code so when she's  a full member she can use it
        //echo ("end of query");
        return(null);      
}




function sms_user($number_and_message = "", $is_advert = true){
    global $db;
	//send an sms to the user(s) from a previously run query
        //sometimes it's an array of objects, other times, it's an array
	//call sms_user before calling store_user_data to store send counts for the user and also profit generated for the referrer, etc
	if($number_and_message ==""){
		 $toadvertise = loadUsers("");
	}else{
                
            if(is_object($number_and_message)){
                $toadvertise[] = get_object_vars($number_and_message);
            }else{
                $counter = count($number_and_message);
                for ($i = 0; $i < $counter; $i++){
                    $toadvertise[] = $number_and_message[$i];
                }
            }    
        }

	
 //IMPORTANT!!! BELOW CODE WILL HAVE TO BE EDITED IF YOU WANT TO TEXT TO MULTIPLE USERS - MIGHT HAVE TO ADD INDIVIDUALLY AFTER EXPLODING THE ARRAY       
 ///slice off the array below - is_object is when we get stuff from db
	if(is_array($toadvertise)){//results found
		require_once('classes/sms.php');
		
		$offset = 0;
		$loop_count = ceil(count($toadvertise)/SMS_MAX_SENDERS);//number of times to loop
		if($number_and_message != ""){
                    $message = $number_and_message[0]->message;
                }else{
                    $message = $_GET['smstext'];
                }
		for ($loops = 1; $loops <=  $loop_count; $loops++){
			$user = array_slice($toadvertise,$offset,SMS_MAX_SENDERS);
			unset($numberlist);
			foreach ($user as $numbers) {
                            if(is_object($numbers)){
                                $numberlist[] = $numbers->number;		
                            }else{
                                $numberlist[] = $numbers['number'];		
                            }
				
			}
			$numberlist = implode(",",$numberlist);
			//$obj = new Sender(SMS_HOST,SMS_PORT,SMS_USERNAME,SMS_PASSWORD,SMS_SENDER, $message,$numberlist,SMS_MESSAGE_TYPE,SMS_DLR);
			//$results = $obj->Submit();
            
            $numberlist = '+' . $numberlist;
            //mail("kawess@gmail.com","this works nigga $numberlist, $message ","it works");
            $sendstring = SMS_BASE_URL ."sendsms?phone=$numberlist&text=".urlencode($message);
            $sendstring = str_replace('+254','0',$sendstring);
            $results = file_get_contents($sendstring);
            
			
			$offset += SMS_MAX_SENDERS;
			
            if(($results !== false)){
				//echo ("1701 code is for success");
				//foreach ($results as $line_num => $line) {
				//	echo "Line #<b>{$line_num}</b> : " . $line . "<br />\n";
					//add to ads_received_week and ads_received_total
					//update my referrer (ref_id) (if i was refered) with total_profits_generated and  total_profits_generated_unrewarded
					/////this is the format of $line: 1701|<CELL_NO>:<MESSAGE ID> - so get the number that was successful
					//$pieces = explode("|",$line);
					//if(trim($pieces[0]) == 1701){//success - //$pieces[1] becomes <CELL_NO>:<MESSAGE ID>
					//	$pieces = explode(":",$pieces);
						$number = ltrim($numberlist,'+');//($pieces[0]);
						$query = "SELECT id, ref_id FROM user WHERE number = $number";
						if($result =  $db->get_row($query)){;	
							//update that they have recieved and ad					
							
                            if($is_advert){
                                $update = "UPDATE user SET ads_received_week = (1 + ads_received_week), ads_received_total = (1 + ads_received_total), " . 
    						" WHERE id = " . $result->id;
    							$db->query($update);
    							//reward their referals, if they exist
    							if($GLOBALS['discount']){//NOOOO THIS SHOULD ONLY GIVE A DISCOUNT IF I'M THE ONE WHO REFERED THEM - 
    								$profit = PROFITS_WITH_DISCOUNT;
    							}else{
    								$profit = PROFITS_WITHOUT_DISCOUNT;	
    							}
    							if($result->ref_id != 0){ //referal exists
    								$update = "UPDATE user  SET total_profits_generated = (total_profits_generated + $profit) AND" .
    								" total_profits_generated_unrewarded = (total_profits_generated_unrewarded + $profit) WHERE id = " . $result->ref_id;
    								$db->query($update);
    							}
                            }
						}else{//the number that we sent to (rather the response) does not exist in our database
							echo "DEBUG: THE NUMBER THAT WAS CONFIRMED IS NOT IN MY DATABASE - MESSAGE SEND SUCCESSFUL, BUT UPDATE TO DB NOT - NO: $number";	
						}
					//}
				//}
			}else{//some sort of error happend
				echo "Error in sending message: ".$results;
			}
		}
	}else{
		echo('no users were found to advertise to');	
	}
}


function return_user_query($total_count,  $limit=""){
		if( $_GET['sexmale'] == 'true'){
			$sexvar = " sex = 1";	
		}
		if( $_GET['sexfemale']=='true'){
			$sexvar = " sex = 2";
		}
		if($_GET['sexmale'] == $_GET['sexfemale']){//both unchecked or both checked means sex don't matter
			$sexvar = " (sex = 0 OR sex = 1 OR sex = 2)";//unknown, male or female
		}
		
		$location_clause = ($_GET['location'] == "")? "" : " AND location = '" . $_GET['location'] . "'";
		


		if(!isset($_GET['ref_opt'])) $_GET['ref_opt'] = 0;
		if(!isset($_GET['ref'])) $_GET['ref'] = 0;		

		if($_GET['ref'] == '') $_GET['ref'] = 0;
		switch($_GET['ref_opt']){
			case 0: //ignore
				$referrer_clause = "";		
				break;
			case 1://include
				$referrer_clause = " ref_id = " . $_GET['ref'] . ' AND';
				break;
			case 2://exclude
				$referrer_clause = " ref_id != " . $_GET['ref'] . ' AND';
				break;
		}
		
		$whereclause = $referrer_clause . " ads_received_week <= " .MAX_ADS_PER_WEEK ." AND (age >= " . $_GET['agemin'] . " AND age <= " . $_GET['agemax'] . ") AND " . $sexvar   . $location_clause;
		
		if(!$total_count){
			
			$limit_clause = ($_GET['limit'] == "")? "" : " LIMIT ".$_GET['limit'];//must be placed outside the subquery for mysql to work
			
			$query = "SELECT id, ref_id, ads_received_week, ads_received_total, total_profits_generated, number, age, sex, location FROM user WHERE id IN";	
			$query_inner = "(SELECT id FROM user WHERE".$whereclause." ORDER BY RAND())";
			$query = $query.$query_inner . $limit_clause;
		}else{
			$query = "SELECT count(*) FROM user WHERE";	
			$query .= $whereclause.$limit;
		}
		

				
//		echo ('query: ' . $query .' end query'); // WARNING - ECHOING ANYTHING HERE CAN CAUSE AN ERROR WITH THE AJAX
		return($query);	
		
	}
	
	function getRowCount() {
		global $db;
		$strSQL = return_user_query(true);
		$count = $db->get_var($strSQL);
		echo $count;
	}
	
	function getRows() {
		$start_row = isset($_GET['start'])?$_GET['start']:0;
		$start_row = 10 * (int)$start_row;
		
		$users = loadUsers($start_row);
		
		$formatted_users = "<div id='formatted_users'>" . formatData($users) . "</div>";
		
		echo   $formatted_users;
	}
	
	function loadUsers($start_row = 0) {
		global $db;
		$strSQL = "";
		if($start_row != "") $strSQL = " LIMIT {$start_row}, 10";
		$query = return_user_query(false, $strSQL);
		$users = "No results";
		if($results =  $db->get_results($query)){;	
			$users = array();
			foreach($results as $result){
				$users[] = $result;
			}
		}
		return ($users);
		
	}
	
	
	
	function formatData($users) {
		$formatted = '<br>';
		if(is_array($users)){
			foreach ($users as $user) {
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
				$formatted .= '<p>' . "$user->number - age: $user->age - sex: $user->sex - location: $user->location" . '</p>';
			}
		}else{
			$formatted = $users;
		}
		return ($formatted);
	}
	
	
function get_users_to_send_join_confirmation($number = ""){
	//it searches the database for users who haven't been sent a join confirmation, and have been members for a full day, then confirms them.
	
	if($number == ""){ //this cron only runs once per day and messages anyone who we haven't confirmed joining
		global $db;
		$query = "SELECT id, number FROM user WHERE join_confirmed = 0 AND CURRENT_DATE() - DATE(update_time) >= 1";
		$results = $db->get_results($query);
		foreach($results as $result){
			$tomessage['message'] =  str_replace("*replace_code*",convert_id_to_aff_id($result->id),MESSAGE_CONFIRM_JOINED);
			$tomessage['number'] = $result->number;
			confirm_joining($tomessage);
		}
	}else{
		$tomessage['message'] =  str_replace("*replace_code*",convert_id_to_aff_id($number['id']),MESSAGE_CONFIRM_JOINED);
		$tomessage['number'] = $number['number'];
		confirm_joining($tomessage);
	}
}

function confirm_joining($user_and_message){//tell the user welcome, and get them to tell their friends. This is also called by get_users_to_send_join_confirmation, or parse_user_data
	//remember to update join_confirmed
		global $db;
                $user_and_message=format_message($user_and_message);
		sms_user($user_and_message, false);
		$query = "UPDATE user SET join_confirmed = 1 WHERE  number = " . $user_and_message->number;
		$db->query($query);
}

function reset_and_check_rewards(){
	//called by the weekly cron to go through each user.
	/*
	1. reset advertised_to counts to zero (the one that keeps us from oversending to users weekly
	2. if profits generated is > than x, then reward the user, and reset count to zero
	*/
	global $db;
	$query = "SELECT number,total_profits_generated_unrewarded, reward_dump WHERE total_profits_generated_unrewarded > " . MINIMUM_REWARD_LEVEL;	
	$results = $db->query($query);
	foreach($results as $result){
		$query = "UPDATE user SET total_profits_generated_unrewarded = 0, reward_dump = " . ($result->reward_dump + $result->total_profits_generated_unrewarded)
		 . " WHERE number = " . $result->number;
		$db->query($query);//reset the reward count to zero and dump the total rewards to reward_dump - so these can be rewarded manually
	}
	$query = "UPDATE user SET ads_received_week = 0";
	$db->query($query);
	return(true);
}
	
?>