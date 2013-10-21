<?php
/**
 * contains the auth twilio controller
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
 * This twilio controller provides the twilio functions needed to play a pin number to a user for authentication. The pin comes in the GET.
 * @package YDA
 */
class Twilio_auth extends Twilio{

/**
 * the pin function plays the pin it recieves on the GET argument. Allowing
 * another function to refer to it by url as the way to communicate the 
 * authentication pin to the user. 
 */
	function pin(){

		$caller = $_REQUEST['Caller']; 

		$caller = PhoneFormat::forStorage($caller);

		$pin = $_GET['pin'];
		//we get the pin as single number in a GET argument
		//but we need individual digits
		$pin_array = str_split($pin);
		$pin_string = '';
		foreach($pin_array as $number){
			$pin_string .= $number . " ";
		}

		//and now we have a string with the pin seperated by spaces
		//perfect for Twilios text to voice 

	//the following response simple says the authentication string
	//three times, with a pause in case the user is not ready.
	//the last time it says the pin as merged digits, for testing
	//and b/c some people might hear better that way.

	$app_name = $GLOBALS['app_name'];

   $this->xml = "<Response> 
<Say voice='man'> Your verification pin is $pin_string </Say>
<Pause length='4' />
<Say voice='man'> Again, your verification pin is $pin_string</Say>
<Pause length='4' />
<Say voice='man'> Again, your pin is $pin_string or you could say $pin. Goodbye</Say>
</Response>";


	}//end pin function	


}//end controller class


?>
