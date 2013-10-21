<?php
/**
 * contains the basic controller class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */
require_once('../util/PhoneFormat.php');
require_once('../model/User.class.php');
require_once("../model/HybridUserInstance.class.php");
/**
 * the base controller class. 
 * Establishes the data array that will be passed to the view.
 * Creates the various menu arrays that will be used by the medium to make menu container
 * 
 * @package YDA
 */
class Controller {
	//this is the default menu data...

    /**
     * The data array contains everything that is passed to the view and medium for display
     * @access public
     * @var array
     */
	var $data = array( 
	'main_menu' => 
		array(
			'Recordings' => array( 
				'url' => '/index.php/recordings/index',
				'active' => false),
			'Upload' => array(
				'url' => '/index.php/recordings/upload/',
				'active' => false),
			'Sharing' => array(
				'url' => '/index.php/sharing/index/',
				'active' => false),
			'Account' => array(
				'url' => '/index.php/account/index/',
				'active' => false),
			'Log out' => array(
				'url' => '/index.php/account/logout/',
				'active' => false),
		),
	
	'sub_menu' => false,
			
		
	); 

    /**
     * We create the user object almost everywhere, so we can save it here...
     * @access public
     * @var object
     */
	var $user;	


	
/**
 * This contructor handles portions of the interface that are always shown, like the 'message' area..
 * as well as handling basic 'have you paid' functionality issues...
 */
	function __construct(){

		$refresh =  0;

	$header_message = '';
 	mylogger("Starting Controller");
	$current_action = strtolower($GLOBALS['action_name']);

	if(strcmp($current_action,'logout') == 0){
		return;
	}

	if(isset($_SESSION['user_id'])){ //this controller still gets called when we are not logged in!!
 		mylogger("user_id is set");


		$user = new User($_SESSION['user_id']);
		if($user->user_id > 0){
			//then we have a saved user... 
			//do nothing and continue
 			mylogger("user known");
		}else{
			//then we have not yet completed the forwardings 
			//that save a user...
 			mylogger("unknown user creating, no more Controller");
			return;
		}


		//create dynamic menu items here
		$this->data['main_menu']['Feedback'] = array(
				'url' => $GLOBALS['feedback_url'],
				'active' => false);


		$header = $GLOBALS['head'];
		//include auto_logout code...
		$header->addJS("<script type='text/javascript' src='/js/auto_logout.js'></script>
");
		//and add it to the body text...
		$header->setBodyTag('<body onmousemove="reset_interval()" onclick="reset_interval()" onkeypress="reset_interval()" onscroll="reset_interval()">
');

		$user_id = $_SESSION['user_id'];
		$this->user = new User($user_id);
		if(HybridUserInstance::canUserShare($user_id)){
			//then this user can share
			$this->data['sharing_works'] = true;
			$this->data['user_print'] = $this->user->getEmail();
		}else{
			$this->data['sharing_works'] = false;
			$this->data['user_print'] = $this->user->phone;
		}
		
		//$header_message .= "logged in as $user_id";
		$user_id = $this->user->user_id;

		//EULA comes before all else
		if($this->user->eula_agree < $GLOBALS['EULA_VERSION']){

			$no_eula_actions = array(
					'logout',
					'prompt',
					'login'
				);
			if(strcmp($current_action,'eula') == 0){	
				return; // we just move forward
			}
			if(in_array($current_action,$no_eula_actions)){	
				//then we are already in the EULA agree stage..
				//we need to continue...
				//do nothing...
			}else{
				$eula_url = "/index.php/xhtmlsimple/login/eula";
                		bounce($eula_url,$refresh);
			//	echo "bouncing here to <a href='$eula_url'>$eula_url</a> ";
                		exit();
			}
        	}

        if(!$this->user->checkOpenidHash($_SESSION['user_key'])){
                //then we have an OpenID value that has changed
                //lets deal with it...

		$no_check_options = array(
			'openidfix',
			'viewkeys'
			);
		if(in_array($current_action,$no_check_options)){
			//ok then we are fixing it...
			//this will prevent a loop...
		}else{
			$refresh = 0;
	                bounce("/index.php/login/openidfix/",$refresh);
        	        exit();
		}
        }
	

		if($this->user->paid){//this user has paid!!

 		mylogger("user paid");
			

			$this->data['paid'] = true;
	
			$number_of_phones_sql = "SELECT count(`id`) as number_of_phones FROM `phones` WHERE `user_id` = $user_id";

			$result = mysql_query($number_of_phones_sql) or 
				die("xhtml.class.php: Error cannot get phone count for user with $number_of_phones_sql <br>".mysql_error());

			$row = mysql_fetch_assoc($result);
			if($row['number_of_phones'] > 0){
 				mylogger("user has phones");


				 $phone_array = $this->user->listPhones($_SESSION['user_key']);

				$use_local = true;
				$last_phone_id = 0;
				foreach($phone_array as $phone){
					$area = substr($phone,0,3);
					$local_phone_id = 0; //to start
					if(strlen($area) > 2){
					$local_phone_sql = "
SELECT *
FROM `inbound_numbers`
WHERE `area_code` = $area
";					
					$phone_result = mysql_query($local_phone_sql) or die("could not load numbers with $local_phone_sql".mysql_error());

					$row = mysql_fetch_assoc($phone_result);
					if(isset($row['id'])){
						//then we have a local phone..
						$local_phone_id = $row['id'];
						$local_record_number = $row['record_phone'];
						$local_play_number = $row['play_phone'];
						

						//but we need logic to check for people who have many phone numbers
						if($local_phone_id != $last_phone_id && $last_phone_id != 0){
							//then we have two different local numbers for this user
							//we will display the 1800 number...
							$use_local = false;
						}

						$last_phone_id  = $local_phone_id;
					}
					}//end if strlen
				}

				if($use_local && $local_phone_id != 0){
					//then we have an conflicting clean version of local phones for printing
					$GLOBALS['recording_phonenumber'] = $local_record_number;
					$this->data['recording_phonenumber'] = $local_record_number;
					$GLOBALS['play_phonenumber'] = $local_play_number;
					$this->data['play_phonenumber'] = $local_play_number;

				}
	
				$record_number = PhoneFormat::forPrinting($GLOBALS['recording_phonenumber']);
				$play_number = PhoneFormat::forPrinting($GLOBALS['play_phonenumber']);

				/*
				$header_message .= "
<h4> To record call $record_number <br>
 To listen call $play_number</h4>
";
				*/

			}else{
 				mylogger("user has no phones");
				//lets not show this if you are already going there!!..
				if(strpos($_SERVER["REQUEST_URI"],'manage_phones') === false){
					//manage_phones is not in the URI
					$add_phone_url = "/index.php/account/manage_phones/";
					$prompt_message = "<h4> To make recordings, you must add  <a href='$add_phone_url'>your cell phone number</a>.</h4>";
					prompt('no_phone',$prompt_message);
					$header_message = '';
				}else{
					//we are already at where we want to go... lets not prompt...
					$header_message = '';
				}

			}

		}else{//this user has not paid yet.. 
 			mylogger("user not paid");
			$this->data['paid'] = false;
			$app_name = $GLOBALS['app_name'];
			$subscribe_url = "/index.php/account/subscribe/";
			$logout_url = "/index.php/account/logout/";
			$record_url = "/index.php/recordings/index/";

			$show_middle_screen = false; //by default we do not show the middle screen for users...


			//by unsetting menu items
			//we ensure that they will only see what they are allowed to do
			//even after they move back to the normal interface...
			unset($this->data['main_menu']['Upload']);
			unset($this->data['main_menu']['Sharing']);

			//are other people sharing with me already? if so then I display the 
			// listen or upgrade message....
			$sharing_with_me = $this->user->whoSharesWithMe();

			if(count($sharing_with_me) > 0){//then someone is sharing with me...
 				mylogger("detected sharing");
					$prompt_message = "
<h4> Someone is sharing message with you. You can:</h4>
<p>
<ul>
 <li> <a href='$record_url'>Listen</a> to the messages that you have access to. 
	</li>

 <li> If you want to make your own recordings you must <a href='$subscribe_url'>subscribe</a>. </li>

</ul>
Once you subscribe, $app_name will allow you to make recordings by simply calling the Recording Phone Number. $app_name will recognize the number that you are calling from and use that information to securely add the recordings to your account. You will not need to enter any kind of code to start recording, just make the call, and the recording starts automatically. Later, after you have made the recordings, you will be able to play, manage and comment on your recordings through this web interface.<br>

</p>
";

			$show_middle_screen = true; //we show the middle screen when someone is sharing with this user...
	
			}else{// no one is sharing with this user...
 				mylogger("no one sharing with user");
			//no reason to look at the recordings menu item
				unset($this->data['main_menu']['Recordings']);

				$invited_by_list = $this->user->getWhoInvitedMe();
				$invited_by_html = '';
				$invited_html_header = '';
				foreach($invited_by_list as $id => $email){
					$invited_html_header = '<h4> Thank you for accepting an invitation to this site! </h4> <p>The following user(s) had invited you and will now be notified that you are now a user <ul>';
					$invited_by_html .= "<li>$email </li>\n";
					$show_middle_screen = true; //we show the middle screen when there are invites accepted
				}
				$invited_by_html = $invited_html_header . $invited_by_html . "</ul>";


			}

			if($show_middle_screen){
 				mylogger("showing middle screen");
				//we need to have a middle screen so that we can do something regarding invites and sharing
				$header_message  = '';
				prompt('paid',$prompt_message);

			}else{
				//not sure why this check does not occur in a more clean fashion..
				//oh well...
				$user = new User($_SESSION['user_id']);
				if(!$user->paid){
 				mylogger("no middle screen");
				//we just forward the user to subscribe, unless they are already subscribing
				if(strpos($_SERVER["REQUEST_URI"],'subscribe') === false){
 					mylogger("bouncing to subscribe");
					bounce_once($subscribe_url);
				}else{
					//just show the page...
 					mylogger("subscribe");

				}
				}
			}	
		}

		
		


		$this->data['header_message'] = $header_message;

	}//end if the user is logged in!!

	}			

}
?>
