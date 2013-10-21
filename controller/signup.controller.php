<?php
/**
 * contains the signup controller.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */
require_once("../config.php");
require_once('Controller.class.php');



/**
 * The signup controller handles payments from users
 * @todo implement
 * @todo implement coupons
 * @package YDA
 */
class Controller_signup extends Controller{


	var $outside = true;

/**
 * Typical constructor.
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - Signup");

	}
/**
 * Displays year signup button. Uses paypal for limitless subscriptions.
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
                                $_SESSION['users_code'] = $users_code;
                                $_SESSION['first_login'] = true;
				
                                //we need to override the warning but in the header
                                //message by the main controller class...
                                $app_name = $GLOBALS['app_name'];
                                $this->data['header_message'] = "<h4>Thanks for subscribing to $app_name!! </h4>";
                        }else{
                                $this->data['bad_code'] = $users_code;
				$ip = $_SERVER['REMOTE_ADDR'];
                                syslog(LOG_INFO,"signup.controller.php: $ip failed to subscribe with code $users_code");
                        }

                }


        }



/**
 * Called after you just paid through amazon/paypal
 */
	function justpaid(){
		//called after you get back from Amazon payments...

	}

/**
 * Called after you just cancel with amazon/paypal
 */
	function justcanceled(){
		//called if you canceled your payment at amazon...

	}

/**
 * Called by amazon or paypal upon payment
 * this cannot be accessed through index.php and needs to come through paypal.php or amazon.php
 * @todo create paypal.php or amazon.php...
 */
	function IPN(){
		//not called by a user at all
		//instead called by Amazon with a POST of user information
		//probably should just save it to the database...

	}
/**
 * A second place for the EULA
 * @todo should this be here?
 */
	function EULA(){

	}

}//end controller class


?>
