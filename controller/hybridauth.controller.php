<?php
/**
 * The controller that maps to the HybridAuth library, providing endpoints etc 
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Controller.class.php');
require_once("../util/Hybrid/Auth.php");
require_once("../model/User.class.php");
require_once("../model/Prephone.class.php");
require_once("../model/HybridUserInstance.class.php");
/**
 * This is the hybridauth Controller
 * @package YDA
 */
class Controller_hybridauth extends Controller{

	var $refresh = 0;

	var $outside = true; //this is a way for people to login...

/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - login");

	}

	function phonefirst(){
		//stub for an error message
		//fake a valid login (for the template)
		//so that we can properly display the login error
		$this->data['valid_login'] = true;
	}
	
	function success(){

		//this is the url we want to go to for testing...
               	$view_keys = "/index.php/login/viewkeys";


                Hybrid_Auth::initialize( unserialize( $_SESSION["HA::CONFIG"] ) );
                $hybridauth = new Hybrid_Auth($GLOBALS['hybridauth_config']);

                // selected provider name 
                $provider = @ trim( strip_tags( $_GET["provider"] ) );

                // check if the user is currently connected to the selected provider
                if( !  $hybridauth->isConnectedWith( $provider ) ){
                        // redirect him back to login page
                        bounce("/");
			echo "<h1> You have not yet logged in with $provider... not sure how you got here... </h1>";
			exit();

                }

                // call back the requested provider adapter instance (no need to use authenticate() as we already did on login page)
                $adapter = $hybridauth->getAdapter( $provider );

                // grab the user profile
                $userProfile = $adapter->getUserProfile();
		$identifier = $userProfile->identifier;

		if(
			strcmp($provider,"YDANextOpenID") == 0 ||
			strcmp($provider,"YDAOpenID") == 0
			){
			//then this is a phone based login...
			//lets get the phone number...
			// we use explode twice to parse a url that looks like this..
			//http://next.yourdoctorsadvice.org/index.php/openid/index/?id=12312341234
			//we want the phone number at the end...
	
			list($throw_away,$id_string ) = explode("?",$identifier);
			list($throw_away,$phone) = explode("=",$id_string);
			$_SESSION['phone'] = $phone;
			
			$user_id = User::getUserFromPhone($phone);
			if($user_id){
				$_SESSION['user_id'] = $user_id;
				$_SESSION['user_key'] = $identifier;

				//everything else should now work...
			}else{
				$user_id = $this->_newuser($phone,$identifier);
			} 		
		
			$_SESSION['valid_login'] = true;
			$_SESSION['user_id'] = $user_id;
			$_SESSION['user_key'] = $identifier;


		}else{
			//then this is a hybridauth openid instance
			//where we get an email address and need to lookup 
			//the userid in question...
			$email = $userProfile->email;	
				

			$user_id =  HybridUserInstance::user_id_from_email($email);
			//for now this will verify someone logging in from facebook/windowslive
			//if they have already logged in from google.
			//or really any second provider using the same email will just 
			//automatically work by piggybacking on the first providers use of email...

                        if($user_id){
				//deeply unhappy about having to give up on 
				//not having the user_key stored in the database
				//but there is really no other simple way 
				//to get multiple logins to work in a timely manner...
				//**sigh**
				$user_key =  HybridUserInstance::user_key_from_email($email);
                                $_SESSION['user_id'] = $user_id;
                                $_SESSION['user_key'] = $user_key;

                                //everything else should now work...
                        }else{
                                //this is a new hybrid user...
				//There are two modes here...
				//one in which there is no user record yet...
				if(!isset($_SESSION['user_id'])){
					//then this is an attempt to login
					//with a user that we have never seen before
					//that does not yet have a 
					//a phone-based account...
					bounce("/index.php/xhtmlsimple/hybridauth/phonefirst/");
				//	echo "You have to have a phone first";
					exit();
	
				}else{

					//then this is a user already exists
					//and we have a user_id for them
					//so we just need to create a hybrid user for them...

					$user_id = $_SESSION['user_id'];
					$HybridUser = new HybridUserInstance();
					$HybridUser->user_id = $user_id;
			
					foreach($userProfile as $hybrid_key => $hybrid_value){
						//copy over all the values from the hybrid process
						//to the database object
						echo "$hybrid_key -> $hybrid_value <br>";
						if(strlen($hybrid_value)>0){
							$HybridUser->$hybrid_key = $hybrid_value;
						}
						
					}//foreach userProfile end

					$HybridUser->id_provider = $provider;
					$HybridUser->user_key = $_SESSION['user_key'];

					$HybridUser->save();
					$name = $HybridUser->firstName. " " . $HybridUser->lastName;

					$User = new User($user_id);
					$User->name = $name;
					$User->save();
					$User->notifyWaitingUsers($HybridUser->email);


					bounce("/index.php/xhtml/hybridauth/index/");
					exit();

				}
				//and one in which there is a user record 
					//with a phone.. 
					//without a phone...

				
	
                        }

                        $_SESSION['valid_login'] = true;


			

		}

		
		//echo "Going to <a href='$view_keys'>$view_keys</a> Now!!";
		bounce($view_keys);
                exit();
	
	}


	function _newuser($phone,$identifier){

                $refresh = $this->refresh;

                $user = new User();
                $user->newPrivateKey($identifier);
                $user->setOpenidHash($identifier);
                $user->eula_agree = 0;
                $user->splash_seen = 0;
                $user->motd = 0;
                $user->phone = $phone;
		if(isset($_SESSION['users_code'])){
			$user->pay_status = 'code';
			$user->pay_code = $_SESSION['users_code'];
			$user->pay_good_til  = date('Y-m-d', strtotime("6 months")); 
		}

                $user->save();
		$user_id = $user->user_id;
		
		if(is_null($user_id)){
			echo "Error: HybridAuth _newuser:  user_id is null";
			exit();
		}
	
		if(!$user_id){
			echo "Error: HybridAuth _newuser:  user_id is false";
			exit();
		}
	

		//OK, I have made my user...
		//now lets also automatically create a phone
		//and migreate any Prephones too!!

                $user->addPhone($phone,$identifier);

                $prephone = new Prephone($phone);
                //getting upload outputss
                ob_start();
                $prephone->migrateToUser($user->user_id);
                ob_flush();

		$_SESSION['user_id'] = $user->user_id;
		$_SESSION['user_key'] = $identifier;
//		echo "OK Did it work?";
//		var_export($user);

		return($user_id);

	}



	function debug_out(){
		$provider = $_GET['provider'];
		echo "sucesss you have logged in with $provider";

		echo "<pre>";
		var_export($_SESSION);
                Hybrid_Auth::initialize( unserialize( $_SESSION["HA::CONFIG"] ) );
   		$hybridauth = new Hybrid_Auth($GLOBALS['hybridauth_config']);

                // selected provider name 
                $provider = @ trim( strip_tags( $_GET["provider"] ) );

                // check if the user is currently connected to the selected provider
                if( !  $hybridauth->isConnectedWith( $provider ) ){
                        // redirect him back to login page
			bounce("/index.php/hybridauth/index/");
                }

                // call back the requested provider adapter instance (no need to use authenticate() as we already did on login page)
                $adapter = $hybridauth->getAdapter( $provider );

                // grab the user profile
                $userProfile = $adapter->getUserProfile();
	
		
		var_export($userProfile);

		echo "</pre>";

		echo "<a href='/index.php/hybridauth/index/'> Login Again </a>";
		echo "<br>";
		echo "<a href='/index.php/hybridauth/logout/'> Log out from everything </a>";

		exit();

	}


	function logout(){
		try{
			$hybridauth = new Hybrid_Auth( $GLOBALS['hybridauth_config'] );

			// logout the user from $provider
			$hybridauth->logoutAllProviders(); 

			// return to login page
	              	$_SESSION = array();
			$hybridauth->redirect("/");
			exit();
		
    		}
		catch( Exception $e ){
			echo "<br /><br /><b>Oh well, we got an error :</b> " . $e->getMessage();

		}

	}

	function index(){
		//we need to get this from the GLOBALS i.e. config file eventually...
	
        if( isset( $_GET["provider"] ) && $_GET["provider"] ){
                try{
                        // create an instance for Hybridauth with the configuration file path as parameter
                        $hybridauth = new Hybrid_Auth( $GLOBALS['hybridauth_config'] );

                        // set selected provider name 
                        $provider = @ trim( strip_tags( $_GET["provider"] ) );

                        // try to authenticate the selected $provider
                        $adapter = $hybridauth->authenticate( $provider );

                        // if okey, we will redirect to user profile page 
                        $hybridauth->redirect( "/index.php/hybridauth/success?provider=$provider" );
                }
                catch( Exception $e ){
                        // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to 
                        // let hybridauth forget all about the user so we can try to authenticate again.

                        // Display the recived error, 
                        // to know more please refer to Exceptions handling section on the userguide
                        switch( $e->getCode() ){
                                case 0 : $error = "Unspecified error."; break;
                                case 1 : $error = "Hybriauth configuration error."; break;
                                case 2 : $error = "Provider not properly configured."; break;
                                case 3 : $error = "Unknown or disabled provider."; break;
                                case 4 : $error = "Missing provider application credentials."; break;
                                case 5 : $error = "Authentification failed. The user has canceled the authentication or the provider refused the connection."; break;
                                case 6 : $error = "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.";
                                             $adapter->logout();
                                             break;
                                case 7 : $error = "User not connected to the provider.";
                                             $adapter->logout();
                                             break;
                        }

                        // well, basically your should not display this to the end user, just give him a hint and move on..
                        $error = "<br /><br /><b>Original error message:</b> " . $e->getMessage();
                        $error .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";
			echo $error;
			exit();
                }

	}

		$this->data['base_index_url'] = "/index.php/hybridauth/index/";
		$temp_array = array();
		foreach($GLOBALS['hybridauth_config']['providers'] as $provider => $provider_array){
			if($provider_array['enabled']){
				$temp_array[] = $provider;
			}
		}
		$this->data['providers'] = $temp_array;

		if(isset($_SESSION['user_id'])){
			$user_id = $_SESSION['user_id'];
		}else{
			echo "Error: you should not be able to get to the end of hybridauth/index without a user_id";
			echo "This can happen when there is no openid provider in the GET";
			exit();
		}

		$emails = HybridUserInstance::getAllEmails($user_id);
		if(count($emails) > 1){
			$this->data['emails'] = $emails; 
			$_SESSION['emails'] = $emails;
			$_SESSION['user_email'] = array_pop($emails);
		}
	}

/**
 * The endpoint function. Where we send our identity providers back to...
 */
	function endpoint(){
		//display the openid selector...

		$this->_check_static(); //we ensure that perhaps we should just serve some type of static content...
		
if( isset( $_REQUEST["hauth_start"] ) || isset( $_REQUEST["hauth_done"] ) )
{
        # init Hybrid_Auth
        try{
                // check if Hybrid_Auth session already exist
                if( ! isset( $_SESSION["HA::CONFIG"] ) ):
                        header("HTTP/1.0 404 Not Found");

                        die( "Sorry, this page cannot be accessed directly!" );
                endif;

                Hybrid_Auth::initialize( unserialize( $_SESSION["HA::CONFIG"] ) );
        }
        catch( Exception $e )
        {
                Hybrid_Logger::error( "Endpoint: Error while trying to init Hybrid_Auth" );

                header("HTTP/1.0 404 Not Found");

                die( "Oophs. Error!" );
        }

        Hybrid_Logger::info( "Enter Endpoint" );

        # define:endpoint step 3.
        # yeah, why not a switch!
        if( isset( $_REQUEST["hauth_start"] ) && $_REQUEST["hauth_start"] )
        {
                $provider_id = trim( strip_tags( $_REQUEST["hauth_start"] ) );

                # check if page accessed directly
                if( ! Hybrid_Auth::storage()->get( "hauth_session.$provider_id.hauth_endpoint" ) )
                {
                        Hybrid_Logger::error( "Endpoint: hauth_endpoint parameter is not defined on hauth_start, halt login process!" );

                        header("HTTP/1.0 404 Not Found");

                        die( "Sorry, this page cannot be accessed directly!" );
                }

                # define:hybrid.endpoint.php step 2.
                $hauth = Hybrid_Auth::setup( $provider_id );

                # if REQUESTed hauth_idprovider is wrong, session not created, or shit happen, etc. 
                if( ! $hauth )
                {
                        Hybrid_Logger::error( "Endpoint: Invalide parameter on hauth_start!" );

                        header("HTTP/1.0 404 Not Found");

                        die( "Invalide parameter! Please return to the login page and try again." );
                }

                try
                {
                        Hybrid_Logger::info( "Endpoint: call adapter [{$provider_id}] loginBegin()" );

                        $hauth->adapter->loginBegin();
                }
                catch( Exception $e )
                {
                        Hybrid_Logger::error( "Exception:" . $e->getMessage(), $e );

                        Hybrid_Error::setError( $e->getMessage(), $e->getCode(), $e->getTraceAsString(), $e );

                        $hauth->returnToCallbackUrl();
                }

                die();
        }

        # define:endpoint step 3.1 and 3.2
        if( isset( $_REQUEST["hauth_done"] ) && $_REQUEST["hauth_done"] )
        {
                // Fix a strange behavior when some provider call back ha endpoint
                // with /index.php?hauth.done={provider}?oauth_token={oauth_token} 
                // By RP Lin
                if ( strrpos( $_REQUEST["hauth_done"], 'oauth_token' ) )
                {
                        $arr = explode( 'oauth_token', $_REQUEST["hauth_done"] );
                        $_REQUEST["hauth_done"]  = substr( $arr[0], 0, -1 ); // remove ?
                        $_REQUEST["oauth_token"] = substr( $arr[1], 1 );     // remove =
                }

                $provider_id = trim( strip_tags( $_REQUEST["hauth_done"] ) );

                $hauth = Hybrid_Auth::setup( $provider_id );

                if( ! $hauth )
                {
                        Hybrid_Logger::error( "Endpoint: Invalide parameter on hauth_done!" );

                        $hauth->adapter->setUserUnconnected();

                        header("HTTP/1.0 404 Not Found");

                        die( "Invalide parameter! Please return to the login page and try again." );
                }

                try
                {
                        Hybrid_Logger::info( "Endpoint: call adapter [{$provider_id}] loginFinish() " );

                        $hauth->adapter->loginFinish();
                }
                catch( Exception $e )
                {
                        Hybrid_Logger::error( "Exception:" . $e->getMessage(), $e );

                        Hybrid_Error::setError( $e->getMessage(), $e->getCode(), $e->getTraceAsString(), $e );

                        $hauth->adapter->setUserUnconnected();
                }

                Hybrid_Logger::info( "Endpoint: job done. retrun to callback url." );

                $hauth->returnToCallbackUrl();

                die();
        }
}
else{
        # Else, 
        # We advertise our XRDS document, something supposed to be done from the Realm URL page 
        echo str_replace
                (
                        "{X_XRDS_LOCATION}",
                        Hybrid_Auth::getCurrentUrl( false ) . "?get=openid_xrds&v=" . Hybrid_Auth::$version,
                        file_get_contents( dirname(__FILE__) . "../util/Hybrid/resources/openid_realm.html" )
                );

        die();
}




	}



	function _check_static(){

# if windows_live_channel requested, we return our windows_live WRAP_CHANNEL_URL
if( isset( $_REQUEST["get"] ) && $_REQUEST["get"] == "windows_live_channel" )
{
        echo
                file_get_contents( dirname(__FILE__) . "../view/Hybrid/windows_live_channel.html" );

        die();
}

# if openid_policy requested, we return our policy document  
if( isset( $_REQUEST["get"] ) && $_REQUEST["get"] == "openid_policy")
{
        echo
                file_get_contents( dirname(__FILE__) . "../view/Hybrid/openid_policy.html" );

        die();
}

# if openid_xrds requested, we return our XRDS document 
if( isset( $_REQUEST["get"] ) && $_REQUEST["get"] == "openid_xrds" )
{
        header("Content-Type: application/xrds+xml");

        echo str_replace
                (
                        "{RETURN_TO_URL}",
                        Hybrid_Auth::getCurrentUrl( false ) ,
                        file_get_contents( dirname(__FILE__) . "../view/Hybrid/openid_xrds.xml" )
                );

        die();
}



	}


}//end controller class


?>
