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
require_once('Controller.class.php');
set_include_path('../util/php-openid/');
require_once "Auth/OpenID/Consumer.php";
require_once "Auth/OpenID/FileStore.php";
require_once "Auth/OpenID/AX.php";
require_once "Auth/OpenID/SReg.php";
require_once "Auth/OpenID/PAPE.php";

/**
 * The login controller has lots of OpenID magic.
 * It uses the OpenID libraries to throw and catch the OpenID stuff.
 * It detected if the openid response gives what we need, like name and email.
 * It handles the process of detecting if the message of the day has been seen,
 * the eula has been agreed to, and the splash has been seen.
 * it will handle have you paid?
 * @todo handle have you paid.
 * @todo rebuild EULA.
 * @todo rebuild MOTD.
 * @todo rebiuld splash screen.
 * @package YDA
 */
class Controller_login extends Controller{

       /**
     	* allows the same catch_url to be used by both catch and throw.
     	* @var string 
        */
	var $catch_url;
	
       /**
     	* this class uses lots of refreshes, this determines when the happen, it should normally be 0 for instance forwarding between functions
	* setting this to something like 10 will allow you to watch the login forwards in slow motion, great for debugging
     	* @var int 
        */
	var $refresh = 0;

	/**
	*	This controller is accesible outside as well as inside
	*/
	var $outside = true;

/**
 * Typical constructor, and it sets the catch url, since we must calculate it.
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - Login Page");

		$this->catch_url = $GLOBALS['base_url']. "index.php/login/catchopenid/";

	}

/**
 * this call gets accidentally made when the iphone app is made.. should be just to index
 */
	function navigation(){

		bounce('/index.php/login/index/',$this->refresh);
		exit();

	}


/**
 * this call gets accidentally made when the iphone app is made.. should be just to index
 */
        function logout(){


        }

/**
 * This is wrapper to the index page that simple takes a get variable and change the logins accordingly
 */
        function gift(){
	
		if(!isset($_GET['code'])){

			echo "Error: you have reached the gift code page without a code";
			exit();
		}
	
		$code = mysql_real_escape_string($_GET['code']);

		$this->data['code'] = $code;
		
		//when we have support for different codes
		//then that will go here too...
		
	return($this->index());


	}


/**
 * Displays the login page, with all needed Javascript added to the header.
 * then it detects the login stage and forwards to other actions using refreshes
 */
	function index(){

		$refresh = $this->refresh;


if(!isset($_SESSION['valid_login'])){
	$_SESSION['valid_login'] = false;
}
	if(!$_SESSION['valid_login']){ //if the session is not valid... lets get a login prompt out to the user

		//display the openid selector...
		$header = $GLOBALS['head'];
		$header->addCSS('<link rel="stylesheet" href="/css/openid.css" />');
		$header->addJS('	
	<script type="text/javascript" src="/js/jquery-1.2.6.min.js"></script>
	<script type="text/javascript" src="/js/openid-jquery.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
	    openid.init(\'openid_identifier\');
	});
	</script>

<style type="text/css">
		/* Basic page formatting. */
		body {
			font-family:"Helvetica Neue", Helvetica, Arial, sans-serif;
		}
</style>
	');

		$this->data['form_action'] = "/index.php/login/throwopenid/";
		$this->data['hello'] = "hi mom";
		}else{// this user has already logged in!!

        $user = new User($_SESSION['user_id']);

	$this->data['new_user'] = false;
	//this should no longer happen base do on the new signup procedure
	if($user->is_new){
        	bounce("/index.php/login/newuser",$refresh);
		exit();
	}


	if($user->eula_agree < $GLOBALS['EULA_VERSION']){

		$your_version = $user->eula_agree;
		$latest_version = $GLOBALS['EULA_VERSION'];

		bounce("/index.php/login/EULA",$refresh);
	//	echo "You have not yet agreed to the latest version of the EULA... forwarding you now";
	//	echo "<br>Your version $your_version, the latest version $latest_version";
	// implement EULA
		exit();
	}

        if($user->splash_seen < $GLOBALS['SPLASH_VERSION']){

                $your_version = $user->splash_seen;
                $latest_version = $GLOBALS['SPLASH_VERSION'];

// implement SPLASH..
//                bounce("/index.php/login/splash",$refresh);
//                exit();
        }

        if($user->motd < $GLOBALS['MOTD_VERSION']){

                $your_version = $user->motd;
                $latest_version = $GLOBALS['MOTD_VERSION'];

		$user->motd = $GLOBALS['MOTD_VERSION'];
		$user->save();

              //  echo "You have not yet agreed to the latest version of the EULA... forwarding you now";
        //      echo "<br>Your version $your_version, the latest version $latest_version";
// Implement MOTD
//               bounce("/index.php/login/motd",$bounce);
//                exit();
        }


        	bounce("/index.php/recordings/index/",$refresh);

//	if($GLOBALS['debug']){	
//        	bounce("/index.php/login/viewkeys",$refresh);
//	}else{
//        	bounce("/index.php/recordings/index/",$refresh);
//	}
	exit();
		}

	}

/**
 * Uses the phone trick to migrate between OpenID URLs..
 * depending on the globals setting...
 * mote sure what the status of this is after the migration to HybridAuth
 */
	function openidfix(){

	
		if(isset($_POST['phone'])){


                $caller = PhoneFormat::forStorage($_POST['phone']);
                //syslog(LOG_INFO,"YDA: twilio_called.php: called with $caller");

		if($caller){
/*                $caller_hash = hash("sha512", $caller);

                $recording_search_sql = "
SELECT 
phones.id as phone_id,
phones.phone_hash,
phones.user_id,
 FROM `phones` 
WHERE `phone_hash` = '$caller_hash' 
";
*/
		$user_id = User::getUserFromPhone($caller);

	        	if($user_id){
	       	         //$user = new User($user_id);

				$PhoneUser = new User($user_id);
				$private_key = $PhoneUser->getPrivatekeyFromPhone($caller);
				$User = new User($_SESSION['user_id']);
				$user_key = $_SESSION['user_key'];
				$User->setPrivateKey($private_key,$user_key); 
				$User->setOpenidHash($user_key);
				$User->save();
				$new_priv_print = base64_encode($private_key);
				
				//$this->data['message'] = "The New private key = <br> <pre> $new_priv_print </pre> From USer Key $user_key ";
				$this->data['done'] = "<h2> All done! You can view your <a href='/'>recordings</a> now.</h2>";
			}else{

				$this->data['message'] = '<h2> We could not find your user account with that phone number </h2>';

			}

		}else{
			// not a ten digit phone number...
			$this->data['message'] = "<h2> Please enter a 10 digit phone number. </h2>";
		}



		}
	}


/**
 * Displaysthe EULA and records agreement with it..
 */
	function EULA(){


		if(isset($_POST['agree'])){

        		$user = new User($_SESSION['user_id']);
			$your_version = $user->eula_agree;
			$latest_version = $GLOBALS['EULA_VERSION'];
			
			$user->eula_agree = $latest_version;
			$user->save();

			bounce('/index.php/login/index/',$this->refresh);
			exit();
		}


	
	}

/**
 * Creates a new user and then forwards browser to viewkeys
 * Deprecated for the version in signup
 */
	function newuser(){


		echo "<pre>";
		var_export($_SESSION);
		echo "</pre>";

		$refresh = $this->refresh;

		$user = new User($_SESSION['user_id']);
		$user->newPrivateKey($_SESSION['user_key']);
		$user->setOpenidHash($_SESSION['user_key']);
		$user->name = $_SESSION['user_name'];
		$user->eula_agree = 0;
		$user->splash_seen = 0;
		$user->motd = 0;

		$user->save();

                bounce("/index.php/login/viewkeys", $refresh);
		exit();	



	}
//This code is based on the old system where a user would have to remember his userkey
//Now, the userkey is always set based on the users private OpenID url (never recorded anywhere in the system) 
/*
	function userkey(){

		$this->data['form_action'] = "/index.php/login/userkey/";

		$user = new User($_SESSION['email']);

		$this->data['new_user'] = false;
		if($user->is_new){
				$this->data['new_user'] = true;
		}
		if(isset($_POST['user_key'])){
			$this->data['display_form'] = false;

			$user_key = $_POST['user_key'];
			$_SESSION['user_key'] = $user_key;
			setcookie("user_key",$user_key,time()+60*60*24*365*10);


			if($user->is_new){
		

			mail(
				$_SESSION['email'],
				"Important Message From: ". $GLOBALS['app_name'],
" DO NOT DELETE THIS MESSAGE!!!! 
This is not a password, and -cannot- be recovered in some cases. If you lose it, you may lose access
to all of your recordings!!! 

Keep this email and write the following somewhere same. Remember it is case-sensitive A and a are two different letters!!

$user_key
",
'From: RecordYourDoc <noreply@recordyourdoc.com>' . "\r\n" // TODO replace with variables from config!!
				);


			//Create a new key pair for this user...

			$user->newPrivateKey($user_key);
			$user->name = $_SESSION['user_name'];
			$user->email = $_SESSION['email'];
			$user->openid = $_SESSION['openid'];
			$user->eula_agree = 0;
			$user->splash_seen = 0;
			$user->motd = 0;

			$user->save();

			}else{
				//this is not a new key...
				//setup to use this key!!
				//perform system decryption here...

			}


			//will not show correctly without the right key
                 	bounce("/index.php/login/viewkeys",$refresh);
			exit();	



		}else{
		}
	}
*/


/**
 * Runs some tests to make sure the key system works. Then either forwards on to recordings
 * index automatically or displays the tests for debugging...
 */
	function viewkeys(){
		$refresh = $this->refresh;

		//var_export($_SESSION);

		$user_id = $_SESSION['user_id'];
		$user_key = $_SESSION['user_key'];

		$user = new User($user_id);
		
		$privkey = $user->getPrivateKey($user_key);
		$public_key = $user->public_key;
		$enc_privkey = $user->enc_priv_key;
	
		//echo "Looks like you are $email <br>";
		//echo "Private Key based on $user_key <br> <pre>$privkey</pre>";
		//echo "How it looks in the db (encrypted) $enc_privkey<br>";
		//echo "Your public key <pre> $public_key </pre> ";

		$test_data = "--------FRED SURE IS A NICE GUY HE IS MARRIED TO LAURA AND HELUNA IS HIS PUPPY DOG heluna laura puppy +++++++++++++";

		//echo "lets practice public/private decryption, with an encrypted private key!!<br> We will use $test_data as a test<br>";
		
		//do not need a password to encrypted to a user...
		$encrypted = $user->publicEncrypt($test_data);

		//echo "encrypted message <pre>$encrypted </pre> <br>";
		
		//but we do need one to get the data out!!
		$cleartext = $user->privateDecrypt($encrypted,$user_key);
                $debug = false;
                if(!$cleartext || $debug){
                        $priv_print = base64_encode($privkey);
                        if(!$cleartext){
                                $warning = "WARNING THIS IS NOT THE RIGHT CODE ";
                        }else{
                                $warning = "Debugging: ";
                        }
                        $this->data['warning'] = "$warning <br><br>Private Key = <pre>$priv_print</pre> encoded with $user_key<br>";
			$this->data['warning'] .= "user_id=$user_id user_key=$user_key <pre>".var_export($user_id,true)."</pre>";
			$try_again_link = "/index.php/login/userkey/";
			$this->data['warning'] .= "<a href='$try_again_link'>Try Again</a>";
		}else{
			//echo "decrypted with $user_key = <br> $cleartext<br>";
			//we know that the user_key works, so we save the user the trouble from now on
			//
			setcookie("user_key",$user_key,time()+60*60*24*365*10);
               		bounce("/index.php/xhtml/recordings/index",$refresh);
			exit();	
		}
	}


/**
 * Uses the php openid libraries to bounce a user to the openid provider.
 */
	function throwopenid(){
		error_reporting(E_ALL);
		$refresh = $this->refresh;
	
		$store = new Auth_OpenID_FileStore('/tmp/oid_store');

		// Create OpenID consumer
		$consumer = new Auth_OpenID_Consumer($store);

		$oid_identifier = $this->getOpenIDURL();

		// Create an authentication request to the OpenID provider
		$auth = $consumer->begin($oid_identifier);

    		if (!$auth) {
        		echo "Authentication error; not a valid OpenID.";
			exit();
    		}

    		$sreg_request = Auth_OpenID_SRegRequest::build(
                                     // Required
                                     array('fullname', 'email','nickname'),
                                     array('nickname')
                                     // Optional
    		);

    		if ($sreg_request) {
        		$auth->addExtension($sreg_request);
    		}

    		if(isset($_GET['policies'])){
        		$policy_uris = $_GET['policies'];

        		$pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
        		if ($pape_request) {
                		$auth->addExtension($pape_request);
        		}
    		}


		// Create attribute request object
		// See http://code.google.com/apis/accounts/docs/OpenID.html#Parameters for parameters
		// Usage: make($type_uri, $count=1, $required=false, $alias=null)
		$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email',2,1, 'email');
		$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first',1,1, 'firstname');
		$attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/last',1,1, 'lastname');

		// Create AX fetch request
		$ax = new Auth_OpenID_AX_FetchRequest;

		// Add attributes to AX fetch request
		foreach($attribute as $attr){
        		$ax->add($attr);
		}

		// Add AX fetch request to authentication request
		$auth->addExtension($ax);


		// Redirect to OpenID provider for authentication
		$url = $auth->redirectURL($GLOBALS['base_url'], $this->catch_url);

		if(headers_sent()){
			echo "I have already sent headers?";
		}

		bounce($url,$refresh);	
		exit();	
	}// end function throw




/**
 * If the email or name is missing from an OpenID catch, this function will get the results
 * of the form to get that info and then bounce over to index for further processing.
 */
	function catchform(){
		$refresh = $this->refresh;
		
			//should we test the email?
			//should we at least make sure it matches the 
			// domain of the openid?
			// but what about other openid providers??
			//TODO figure this out...
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['user_name'] = $_POST['user_name'];
			//bounce("/index.php/login/index",$refresh);	
			exit();

	}

/**
 * Uses the php openid libraries to catch the response from an OpenID provider.
 * Lots of work here to digest email and name fields in the different ways 
 * that various OpenID providers send them.
 * Deprecated in favor of HybridAuth
 */
	function catchopenid(){
		$refresh = $this->refresh;

		//var_export($_POST);
		//var_export($_GET);
		//exit();

		//prevent warnings
		$user_email = false;
		$user_name = false;

		// Create file storage area for OpenID data
		$store = new Auth_OpenID_FileStore('/tmp/oid_store');

		// Create OpenID consumer
		$consumer = new Auth_OpenID_Consumer($store);

		// Create an authentication request to the OpenID provider
		$response = $consumer->complete($this->catch_url);
		$try_again_url = "/index.php/login/index";
		if ($response->status == Auth_OpenID_CANCEL) {
        		// This means the authentication was cancelled.
			$_SESSION = array();
			bounce($try_again_url,$refresh);	
      			echo 'ERROR: Verification cancelled.';
			echo "<br> <a href='$try_again_url'>Try Again</a>";
			exit();
		} else if ($response->status == Auth_OpenID_FAILURE) {
        		// Authentication failed; display the error message.
			$_SESSION = array();
			bounce($try_again_url,$refresh);	
      			echo "ERROR: OpenID authentication failed with: " . $response->message;			
			echo "<br> <a href='$try_again_url'>Try Again</a>";
			exit();

		} else if ($response->status == Auth_OpenID_SUCCESS) {

        		$openid = $response->getDisplayIdentifier();
        		$esc_identity = $this->escape($openid);
	
       		 	//echo "You have successfully verified
       	      		//	<a href='$esc_identity'>$esc_identity</a> as your identity.<br>";

        		if ($response->endpoint->canonicalID) {
            			$escaped_canonicalID = $this->escape($response->endpoint->canonicalID);
            		//	echo "XRI CanonicalID: '.$escaped_canonicalID.')<br> ";
        		}else{
	    		//	echo "No XRI Canonical ID <br>";
			}
	
       		 	$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

        		$sreg = $sreg_resp->contents();
	
        		if (@$sreg['email']) {
            			//echo " SREG Email =  '".$this->escape($sreg['email'])."' as your email <br>";
				$user_email = $sreg['email'];
        		}else{
	    			//echo "No SREG Email <br>";
			}
	
       		 	if (@$sreg['nickname']) {
       	     			//echo "  Your nickname is '".$this->escape($sreg['nickname'])."'<br>";
        		}else{
	    			//echo "No SREG nickname<br>";
			}
	
        		if (@$sreg['fullname']) {
            			//echo  "  Your fullname is '".$this->escape($sreg['fullname'])."'.<br>";
				$user_name = $sreg['fullname'];
        		}else{
	    			//echo "No SREG fullname<br>";
			}

        		// Get AX registration informations
      		  	$ax = new Auth_OpenID_AX_FetchResponse();
        		$obj = $ax->fromSuccessResponse($response);

			if(is_object($obj)){
				//then we did in fact have some AX content


				$first_name = $obj->getSingle('http://axschema.org/namePerson/first',false);
				$last_name = $obj->getSingle('http://axschema.org/namePerson/last',false);
				if($first_name && $last_name){
					$user_name = "$first_name $last_name";
				}else{
					if($first_name){
						$user_name = $first_name;
					}
					
					if($last_name){
						$user_name = $last_name;
					}		
	
				}
				$user_email = $obj->getSingle('http://axschema.org/contact/email',false);


			}else{
				//echo "No AX data <br>";
			}

			$good_to_go = false;
			if($user_name && $user_email){
				$good_to_go = true;
			}

			if($user_name){
				if($GLOBALS['debug']){ mylogger("we have a username of $user_name <br>"); }
			}
			
			if($user_email){
				if($GLOBALS['debug']){ mylogger("we have an email of $user_email <br>");}
			}

			$this->data['user_email'] = $user_email;
			$this->data['user_name'] = $user_name;
			$this->data['user_openid'] = $openid;

			$_SESSION['valid_login'] = true;
			$_SESSION['email'] = $user_email;
			$_SESSION['user_name'] = $user_name;
			$_SESSION['user_key'] = $openid;

			$login_index_url = "/index.php/login/index";

			if($good_to_go){

				if($GLOBALS['debug']){
					mylogger("Session values set no visit go back to the <a href='$login_index_url'>login index</a> to test the passkey form");
				}
			
				//moving right along
				bounce($login_index_url,$refresh);	
				exit();
				
			}else{
				//TODO make the email getting web-form...
				echo "ERROR your openid provider did not provide us with an email for you... so we need a webform here to get one// please let us know if you get to this error... it should not ever happen";
			}

		} else {
  			echo "ERROR: the response status was something other than SUCCESS";
		}


	}

/**
 * Displays the current motd and marks the user as having seen it.
 * @todo implement
 */
	function MOTD(){
		// Message of the day is 
		//displayed when a user has not seen the latest version...

	}

/**
 * gets the OpenID URL and displays error page if it does not exist
 */

function getOpenIDURL() {
    // Render a default page if we got a submission without an openid
    // value.
    if (empty($_GET['openid_identifier'])) {
        echo "Error, you are not sending in an identifier";
        exit(0);
    }

    return $_GET['openid_identifier'];
}
/**
 * Convience function for removing htmlentities.
 */
function escape($thing) {
    return htmlentities($thing);
}

}//end controller class


?>
