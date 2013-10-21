<?php
/**
 * contains the account controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */
require_once("../config.php");
require_once('Controller.class.php');
require_once("../util/PhoneFormat.php");
require_once("../util/twilio/twilio.php");
require_once("../model/User.class.php");
require_once("../model/Prephone.class.php");

/**
 * The account controller
 * @package YDA
 */
class Controller_account extends Controller{

/**
 * Typical constructor
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - Manage Your Account");

		//sets the main menu tab to be active
		$this->data['main_menu']['Account']['active'] = true;


	}
/**
 * The index displays the basic menu for all of the things that you can
 * Do with the account controller. Otherwise does nothing. 
 * @todo Move to using the submenu for displaying options?
 */
	function index(){
		
		$hybrid_link = "/index.php/hybridauth/index/";
		$logout_link = "/index.php/account/logout/";
		$phone_link = "/index.php/account/manage_phones/";
		$subscribe_link = "/index.php/account/subscribe/";
		$pin_link = "/index.php/account/manage_pin/";


		$user = new User($_SESSION['user_id']);
		//TODO can I prettify and still be language neutral?
		$good_til = $user->pay_good_til;

			

		$this->data['links']['Manage Logins'] = $hybrid_link; 
		$this->data['links']['Logout'] = $logout_link; 
		$this->data['links']['Manage Phones'] = $phone_link; 
		$this->data['links']['Manage Pin Code'] = $pin_link; 



		if($user->paid){
			$this->data['good_til'] = $good_til;
			$this->data['paid'] = true;
		}else{
			$this->data['paid'] = false;
			$this->data['links']['Recording Subscription'] = $subscribe_link;
			$this->data['upgrade_link'] = $subscribe_link;
	
		}
	}


	function deleteMyself(){
		//this function allows a user to delete themselves after a confirmation
		
		if(isset($_POST['really'])){

			$User = new User($_SESSION['user_id']);
			$User->delete();
			bounce("/index.php/account/logout/",10);
			$this->data['deleted'] = true;
		}else{
			$this->data['deleted'] = false;
		}
	}


	function home(){
		//this function does nothing. It a stub so that we can show the interface...
	
	}

/**
 * This function allows a user to logout, by clearing the Session. 
 * and returning to the login
 *
 * @todo nothing
 */
	function logout(){

		$_SESSION = array();
		$login_url = "/index.php/login/index/";
		bounce($login_url,0);
		exit();
		//which is what actually logs us out
	
		//old way...
		/*

		if(isset($_GET['auto_logout'])){
			$time_out = 300;
		}else{
			$time_out = 0;
		}
		$login_url = "/index.php/login/index/";
		bounce($login_url,$time_out);
		if($time_out != 0){
			echo "You have been logged out due to inactivity. Returning you to the <a href='$login_url'>login screen</a> in thirty seconds.";
		}
		exit();
		*/

	}


/**
 * The phone playback system has an optional pin code. This interface manages that pin code. 
 *
 * @todo nothing
 */
	function manage_pin(){

		if(isset($_SESSION['user_id'])){
			$user = new User($_SESSION['user_id']);
		}else{
			echo "Error: the session needs a user_id for account/manage_pin to work. How did you get here without one?";
			exit();
		}
		if($user->pin_code == 0){
			$this->data['pin_code'] = '';
		}else{
			$this->data['pin_code'] = $user->pin_code;
		}

		if(isset($_POST['pin_code'])){
			//the user is sure, delete thier phone
			if(strlen($_POST['pin_code']) == 0){
		
				echo "Error: you need to have a pin code with at least one digit please press the back buttong and try again."; 
				exit();
			}

			$user->pin_code = $_POST['pin_code'];		
			$this->data['pin_code_set'] = true;
			$this->data['pin_code'] = $_POST['pin_code'];


			if(!isset($_POST['use_pin'])){
				//then we are actually trying to turn -off- the pin...
				$user->pin_code = 0;
				$this->data['pin_code'] = '0';
				$this->data['pin_code_set'] = false;

			}
			$user->save();
		}
	}


/**
 * Delete Phone allows a user to delete a phone number from the account.
 * the "are you sure" stage is important here, since we do not actually
 * store a real phone number, but instead a copy of the phone encrypted 
 * by the users private key. If they delete it, there is no undelete.
 *
 * @todo nothing
 */
	function delete_phone(){

		$user = new User($_SESSION['user_id']);

		if(isset($_POST['sure'])){
			//the user is sure, delete thier phone
			$user->deletePhone($_POST['phone_id']);
			$this->data['deleted'] = true; 

		}else{
			if(!isset($_GET['id'])){
				echo "account.controller.php: you have to have an id here... ";
				exit();
			}

			$phone_id = $_GET['id'];		

			$this->data['phone'] = $user->decryptPhone($phone_id,$_SESSION['user_key']);
			$this->data['phone_id'] = $phone_id;

		}
		
	}


/**
 * This is the start page for the subscription page.
 *
 */
	function subscribe(){

		//nothing yet...
		//just say thanks in the view
		//remove the nag...
		$app_name = $GLOBALS['app_name'];
		$this->data['header_message'] = "";



		if(isset($_REQUEST['code'])){
	
			$users_code = $_REQUEST['code'];			

			$good_codes = $GLOBALS['subscription_codes'];

			if(in_array($users_code,$good_codes)){
				//then this is a good code...
				$this->data['good_code'] = $users_code;
				//we need to prevent the prompt system from immediately kicking in...
				//so we set the prompted session key to prevent that...
				$_SESSION['prompted']['no_phone'] = true;
				$user = new User($_SESSION['user_id']);
				$user->paidUntil('code',$users_code);
				$user->save();
				//we need to override the warning but in the header
				//message by the main controller class...
				$app_name = $GLOBALS['app_name'];
				$this->data['header_message'] = "<h4>Thanks for subscribing to $app_name!! </h4>";
			}else{
				$this->data['bad_code'] = $users_code;
				$email = $_SESSION['user_id'];
				syslog(LOG_INFO,"account.controller.php: $email failed to subscribe with code $users_code");
			}

		}


	}

/**
 * This is the landing page when paying from paypal..
 *
 */
	function paypal_landing(){

		//paypal broke DST for subscriptions.
		// it removes the GET variables I put in the return URL
		// WTF?
			$limit = 0;
			$good_till = date('Y-m-d', strtotime("13 months"));
			$user = new User($_SESSION['user_id']);
			$user->paidUntil('paypal',"mostly unlimited",$good_till,$limit);
			$user->save();		

	}


/**
 * This is the landing page when paying from paypal..
 *
 */
	function paypal_landing_lite(){

		//paypal broke DST for subscriptions.
		// it removes the GET variables I put in the return URL
		// WTF?
			$limit = 10;
			$good_till = date('Y-m-d', strtotime("13 months"));
			$user = new User($_SESSION['user_id']);
			$user->paidUntil('paypal',"try it out",$good_till,$limit);
			$user->save();		

	}

/**
 * This is the landing page when canceling from paypal..
 *
 */
	function paypal_cancel(){

		//nothing yet...
		//just say you did not buy in the view...
	}

/**
 * This is the last stage of the adding a phone. It has to be a seperate URL so that the local phone
 * call-in numbers will display correctly... basically this exists so that we get a fresh MySQL query... 
 *
 * @todo nothing
 */
	function phones_success(){



	}
/**
 * Manage phones handles the form which steps through adding a phone to the users account.
 * For now phones must be added only to one account. If you attempt to add a phone and the system 
 * already knows about the phone, the adding process will die. Without this in place,
 * it might be possible for someone to trick a user to adding thier phone to the account,
 * allowing them to have access to recordings through the call-in function. This limits that attack
 * to working only on one user. Also the call-in function relies on only having a one-phone to one-account
 * mapping. Its just a nightmare to think about how to do one-phone to many-accounts.
 *
 * @todo nothing
 */
	function manage_phones(){

		//adding phones is a privilege for subscribers
		//this is the mechanism that we use to prevent
		//users from adding a phone blind, circumventing 
		//the change in the view.
		//TODO users who have already added the phone number in the system
		if(!$this->user->paid){
			return;
		}

		//without a phone added, there is a header_message nag, that we should remove
		//from just this page
		//this will also remove the header_message when we add a second phone...
		// oh well.
		$app_name = $GLOBALS['app_name'];
		$this->data['header_message'] = "";


		$current_user_email = $_SESSION['user_id'];
		$user = new User($current_user_email);

		if(isset($_POST['pin'])){
			if($_SESSION['pin'] == $_POST['pin']){

				$phone = $_SESSION['phone_for_pin'];	
				 
				//then we should add this phone to the users account!! 
				
				$phone_used_by = $user->phoneUsed($phone);
				if($phone_used_by){	//this user has the other users phone and is overriding... 
					//send an email to the previous user
					
					$security_email = $GLOBALS['security_email'];
					$text_body = "Another user of $app_name has claimed the phone number $phone. The new users email is $current_user_email. If you do not know this person, and you believe that this person may have stolen your phone, please contact the security administrator for $app_name at $security_email, immediately. 

Thanks,
The $app_name team.
  ";

					$html_body = nl2br($text_body);				

					require_once("../model/Email.class.php");
					$mail = new Email();
					$mail->FromName = "$app_name Invitation";
					$mail->AddAddress($phone_used_by);                  // name is optional
					$mail->Subject = "Another account has claimed your $app_name phone number";
					$mail->Body    = $html_body;
					$mail->AltBody = $text_body;
					$mail->send();

					$previous_user = new User($phone_used_by);
					$phone_id = $previous_user->getPhoneIdFromPhone($phone);
					$previous_user->deletePhone($phone_id);

				}	

				$user->addPhone($phone,$_SESSION['user_key']);
		
				$prephone = new Prephone($phone);
				//getting upload outputss
				ob_start();
				$prephone->migrateToUser($user->user_id);
				ob_flush();					
				
				$this->data['success'] = true;
				bounce('/index.php/account/phones_success/',0);
				exit();
				

			}else{
			//	echo "Session = ".$_SESSION['pin'];
			//	echo "POST = ".$_POST['pin'];
				$this->data['wrongpin'] = true;
			}	

		}

		if(isset($_POST['phone'])){
			$phone = $_POST['phone'];
			$this->data['phone'] = $phone;
			//Check availability of that phone!!
			//we cannot allow duplicates...

			$phone_used_by = $user->phoneUsed($phone);//returns either false for a free phone or the email of the person who currently uses it
			if($phone_used_by){
				//each phone must be associated with only one account.
				if(!isset($_POST['override'])){ //if we do not have an override, diplay the override form
					$this->data['phoneused'] = $phone;
					return;
				}else{

				}
			}
			
			//creat a pin and add it to the session

			$pin = rand(1111,9999); //random four digit pine	
			$_SESSION['pin'] = $pin;
			$_SESSION['phone_for_pin'] = $phone;

			$url = $GLOBALS['base_url'] . "twilio.php/auth/pin?pin=$pin";

			$client = new TwilioRestClient(
				$GLOBALS['twilio_AccountSid'], 
				$GLOBALS['twilio_AuthToken']);

			    $data = array(
    				"Caller" => $GLOBALS['twilio_CallerId'], 	      // Outgoing Caller ID
    				"Called" => $phone,	  // The phone number you wish to dial
    				"Url" => $url
    				);

			$AccountSid = $GLOBALS['twilio_AccountSid'];
			$ApiVersion = "2008-08-01";

    			$response = $client->request(
				"/$ApiVersion/Accounts/$AccountSid/Calls", 
       				"POST", 
				$data); 
    
	    	// check response for success or error
    			if($response->IsError){
    				echo "Error starting phone call: {$response->ErrorMessage}\n";
				exit();
   	 		}else{
    				$this->data['called'] = $phone;
			}
		}

		$this->data['phone_array'] = $user->listPhones($_SESSION['user_key']);
	

			//doing nothing will just show the initial form...

	}


}//end controller class


?>
