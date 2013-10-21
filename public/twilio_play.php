<?php
/// This is a file intended to allow twilio and only twilio access
// to use phone numbers as a key to playback recordings that a user has made
// to do this, this file should check to make sure that it was called with the current twilio authtoken!!
// this file previously did both halves of the playback process, but now it just serves up files.



require_once("../config.php");
require_once('../model/User.class.php');
require_once('../model/Recording.class.php');
require_once('../util/PhoneFormat.php');
require_once("../config.php");

	header('Content-type: audio/mpeg');

	if(!isset($_GET['caller'])){
		echo "error... no phone number here!!";
		exit();
	}

	if(!isset($_GET['rec_id'])){
		echo "error... no recording id here!!";
		exit();
	}

	if(!isset($_GET['twilio_key'])){
		echo "error... no twilio key here!!";
		exit();
	}

	$twilio_key = $_GET['twilio_key'];
	$recording_id = $_GET['rec_id'];
	$caller = $_GET['caller'];

	if(strcmp($twilio_key,$GLOBALS['twilio_AuthToken']) != 0){
		//then the keys do not match and this is a potential attack...
		//this is the only defense against outsiders fuzzing for recordings
		//by testing phone numbers in bulk
		$attacking_ip = $_SERVER['REMOTE_ADDR'];
		syslog(LOG_INFO,"RYD: twilio_play.php: hacking attempt from: $attacking_ip ");
		echo "naughty naughty";
		exit();
	}


	$user_id = User::getUserFromPhone($caller);

	if($user_id){
		//$user = new User($user_id);
		$recording = new Recording($recording_id);
		$file_name = $recording->decryptFileFromPhone($caller,$user_id);
		$name = $recording->name;
	
			if(stristr($name,'.mp3') === FALSE){
				//mp3 not found in string
				$name = $name . ".mp3";
			}
			$attachment = 'attachement; ';
			$attachment = ' ';
			if(file_exists($file_name))
			{
    				header('Content-length: ' . filesize($file_name));
    				header("Content-Disposition: $attachment filename=\"$name\"");
    				header('Content-Transfer-Encoding: binary');
    				header('X-Pad: avoid browser bug');
    				Header('Cache-Control: no-cache');
    				ob_clean();
    				flush();    
    				readfile($file_name);
			}


			unlink($file_name);

			exit();
	}else{
		//no user id the phone is wonky

	}



?>
