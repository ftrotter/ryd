<?php

	require_once('../util/functions.php');
	require_once('../config.php');
	require_once("../util/PhoneFormat.php");
	require_once("../util/twilio/twilio.php");

	// Cookie work
	//if the cookie for the user_key has been set... transfer it to the session and 
	//postpone its expiration for another ten years...


	// URI work
	$uri_no_gets = explode('?',$_SERVER['REQUEST_URI']);	
	$parts_array = explode('/',$uri_no_gets[0]);
	//instatiate to avoid warnings...
	$uri_array = array();
	$controller_name = '';
	$action_name = '';
	$medium_name = '';
	foreach($parts_array as $value){
		if(strlen($value)>1){
			$uri_array[] = $value;
		}else{
			//nothing erasing all of the '' values
		}
	}
	
	if(count($uri_array) > 0){
	if(strcmp($uri_array[0],'twilio.php')==0){

		//then we are not using a re-writing rule...
		
		array_shift($uri_array); //gets rid of the index.php	

	}else{
		if(strlen($uri_array[1])==0){
			//default link
			//this should be login dependant...
		}else{
			echo "ERROR: we should never use a url without twilio.php in it";
			exit();
		}
	}//if str cmp
	}//if count uri_array

	//so there are two levels for twilio b/c we only one "medium" and that 
	//it twilio XML

	//we should always have the form of 
	// twilio controller and function
	//we will never assume a function like we do in index.php	

	if(count($uri_array) >= 2){
		$twilio_controller_name = $uri_array[0];	
		$action_name = $uri_array[1];	
	}else{
		echo "ERROR: Everything must be of the form twilio.php/twiliocontroller/action";
		exit();
	}


	$GLOBALS['twilio_name'] = $twilio_controller_name;
	$GLOBALS['action_name'] = $action_name;

	//skype hack
	//replace skype_hack.php
	if($_REQUEST['Caller'] == '2025808200'){
		$_REQUEST['Caller'] = '7138933361';
	}

	//twilio security check...
	//create the util object using the globals pulled from the yaml file
	$twilioUtil = new TwilioUtils(
			$GLOBALS['twilio_AccountSid'],
			$GLOBALS['twilio_AuthToken']
				);



	$url = curPageUrl();    
    	if(isset($_POST)) {
		// copy the post data
		$data = $_POST;
    	}
	
	//twilio sends a special header with requests that should live 
	//in this server variable....
	//we will see..
	$expected_signature = '';
	if(!isset($_SERVER["HTTP_X_TWILIO_SIGNATURE"])){
		$valid = false;
		$valid_message = "no signature";
	}else{	
		$expected_signature = $_SERVER["HTTP_X_TWILIO_SIGNATURE"];	
		if($twilioUtil->validateRequest($expected_signature, $url, $data)){
			$valid = true;
		}else{
			$valid = false;
			$valid_message = 'validation fail';
		}
	}

	$debug = false;
	
	if(!$valid && !$debug){
		//then it is not a valid request AND we are not debugging
		//then this is an attack
		$attacking_ip = $_SERVER['REMOTE_ADDR'];
		$request_dump = var_export($_POST,true);
		$url = curPageURL();
		syslog(LOG_INFO,"RYD: twilio.php: hacking attempt from: $attacking_ip with POST contents of: $request_dump");
		$sig = $twilioUtil->calculateSignature($url,$data);
		echo "naughty naughty expected: $expected_signature but got $sig<br><br>\n\n$request_dump<br><br>\n\n$url";
		//this exit prevents un-twilio access.
		exit();
	}
		
	if($debug){
		
		$calling_ip = $_SERVER['REMOTE_ADDR'];
		$request_dump = var_export($_POST,true);
		if($valid){
			$valid_text = 'twilio validates';
		}else{
			$valid_text = 'not from twilio';
		}

		syslog(LOG_INFO,"RYD: twilio.php: $valid_text debug called from $calling_ip with POST contents of: $request_dump");
		//here we continue processing...

	}
	




	//other always twilio stuff...



	$controller = $GLOBALS['base_dir']."controller/$twilio_controller_name.twilio.php";
	if(file_exists($controller)){
		require_once($controller);
		//we name the Controller classes this way so that they sort well in the documentation...
		$controller_full_name = "Twilio_" .$twilio_controller_name;
		$ControllerObject = new $controller_full_name;
		$ControllerObject->$action_name();
		echo $ControllerObject;
	}else{
		echo "ERROR: no such controller as $controller_name looked in $controller";
		exit();
	}



?>
