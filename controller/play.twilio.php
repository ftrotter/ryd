<?php
/**
 * contains the play twilio controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Twilio.class.php');
require_once('../model/User.class.php');
require_once('../model/Recording.class.php');
require_once("../util/PhoneFormat.php");

/**
 * This twilio controller provides the twilio functions needed to playback recordings 
 * using just your phone.
 * @package YDA
 */
class Twilio_play extends Twilio{

/**
 * choose. allows the user to choose which recording to play over twilio.
 * replaces twilio_chose.php
 */
	function choose(){

		$caller = $_REQUEST['Caller']; 

		$caller = PhoneFormat::forStorage($caller);
		//syslog(LOG_INFO,"YDA: twilio_called.php: called with $caller");

		$caller_hash = hash("sha512", $caller);

		$recording_search_sql = "
SELECT 
phones.id as phone_id,
phones.phone_hash,
phones.user_id,
users.name,
users.pin_code,
recording.id as recording_id,
recording.recording_number as recording_number,
recording.name
 FROM `phones` 
JOIN users ON users.id = phones.user_id
LEFT JOIN recording ON recording.user_id = users.id
WHERE `phone_hash` = '$caller_hash' AND active = 1
ORDER BY recording_id DESC
";

		$result = mysql_query($recording_search_sql) or die("Could not do sql $recording_search_sql error :".mysql_error());

		$count = 0;
		$recording_array = array();
		$pin_code = 0;
		while($row = mysql_fetch_assoc($result)){
			//then we found the phone number...
			$count++;
			$user_id = $row['user_id'];
			$recording_array[]	= $row;	
			$pin_code = $row['pin_code'];

		}

		$base_url = $GLOBALS['base_url'];

		$entered_code = -1;

		if(isset($_POST['Digits'])){
			$entered_code = $_POST['Digits'];
		}

		if(isset($_GET['pin_code'])){
			$entered_code = $_GET['pin_code'];
		}


		if($pin_code != 0){

			if($pin_code == $entered_code){
				//pin code accepted
				//move on to listing the recordings...
				$need_to_pin = false;
			}else{
				$need_to_pin = true;
			}
		}else{
			//no pin code entered
			$need_to_pin = false;
		
		}


		if($need_to_pin){

			//we need to have the person enter their pin...
		$this->xml .= "
<Response>
  <Gather action='$base_url"."twilio.php/play/choose'  method='POST'>
        <Say voice='man'>
            Your account has a pin code.
	    Please use your keypad to enter the pin code followed by the pound sign.
	</Say>
	<Pause length='20'/>
  </Gather>
</Response>
";
			return;

		}


		if($count == 0){
			//TODO
			//no results
			//respond with play.. no results..

			$this->xml = "<Response>
    <Say>You do not have any recordings yet. Call the record phone number to create some.</Say>
</Response>";
			return;
		}


		$this->xml .= "
<Response>
  <Gather action='$base_url"."twilio.php/play/play?pin_code=$pin_code'  method='POST'>
        <Say voice='man'>
            You have $count recordings. 
	    Please use your keypad to enter the number of the recording you would like to hear, at any time, by entering the number followed by the pound sign.
	</Say>
	<Pause length='2'/>
";

		foreach($recording_array as $number => $recording){
			$human_number = $recording['recording_number'];
			$this->xml .= "<Say voice='man'> $human_number </Say> <Pause length='1'/>";
			$this->xml .= "<Say voice='man'>". $recording['name']." </Say> <Pause length='1'/>";

		}

		$this->xml .= "            
    </Gather>
    <Say voice='man'>We didn't receive any input. Goodbye!</Say>
</Response>";

	}//end choose function


/**
 * choose. allows the user to choose which recording to play over twilio.
 */
	function play(){

		if(isset($_POST['Caller'])){
			//then this is a call from twilio asking for instructions...
			//we need to use the POST array 
			//to reverse engineer
			//which recording the user wants from the "human number" 
			//that we played them in twilio_choose.php

			$pin_code = $_GET['pin_code'];

			$caller = $_REQUEST['Caller'];
			$caller = PhoneFormat::forStorage($caller);
			$human_number = $_REQUEST['Digits'];

			$caller_hash = hash("sha512", $caller);

			$recording_search_sql = "
SELECT 
phones.id as phone_id,
phones.phone_hash,
phones.user_id,
users.name,
recording.id as recording_id,
recording.recording_number as recording_number,
recording.name
 FROM `phones` 
JOIN users ON users.id = phones.user_id
LEFT JOIN recording ON recording.user_id = users.id
WHERE `phone_hash` = '$caller_hash' 
AND active = 1
AND recording.recording_number = $human_number
ORDER BY recording_id DESC
";
			$result = mysql_query($recording_search_sql) or die("Could not do sql $recording_search_sql error :".mysql_error());

			$url = $GLOBALS['base_url'];
			$redirect_url = $url."twilio.php/play/choose?pin_code=$pin_code";

			if($row = mysql_fetch_assoc($result)){
				//then we found the phone number...

				$recording_id_chosen = $row['recording_id'];
			}else{
 		  	$this->xml .= "
<Response>
	<Say>You do not have a recording with that number</Say>
	<Redirect>$redirect_url </Redirect>
</Response>
";
			return;

			}

			//we need this, because sometimes this twilio controller will
			//not use it...
		  	//header("content-type: text/xml");


	
			//lets bounce them to the other side of this script...
			//not that we are not using the twilio dispatcher for this...
			//this requires a different document type...
			//would love to find a way to ensure that this is only coming from 
			//twilio.... I have it... use the twilio auth that twilio already 
			//gave me! that is a secret that only twilio and I know...
			//and I do not need to embed in code...
			$twilio_key = $GLOBALS['twilio_AuthToken'];
			
			
			$full_url = $url."twilio_play.php?caller=$caller&amp;rec_id=$recording_id_chosen&amp;twilio_key=$twilio_key&amp;pin_code=$pin_code";

			//	$full_url = urlencode($full_url);
 		  	$this->xml .= "
<Response>
	<Play>$full_url</Play>
	<Redirect>$redirect_url </Redirect>
</Response>
";

		}else{// if POST Caller
			//no user id the phone is wonky
		}

	}// end function




	


}//end controller class


?>
