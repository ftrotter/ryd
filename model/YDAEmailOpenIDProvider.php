<?php
/**
 * OpenID Email Server.
 * Uses standard email loging mechanisms
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
 * OpenID Email Server.
 * Uses standard email loging mechanisms
 * @package YDA
 */
class YDAEmailOpenIDProvider extends LightOpenIDProvider
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
	if(!isset($_POST['email'])){
		//then lets display the form..
		
		$GLOBALS['config']['show_email_form'] = true;

		return false;

	}else{
		$email = mysql_real_escape_string($_POST['email']);		
		$password = mysql_real_escape_string($_POST['password']);		

		$password_hash =  hash("sha512", $password);
			
		echo "Error: I did not find a username and password with that combination ";
		echo "Please <a href='/'>try again</a>";
		exit();	


		}
	
        //header('WWW-Authenticate: Basic realm="' . $this->data['openid_realm'] . '"');
        //header('HTTP/1.0 401 Unauthorized');
    }
    
    function checkid($realm, &$attributes)
    {
	if(isset($_POST['email']) && isset($_POST['password'])){       

                $email = mysql_real_escape_string($_POST['email']);
                $password = mysql_real_escape_string($_POST['password']);


		$query = 
"SELECT *
FROM `openid_email_users`
WHERE `email` = '$email'";		
 
		$result = mysql_query($query) or die("Could not lookup password".mysql_error());

		$row = mysql_fetch_array($result);
		$user_salt = $row['salt'];
		$site_salt = $GLOBALS['site_salt'];

                $password_hash =  hash("sha512", $password . $user_salt . $site_salt);

		if($row['password'] == $password_hash){

			$id = $row['randomness'];
			$attributes['contact/email'] = $row['email'];
            		return $this->serverLocation . "?id=$id";

		}

        }
        return false;
    }
    
}

//$op = new YDAOpenIDProvider;
//$op->server();
