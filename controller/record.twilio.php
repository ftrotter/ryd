<?php
/**
 * contains the record twilio controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Twilio.class.php');
require_once('../model/User.class.php');
require_once('../model/Recording.class.php');
require_once('../model/Prephone.class.php');
require_once("../util/PhoneFormat.php");
/**
 * This is the template Controller
 * @package YDA
 */
class Twilio_record extends Twilio{


/**
 * start. This action plays the "hi! start recording" message when the twilio number is dialed
 * it looks up the user using a hash of their caller id, and then plays the message
 * and refers twilio to the encode action to download the resulting mp3 file
 * replaces the old twilio_record.php file
 */
	function start(){

		$caller = $_REQUEST['Caller']; 

		$caller = PhoneFormat::forStorage($caller);

//syslog(LOG_INFO,"YDA: twilio_record.php: searching for: $caller");
//we probably do not need to query for the plaintext phone number anymore.
		$phone_search_sql = "
SELECT 
phones.id as phone_id,
phones.phone,
phones.user_id,
users.name
 FROM `phones` 
JOIN users ON users.id = phones.user_id
WHERE `phones`.`phone` = '$caller' AND active = 1
";

		$result = mysql_query($phone_search_sql) or die("Could not do sql $phone_search_sql error :".mysql_error());


		if($row = mysql_fetch_assoc($result)){
			//then we found the phone number...
			$this->record_call($caller);
			//syslog(LOG_INFO,"YDA: twilio_record.php: Caller found trying to record: $caller");
		}else{// start hashed search

			$hashed_caller = hash("sha512", $caller);

			$hashed_search_sql = "
SELECT 
phones.id as phone_id,
phones.phone_hash,
phones.user_id,
users.email,
users.name
 FROM `phones` 
JOIN users ON users.id = phones.user_id
WHERE `phone_hash` = '$hashed_caller' AND active = 1
";

			$result = mysql_query($hashed_search_sql) 
				or die("Could not do sql $hashed_search_sql error :".mysql_error());

			if($row = mysql_fetch_assoc($result)){
			//then we found the phone number...

				$user_id = $row['user_id'];
				//now we need to enforce the recording limitation...
				//for now lets just complain...
				$check_count_sql = "SELECT COUNT(*) as total_recordings, recording_limit  FROM `recording` 
JOIN users on recording.user_id = users.id
WHERE `user_id` = $user_id
GROUP BY recording.user_id";
				$count_result = mysql_query($check_count_sql) 
					or die("Could not do sql $check_count_sql error :".mysql_error());
				if($row = mysql_fetch_assoc($count_result)){
				
					$total_allowed = $row['recording_limit'];
					if($total_allowed == 0){
						//that means unlimited...
						$this->record_call($caller);

					}else{
				
						$total = $row['total_recordings'];
						if($total > $total_allowed){
							$this->record_call_with_warning($caller,$total,$total_allowed);
						}else{
							$this->record_call($caller);

						}
					}


				}else{
				//no results means no recordings... no problem
				$this->record_call($caller);
				//syslog(LOG_INFO,"YDA: twilio_record.php: Caller found trying to record: $caller");
				}
			}else{

				//$this->play_signup($caller);
				//old way... now we run the logic to let you record a call before signing up...
				$this->record_call_without_user($caller);
			}
		}//end else for hashed search

//TODO how do I detect blocked caller id??


	} // end start

/**
 * play_signup appends the xml to say "we do not recognize your number"
 */
	function play_signup($caller){

		$base_url = $GLOBALS['base_url'];

		$response =  "
<Response>
    <Play>$base_url"."mp3/not_recognized.mp3</Play>
	<Say>$caller </Say>
</Response>
";
		$this->xml .= $response;
	
	} // end play_signup

/**
 * record_call appends the xml needed to say "great we know you... talk and we will record"
 */
	function record_call_with_warning($caller,$total,$total_allowed){

	$base_url = $GLOBALS['base_url'];	

		$response =  "
<Response>
    <Play>$base_url"."mp3/welcome.4.wav </Play>
    <Say> You have $total recordings, but your subscription is limited to $total_allowed. Please use the web interface to delete old recordings. We will let you make this recording because we are nice people, enjoy. </Say>
    <Record action='$base_url"."twilio.php/record/encode' timeout='90' />
</Response>
";

//this is a version that uses the twilio text-to-speach engine.
/*
		$response =  '
<Response>
    <Say>Welcome to Record Your Doc. You can begin recording now</Say>
    <Record action="https://record.synseer.net/twilio_called.php"  />
</Response>';
*/

		$this->xml .= $response;

	}// end record_call


/**
 * record_call appends the xml needed to say "great we know you... talk and we will record"
 */
	function record_call_without_user($caller){
	
	//This function should warn users that
	//their recordings will be erased if they do not login soon enough
	//this function should also warn the user if they have reached their pre-recording limit


	$base_url = $GLOBALS['base_url'];	

/*
		$response =  "
<Response>
    <Play>$base_url"."mp3/nouser.1.wav </Play>
    <Record action='$base_url"."twilio.php/record/encodenouser' timeout='90'  />
</Response>
";
*/
//this is a version that uses the twilio text-to-speach engine.

		$response =  "
<Response>
    <Play>$base_url"."mp3/not_recognized.1.wav </Play>
    <Record action='$base_url"."twilio.php/record/encodenouser' timeout='90'  />
</Response>";


		$this->xml .= $response;

	}// end record_call

/**
 * record_call appends the xml needed to say "great we know you... talk and we will record"
 */
	function record_call($caller){

	$base_url = $GLOBALS['base_url'];	


		$response =  "
<Response>
    <Play>$base_url"."mp3/welcome.4.wav </Play>
    <Record action='$base_url"."twilio.php/record/encode' timeout='90'  />
</Response>
";

//this is a version that uses the twilio text-to-speach engine.
/*
		$response =  '
<Response>
    <Say>Welcome to Record Your Doc. You can begin recording now</Say>
    <Record action="https://record.synseer.net/twilio_called.php"  />
</Response>';
*/

		$this->xml .= $response;

	}// end record_call


/**
 * encode. This action is called by twilio after the start function starts a recording
 * this will happen after twilio has created an mp3 of the file and made it available
 * to just us, using a random url. We download it, encrypted it for the user and whoever the
 * user is globally sharing with, and then delete the twilio copy. replaces the old twilio_called.php
 */
	function encode(){

		$caller = $_REQUEST['Caller']; 

		$caller = PhoneFormat::forStorage($caller);
		//syslog(LOG_INFO,"YDA: twilio_called.php: called with $caller");

		$phone_search_sql = "
SELECT 
phones.id as phone_id,
phones.phone,
phones.user_id,
users.name
 FROM `phones` 
JOIN users ON users.id = phones.user_id
WHERE `phones`.`phone` = '$caller' AND active = 1
";

		$result = mysql_query($phone_search_sql) or die("Could not do sql $phone_search_sql error :".mysql_error());

		$found = false;//we have two ways of searching now...

		if($row = mysql_fetch_assoc($result)){
			//then we found the phone number...
			$found = true;
		}else{
			$hashed_caller = hash("sha512", $caller);

			$hashed_search_sql = "
SELECT 
phones.id as phone_id,
phones.phone_hash,
phones.user_id,
users.email,
users.name
 FROM `phones` 
JOIN users ON users.id = phones.user_id
WHERE `phone_hash` = '$hashed_caller' AND active = 1
";

			$result = mysql_query($hashed_search_sql) or die("Could not do sql $phone_search_sql error :".mysql_error());

			if($row = mysql_fetch_assoc($result)){

				$found = true;
			}
		}

		if($found){
			$user_id = $row['user_id'];	

		        $file = 'record_information.txt';
			$file = $GLOBALS['tmp_dir'] . $file;

			$mp3_file = 'downloaded.mp3';
			$mp3_file = $GLOBALS['tmp_dir'] . $mp3_file;
	
		        $fh = fopen($file, 'w');
		        $results = var_export($_REQUEST,true);
		        fwrite($fh,$results);

			$mp3_url = $_REQUEST['RecordingUrl'] . ".mp3";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $mp3_url);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//for when we use CACert.org certs for testing...
			ob_start();
			$data = curl_exec($ch);
			ob_end_clean();
	
			$recording = new Recording();
			$recording->user_id = $user_id;
			$recording->source = 'twilio';
			$recording->deleted = 0;
	
			$recording->save();//will generate an id;
			$id = $recording->recording_id;
		
			$new_file_name = calculate_recording_path($user_id,$id);

			file_put_contents($new_file_name, $data); 

			$recording->initialEncryptFileUser();

			//TODO delete recordings from twilio here

		}else{
			//then we have a recording happening from a non-user...
			//how is this possible given that we filtered already 
			//from our database in the twilio init script??

			syslog(LOG_ERROR,"YDA: twilio_called.php: Caller not found $caller");


		}

		//just something to let twilio know we still love them.
   		$this->xml .= "<Response></Response>";
	}// end encode function

/**
 * encodenouser. This action is called by twilio when someone is recording before signing up for an account..
 * 
 */
	function encodenouser(){

		syslog(LOG_INFO,"record.twilio.php: we get here...");

		$caller = $_REQUEST['Caller']; 

		$caller = PhoneFormat::forStorage($caller);
		//syslog(LOG_INFO,"YDA: twilio_called.php: called with $caller");

		        $file = 'record_information.txt';
			$file = $GLOBALS['tmp_dir'] . $file;

			$mp3_file = 'downloaded.mp3';
			$mp3_file = $GLOBALS['tmp_dir'] . $mp3_file;
	
		        $fh = fopen($file, 'w');
		        $results = var_export($_REQUEST,true);
		        fwrite($fh,$results);

			$mp3_url = $_REQUEST['RecordingUrl'] . ".mp3";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $mp3_url);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//for when we use CACert.org certs for testing...
			ob_start();
			$data = curl_exec($ch);
			ob_end_clean();
	
			$recording = new Recording();
			$recording->user_id = 0;
			$recording->source = 'prephone';
			$recording->deleted = 0;
	
			$recording->save();//will generate an id;
			$id = $recording->recording_id;

			$prephone = new Prephone($caller);
			$prephone->save();
			$prephone_id = $prephone->prephone_id;			
			$prephone->addRecording($recording->recording_id);	

			$dir = "prephone_".$prephone_id;

			$new_file_name = calculate_recording_path($dir,$id);

			file_put_contents($new_file_name, $data); 
			syslog(LOG_INFO,"record.twilio.php: file contents should have been populated in $new_file_name and dir is $dir");
			
			//encrypt with the phone number
			//yep.. its crappy... but there is no
			//other way to make a reminder...
			$sym_key = $recording->initialEncryptFile($dir,$prephone->sym_key);

			//TODO delete recordings from twilio here


		//just something to let twilio know we still love them.
   		$this->xml .= "<Response></Response>";
	}// end encode function






}//end controller class


?>
