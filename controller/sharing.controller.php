<?php
/**
 * contains the login controller.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */
require_once("../config.php");
require_once("../model/User.class.php");
require_once("../model/Email.class.php");
require_once("../model/Recording.class.php");
require_once("../model/HybridUserInstance.class.php");
require_once('Controller.class.php');

/**
 * The sharing controller handles the complexity of sharing recordings with other people.
 * @todo finish implementing sharing, see TODO
 * @package YDA
 */
class Controller_sharing extends Controller{

       /**
     	* Allows different functions to access the same to user object.
     	* @var object 
        */
	var $ToUser;

/**
 * Typical constructor.
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - Sharing");
		//sets the main menu tab to be active
		$this->data['main_menu']['Sharing']['active'] = true;


	}

/**
 * this internal function warns about the dangers of sharing unless the user has seen it once... (cookie test)
 */ 
	function _been_warned(){

		if(!isset($_COOKIE['sharing_warning'])){
			$warn_url = "/index.php/sharing/warn/";
			setcookie('sharing_warning',true);
			bounce($warn_url);	
			exit();
		}

	}

/**
 * this internal function warns about the dangers of sharing unless the user has seen it once... (cookie test)
 */ 
	function warn(){
		
		if(setcookie('sharing_warning',true)){


		}

	}

/**
 * Displays a list of the people you are sharing with, you can click to delete a sharing relationship
 * Or you can use the form at the top to add a new person..
 */
	function index(){
		$this->_been_warned();
		//this function should list all of the 
		//various sharing that this user will user
		$ThisUser = new User($_SESSION['user_id']);
		$this->data['to_users'] = $ThisUser->getSharingList();
		$this->data['new_users'] = $ThisUser->getInviteAddedList();
		$this->data['recordings_list'] = $ThisUser->getRecordingList();

	}

/**
 * Stop sharing with a given user
 */
	function stopsharing(){

		if(isset($_GET['user_id'])){
			$stop_user_id = $_GET['user_id'];
			$this->data['stop_user_id'] = $stop_user_id;
			$stop_user = new User($stop_user_id);
			$this->data['stop_user_name'] = $stop_user->name;

		}

		if(isset($_GET['recording_id'])){
			$recording_id = $_GET['recording_id'];
			$this->data['recording_id'] = $recording_id;
			$recording = new Recording($recording_id);
			$this->data['recording_name'] = $recording->name;
		}

		if(isset($_POST['sure'])){

			$user = new User($_SESSION['user_id']);
			$stop_user_id  = $_POST['stop_user_id'];
			if(isset($_POST['stop_all'])){				
				$user->stopSharing($stop_user_id);
			}
			if(isset($_POST['stop_one'])){				
				$recording_id  = $_POST['recording_id'];
				$user->stopSharing($stop_user_id,$recording_id);
			}
			$this->data['stopped'] = true;
		}
		
	}


/**
 * Share just one recording function with a user. called by users and also repeatedly by allrecordings
 */
	function onerecording($recording_id = 0,$to_user_id = false){
		//this function will share one recording with another user...	

		// First load the information about the recording...
		// things like the name...		
	
		// todo anything we need a recording id

		//this user might be trying to share without having an email address set up...
		if(!$this->data['sharing_works']){
			return;
		}

		if($recording_id == 0){
			if(isset($_GET['recording_id'])){
				$recording_id = $_GET['recording_id'];
				$_SESSION['recording_id'] = $recording_id;
			}else{
				//lets check the session 
				if(isset($_SESSION['recording_id'])){
					$recording_id = $_SESSION['recording_id'];
				}
			}
		}
		
		if($recording_id == 0){
			//how can we get here?? nonsensical.
			echo "Not sure how you got here. There is no recording id... in your session or in the URL or the function argument...";
			exit();
		}

		//Create a recording object...
		
		$Recording = new Recording($recording_id);
	
		$data['file_name'] = $Recording->name;

		//OK now do we have an email address to share it with?
		//lets see if we have one in the post??

		if(!$to_user_id){
				if(isset($_POST['email'])){
					$email = $_POST['email'];
					$to_user_id = HybridUserInstance::user_id_from_email($email);
					if(!$to_user_id){ //then we do not have a user with this email address...
						$url_encode_email = urlencode($email);
						bounce("/index.php/xhtml/sharing/invite?email=$url_encode_email");
						exit();
					}
				}else{
					//then we should display the form
					//but it will need the recording_id
					return;
				}
		}

		//we could be repeating this function hundreds of times...
		//we do not want to create the User object over and over and over...
		if(is_object($this->ToUser)){
			$ToUser = $this->ToUser;
		}else{
			$ToUser = new User($to_user_id);
			$this->ToUser = $ToUser;	
		}

			//this will not return if the user is new...

				//thank the lord... the simple case!!
				
			//	echo "Ok we can do this....";
				$success = $Recording->shareRecording($_SESSION['user_key'],$ToUser->user_id);
	
				$message = '';
				if($success){
					$message .= "Successfully shared with $email";
					$this->data['to_user_id'] = $ToUser->user_id;
				}else{
					$message .= "Failed to share with $email";
					$this->data['to_user_id'] = 0;
				}
				$this->data['message'] = $message;


		
	}



/**
 * This function handles the invite screens and sends the invitation
 */
	function invite(){

		$user_id = $_SESSION['user_id'];
		$User = new User($user_id);
		$user_email = $User->getEmail();
		if(!$user_email){
			echo "Error: how did you get here without having an email to enable sharing??";
			exit();
		}

		if(isset($_GET['email'])){
			$email = urldecode($_GET['email']);
		}else{
			$email = $_POST['email'];
		}		
		$this->data['email'] = $email;

		$email = mysql_real_escape_string($email);

		if($User->invitedRecently($email)){
			$this->data['message'] = "This user has already been invited recently. You will be notified when this user logs in.";
			$this->data['sent'] = true;
			return;
		}

		if(isset($_POST['send_invite'])){//then the user has decided to invite! We should do that...
		
			//TODO send an email here!!
				$this->data['message'] = "We have sent an invitation to $email! You will be notified when this user logs in";

			//lets get the users name.
			$user = new User($user_id);
			$user_name = $user->name;
			if(strlen($user_name) == 0){
				$user_name = $user_email;
			}
			$app_name = $GLOBALS['app_name'];
			$base_url = $GLOBALS['base_url'];
			$spam_email = $GLOBALS['spam_email'];

				$html_body = "
Hello, <br> 
 &nbsp; &nbsp; &nbsp; &nbsp;   $user_name is using <a href='$base_url'>$app_name</a> to store recordings about their healthcare. 
$user_name has invited you to share those recordings. <br>
In order for them to do that you must first sign in using your phone and this email address at: <a href='$base_url'>$base_url</a><br>
This will allow $app_name to give you access to the recordings that $user_name would like to share with you.<br>
<br>
<br>
Thanks, for using $app_name<br>
<br>
<br>
(note: we sent this email because $user_email invited you. If you feel that this is an unwelcome or unsolicited message, please send an email to $spam_email and we will do our best to ensure that you do not get further emails)
";

				$text_body = "
Hello, 
       $user_name is using $app_name (at $base_url) to store recordings about their healthcare. 
$user_name has invited you to share those recordings. 
In order for them to do that you must first sign using your phone and this email address at: $base_url
This will allow $app_name to give you access to the recordings that $user_name would like to share with you.


Thanks, for using  $app_name!
(note: we sent this email because $user_email invited you. If you feel that this is an unwelcome or unsolicited message, please send an email to $spam_email  and we will do our best to ensure that you do not get further emails)

";


		require_once("../model/Email.class.php");
		$mail = new Email();
		$mail->FromName = "$app_name Invitation";
		$mail->AddAddress($email);                  // name is optional
		$mail->Subject = "$app_name invitation from $user_name";
		$mail->Body    = $html_body;
		$mail->AltBody = $text_body;
		$mail->send();
		
		$this->data['sent'] = true;


		$User->markInvited($email);

		}else{
			//this is first visit to this page, we should just display the sharing form

			$app_name = $GLOBALS['app_name'];

				$message = "<h3> Invite this user to $app_name? </h3>
<p> We can send an invitation to $email to sign up for $app_name.<br>
 this will allow you to share recordings with them. <br> Should we do that?</p>
					";

			$this->data['message'] = $message;
		}//end else for the first visit

	}//end of the function..







/**
 * a function to stop sharing one recordings with one user
 */
	function stopsharingonerecording($recording_id,$stop_user_id){
		//intended to be called, inside and outside..
		if(!isset($this->User)){
			$user = new User($_SESSION['user_id']);
			$this->User = $user;
		}else{
			$user = $this->User;
		}		
		$user->stopSharingOneRecording($recording_id,$stop_user_id);
		$this->data['stopped'] = true;
	}



/**
 * a function to immediately stop all sharing..
 */
	function stopallsharing(){

		if(isset($_POST['sure'])){
			//user is sure... here we go!!
			$user = new User($_SESSION['under_id']);
			$user->stopAllSharing();
			$this->data['stopped'] = true;
		}
	}
/**
 * Admin function to display if your are succesfully sharing with someone.
 */
	function checkvalid(){

		if(isset($_GET['user_id'])){
			$user_id = $_GET['user_id'];
		}else{
			echo "ERROR - shareall(): you need to use a user_id here... not sure how this happened";
			exit();
		}

		$ThisUser = new User($_SESSION['under_id']);

		if($ThisUser->user_id == $user_id){
			echo "You cannot very well check sharing with your self... that does not make any sense...";
			exit();
		}
		//			to 		from
		if(User::sharing_verify($user_id,$ThisUser->user_id)){
			echo "you have shared with $user_id";
		}else{
			echo "you have not shared with $user_id";
		}
	
	}

/**
 * public face of _share_future_records
 */
	function process_future_recordings(){

		if(isset($_REQUEST['to_user_id'])){

			$user_id = $_REQUEST['to_user_id'];

			if(isset($_REQUEST['stop_future_sharing'])){
				$this->_stop_share_future_records($user_id);
			}
			if(isset($_REQUEST['start_future_sharing'])){
				$this->_share_future_records($user_id);
			}
			bounce("/index.php/sharing/manage?user_id=$user_id");
			exit();
		}
		
		echo "sharing.controller.php share_future_records: <br> You should not be able to get here? How did that happen???";

		exit();


	}

/**
 * _stop_share_future_records accepts an email or user_id to share with, and stops sharing future recordings.
 */
	function _stop_share_future_records($user_id = 0){
		if($user_id == 0){
			echo "Error: sharing.controller.php function stop_share_future_records requires a user_id to work, but got zero";
			exit();
		}		

		$this->_new_user_check($user_id);
		
		$ToUser = new User($user_id);
		
		$this->ToUser = $ToUser;	


		//this part happens no matter what... 
		//we need to create a new user_user_access entry...
		//then signit with the current users private key... 

		$ThisUser = new User($_SESSION['user_id']);

		if($ThisUser->user_id == $ToUser->user_id){
			echo "You cannot very well stop share with your self. That would not make any sense,";
			exit();
		}

		//this is the call that stops future sharing
		$ThisUser->stopFutureSharing($ToUser->user_id);


	}
/**
 * _share_future_records accepts an email or user_id to share with, and shares future recordings.
 */
	function _share_future_records($user_id = 0){

		if($user_id == 0){
			echo "Error: sharing.controller.php function _share_future_records requires a user_id to share with, but got zero";
			exit();
		}		

		
		$ToUser = new User($user_id);
		
		$this->ToUser = $ToUser;	


		//this part happens no matter what... 
		//we need to create a new user_user_access entry...
		//then signit with the current users private key... 

		$ThisUser = new User($_SESSION['user_id']);

		if($ThisUser->user_id == $ToUser->user_id){
			echo "You cannot very well share with your self. That would not make any sense,";
			exit();
		}

		//this is the magic call that starts future sharing
		$ThisUser->sharingSign($ToUser->user_id,$_SESSION['user_key']);	


	}


/**
 * directly callable wrapper for _process_recording_list that returns the user
 * to the management interface..
 * 
 */
	function process_recording_list(){

		if(isset($_POST['recordings_array'])){
			$recording_to_share_list = array_flip($_POST['recordings_array']);
		}else{
			$recording_to_share_list = array();
		}
		
		if(isset($_POST['to_user_id'])){
			$to_user_id = $_POST['to_user_id'];

		}else{
			echo "ERROR: sharing.controller.php process_recording_list no to_user_id set";
			exit();
		}
		if(isset($_POST['start_all_sharing'])){
			$ThisUser = new User($_SESSION['user_id']);
			$recording_to_share_list = $ThisUser->getRecordingList();	
		}

		if(isset($_POST['stop_all_sharing'])){
			$ThisUser = new User($_SESSION['user_id']);
			$recording_to_share_list = array();
		}

		$this->_process_recording_list($to_user_id,$recording_to_share_list);

		$return_url = "/index.php/sharing/manage/?user_id=$to_user_id";
		if($GLOBALS['debug']){
			echo "<br><a href='$return_url'>Return to manage</a>";
		}else{	
			bounce($return_url);
			exit();
		}
	}


/**
 * When a user is newly shared with, (for the first time this session).. this function will let them know
 * with a quick email. requires a to_user_id.
 * 
 */
	function _notify_about_sharing($to_user_id = 0){

		if($to_user_id == 0){
			echo "ERROR: sharing.controller.php _notify_about_sharing: This function requires a to_user_id";
			exit();
		}		

		$ThisUser = new User($_SESSION['user_id']);
		$ToUser = new User($to_user_id);
		$from_user_name = $ThisUser->name;
		$from_user_email = $ThisUser->getEmail();
		$spam_email = $GLOBALS['spam_email'];
		$app_name = $GLOBALS['app_name'];
		$base_url = $GLOBALS['base_url'];

		$html_body = "Hello, <br> 
 &nbsp; &nbsp; &nbsp; &nbsp;       $from_user_name has shared new recordings with you. This means that you will have a new tab on the recordings list, that will allow you to listen to all or some of the recordings from the $from_user_name account. 
You can log in at <a href='$base_url'>$app_name</a> to listen to these recordings. <br>
<br>
Thanks, for using $app_name!<br>
<br>
<br>
(note: we sent this email because $from_user_name at $from_user_email shared recordings with you. Because we cannot listen to users recordings without their permission, it is impossible to know if this is a spam recording or not. If you listen to this message and it is not a welcome email from someone you wanted to share with you, contact us at $spam_email  and we will do our best to ensure that this user is removed from your recordings list and cannot bother you again.)
";
		$text_body = "Hello, <br> 
      $from_user_name has shared new recordings with you. This means that you will have a new tab on the recordings list, that will allow you to listen to all or some of the recordings from the $from_user_name account. 
You can log in to $app_name at $base_url to listen to these recordings. <br>
<br>
Thanks, for using $app_name!<br>
<br>
<br>
(note: we sent this email because $from_user_name at $from_user_email shared recordings with you. Because we cannot listen to users recordings without their permission, it is impossible to know if this is a spam recording or not. If you listen to this message and it is not a welcome email from someone you wanted to share with you, contact us at $spam_email  and we will do our best to ensure that this user is removed from your recordings list and cannot bother you again.)
";


		$mail = new Email();
		$mail->FromName = "$app_name Share Notice";
		$mail->AddAddress($ToUser->getEmail());                  // name is optional
		$mail->Subject = "$from_user_name has shared new recordings with you";
		$mail->Body    = $html_body;
		$mail->AltBody = $text_body;
		$mail->send();

		
		

	}//end notify_about_sharing


/**
 * process the per-recording sharing instructions from manage() and then returns the user there...
 * It accepts two arguments, to_user_id is the user_id. 
 * recording_to_share_list is an array of user_ids. If an empty array is sent in, all sharing will be deleted. 
 * 
 */
	function _process_recording_list($to_user_id = 0, $recording_to_share_list){


		if($to_user_id == 0){
			echo "ERROR: sharing_controller.php _process_recording_list, I need both a to_user_id (i got $to_user_id)";
			exit();
		}
		
		$ThisUser = new User($_SESSION['user_id']);

		$full_recordings_list = $ThisUser->getRecordingList();

		$shared_recordings_list = $ThisUser->getSharedRecordingList($to_user_id);
	
		foreach($full_recordings_list as $recording_id => $recording_data){
			if(isset($recording_to_share_list[$recording_id])){
				//we should be sharing this...
				//are we
				if($shared_recordings_list[$recording_id]['shared']){
					//then we are and nothing to do
					if($GLOBALS['debug']){ echo "$recording_id is shared with $to_user_id and it that is OK <br>";}
				}else{
					//then we are not and we need to 
					 if($GLOBALS['debug']){ echo "$recording_id is not shared with $to_user_id and it SHOULD be <br> So we shared<br>";}
					$Recording = new Recording($recording_id);
					if(!$Recording->locked){
						$success = $Recording->shareRecording($_SESSION['user_key'],$to_user_id);
						//if we have not notified this user yet... we need to...
						if(isset($_SESSION["shared_with_$to_user_id"])){
							//then we have already sent notification...
							//we do nothing...
						}else{
							$this->_notify_about_sharing($to_user_id);
							$_SESSION["shared_with_$to_user_id"] = true;
						}


					}else{
						//when we "share all" with locked records, this segment will be hit.
						//we will not share here... so thats that...	
					}
					
				}
			}else{
				//we should not be sharing this
				if($shared_recordings_list[$recording_id]['shared']){
					//then we are, and we need to stop
					if($GLOBALS['debug']){ echo "$recording_id is shared with $to_user_id and it should NOT be <br> so we stopped<br>";}
					$this->stopsharingonerecording($recording_id,$to_user_id);
				}else{
					//then we are not and nothing to do
					if($GLOBALS['debug']){ echo "$recording_id is shared with $to_user_id and that is OK <br>";}
				}
			}
		}//end foreach full_recordings_list
	

	} //end _process_recordings_list


/**
 * Given a user you wish to share with, this interface gives you all of your options.
 */
	function manage(){

		if(isset($_REQUEST['email'])){
			$email = $_REQUEST['email'];
			$to_user_id = HybridUserInstance::user_id_from_email($email);
			if(!$to_user_id){
                                $url_encode_email = urlencode($email);
                                bounce("/index.php/xhtml/sharing/invite?email=$url_encode_email");
                                exit();
			}
			$ToUser = new User($to_user_id);
		}else{
			if(isset($_REQUEST['user_id'])){
				$user_id = $_REQUEST['user_id'];
				$ToUser = new User($user_id);
			}else{

				echo "ERROR - manage(): no email or user_id to share with... I need to know who we are talking about...";
				exit();
			}
		}

		$this->ToUser = $ToUser;	

		$ThisUser = new User($_SESSION['user_id']);

		if($ThisUser->user_id == $ToUser->user_id){
			echo "You cannot very well share with your self. That would not make any sense,";
			exit();
		}


		//OK let make the display.
		$shared_recordings_list = $ThisUser->getSharedRecordingList($ToUser->user_id);
		$this->data['shared_recordings_list'] = $shared_recordings_list;

		$to_user_id = $ToUser->user_id;
		$this->data['to_user_id'] = $to_user_id; 
		$this->data['to_user_name'] = $ToUser->name;	
		$this->data['to_user_email'] = $ToUser->getEmail();	

		$ThisUser = new User($_SESSION['user_id']);
		
		$shared_count_array = array();

		foreach($shared_recordings_list as $recording_id => $r_array){
			if($r_array['shared']){
				$shared_count_array[$recording_id] = $r_array;
			}
		}
	
		$shared_count = count($shared_count_array);
		$this->data['shared_count'] = $shared_count;
		$total_count = count($shared_recordings_list);
		$this->data['total_count'] = $total_count;
		if($shared_count > 0){
			$this->data['shares_some'] = true;
		}else{
			$this->data['shares_some'] = false;
		}

		if($total_count == $shared_count){
			$this->data['shares_all'] = true;
		}else{
			$this->data['shares_all'] = false;
		}



		$future_sharing = User::SharingVerify($ToUser->user_id,$ThisUser->user_id);
		$this->data['future_sharing'] = $future_sharing;


	}//end manage

/**
 * Action to process the sharestop form (the top part of the manage page)
 */
	function process_sharestop(){

		if(isset($_POST['share'])){
			$share = $_POST['share'];
			$to_user_id = $_POST['to_user_id'];
			$ToUser = new User($to_user_id);
			$ThisUser = new User($_SESSION['user_id']);

			$this->data['name'] = $ToUser->name;
			$this->data['to_user_id'] = $ToUser->user_id;

			if(strcmp($share,'all') == 0){
	
				$this->data['message'] = "shared all with " . $ToUser->name;	
				$list_to_share = $ThisUser->getRecordingList();
				//we now run the sharing process against the full list.
				$this->_process_recording_list($to_user_id,$list_to_share);
				$this->_share_future_records($to_user_id);	
			}else{
				$this->data['message'] = "stopped sharing with " . $ToUser->name;
				$ThisUser->stopSharing($to_user_id);
			}


		}else{
			echo "Error: sharing.controller.php how did you get here, expecting a 'share' in POST";
			exit();
		}


	}


/**
 * Share all of your recording with a given user.
 */
	function allrecordings($email){

		if(strlen($email) > 0){
			$to_user_id = HybridUserInstance::user_id_from_email($email);
			$ToUser = new User($to_user_id);

		}else{//lets get it from the request
			if(isset($_REQUEST['email'])){
				$email = $_REQUEST['email'];
				$to_user_id = HybridUserInstance::user_id_from_email($email);
				$ToUser = new User($to_user_id);
			}else{
				if(isset($_REQUEST['user_id'])){
					$user_id = $_REQUEST['user_id'];
					$ToUser = new User($user_id);
					$email = $ToUser->getEmail();
				}else{

					echo "ERROR - allrecordings(): no email or user_id to share with... I need to know who we are talking about...";
					exit();
				}
			}
		}
		$this->_share_future_records($email);

		//then we share every current recording this User has by calling
		//onerecording over and over again.. 

		$ThisUser = new User($_SESSION['user_id']);
		$list_to_share = $ThisUser->getRecordingList();
		//we now run the sharing process against the full list.
		$this->_process_recording_list($ToUser->user_id,$list_to_share);

	} //end all recordings


}//end controller class


?>
