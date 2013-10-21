<?php
/**
 * OpenID Phone Server.
 * Uses Twilio to translate Phone to OpenID
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once("../util/LightOpenIDProvider.php");
require_once("../util/PhoneFormat.php");
require_once("../util/twilio/twilio.php");

/**
 * OpenID Phone Server.
 * Uses Twilio to translate Phone to OpenID
 * @package YDA
 */
class YDAOpenIDProvider extends LightOpenIDProvider
{
    public $select_id = true;
    public $login = '';
    public $password = '';
    
    function __construct()
    {
        parent::__construct();
        
        # If we use select_id, we must disable it for identity pages,
        # so that an RP can discover it and get proper data (i.e. without select_id)
        if(isset($_GET['id'])) {
            $this->select_id = false;
        }
    }
    
    function setup($identity, $realm, $assoc_handle, $attributes)
    {
	if(!isset($_POST['phone'])){
		//then lets display the form..
		
		$GLOBALS['config']['show_phone_form'] = true;


/*
		echo "<h1>Phone Login</h1>";
		echo "
<form method='POST' action=''>

Please enter your ten digit phone: 
<input type='text' name='phone'>
<input type='submit' value='Click to start the phone call'>
</form>
	";
*/
		return false;

	}else{
		$phone = strip_tags($_POST['phone']);		
		$phone_display = $phone;
		$phone = PhoneFormat::forStorage($phone); //should remove all formatting

		if(isset($_POST['pin'])){
			$pin = $_POST['pin'];
			if($pin == $_SESSION['pin']){
				return true;
			}else{
				$_SESSION['show_wrong_pin'] = true;
				return false;
			}
		}else{
			// the user has given us their phone...
			// but not yet entered the code
			// we need to display the code form
			// and spawn the call...
			
			$pin = rand(1111,9999); //random four digit pine        
                        $_SESSION['pin'] = $pin;
                        $_SESSION['phone_for_pin'] = $phone;
			

			$GLOBALS['config']['show_pin_form'] = true;
/*
			echo "<h1> OK we are calling you now </h1>

<form method='POST' action=''>			
Please enter the four digit pin we are calling you with: <br>
<input type='text' name='pin'>
<input type='submit' value='Submit pin'>
</form>

		since we have not linked in twilio... just enter $pin
			";			
*/		
			
			//Twilio call logic goes here...

		        $url = $GLOBALS['base_url'] . "twilio.php/auth/pin?pin=$pin";

                        $client = new TwilioRestClient(
                                $GLOBALS['twilio_AccountSid'],
                                $GLOBALS['twilio_AuthToken']);

                            $data = array(
                                "Caller" => $GLOBALS['twilio_CallerId'],              // Outgoing Caller ID
                                "Called" => $phone,       // The phone number you wish to dial
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
                                $GLOBALS['config']['called'] = $phone;
                        }



		}
	}
        //header('WWW-Authenticate: Basic realm="' . $this->data['openid_realm'] . '"');
        //header('HTTP/1.0 401 Unauthorized');
    }
    
    function checkid($realm, &$attributes)
    {
	if(isset($_POST['pin'])){       
 
        	if ($_POST['pin'] == $_SESSION['pin']) {
            		return $this->serverLocation . '?id=' . $_SESSION['phone_for_pin'];
        	}else{
			//echo "wrong pin. fail";
		}
        }
        return false;
    }
    
}

//$op = new YDAOpenIDProvider;
//$op->server();
