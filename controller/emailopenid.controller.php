<?php
/**
 * contains a OpenID controller specifically for email logins
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2012 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Controller.class.php');
require_once("../model/YDAEmailOpenIDProvider.php");
require_once("../model/HybridUserInstance.class.php");
/**
 * This is the OpenID Controller
 * @package YDA
 */
class Controller_emailopenid extends Controller{

/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..

	}
/**
 * index. adds some things to the data variable, just an example
 * @todo make it do something new
 */
	function index(){
		//display the openid selector...

		$op = new YDAEmailOpenIDProvider;
		$op->server();		

	}


	function signup(){


		$this->data['error'] = false;

		if(isset($_POST['email'])){
			
			$email = mysql_real_escape_string($_POST['email']);

			$test_sql = 
"
SELECT *
FROM `openid_email_users`
WHERE `email` LIKE '$email'
";


			$result = mysql_query($test_sql);
			if(mysql_num_rows($result) > 0){
				$this->data['error'] = true;
				$this->data['already_registered'] = true;
				return;
			}

                        $test_sql = 
"
SELECT id_provider, email
FROM `hybrid_user_instance`
WHERE `email` LIKE '$email'
";

                        $result = mysql_query($test_sql);
                        if(mysql_num_rows($result) > 0){
				$row = mysql_fetch_array($result);
				$id_provider = $row['id_provider'];
                                $this->data['error'] = true;
                                $this->data['already_registered_provider'] = true;
                                $this->data['already_registered_provider_id'] = $id_provider;
                                return;
                        }

			if(strcmp($_POST['email'],$_POST['email_again']) == 0){
				//well thats good...
			}else{
                                $this->data['error'] = true;
                                $this->data['emails_do_not_match'] = true;
				return;
			}

			if(	strlen($_POST['first_name'] < 1)  ||
				strlen($_POST['last_name'] < 1 )){
				//well thats good...
			}else{
                                $this->data['error'] = true;
                                $this->data['need_name'] = true;
				return;
			}

			if(strcmp($_POST['password_one'],$_POST['password_two']) == 0){
				//then the passwords match, lets send the signup email
				$password = mysql_real_escape_string($_POST['password_one']);

				$site_salt = $GLOBALS['site_salt'];
				$user_salt = generateSalt();

				$password_hash =  hash("sha512", $password . $user_salt . $site_salt);
		
				$randomness = uniqid('',true);
				$key = uniqid('',true);
				
				$first_name = $_POST['first_name'];
				$last_name = $_POST['last_name'];
	

				$verified = 1; //for now, no email checking...
		
				$sql = "
INSERT INTO `openid_email_users` (
`id`, 
`email`, 
`first_name`, 
`last_name`, 
`salt`, 
`password`, 
`randomness`, 
`verified`, 
`verified_key`
) 
VALUES (
NULL , '$email', '$first_name', '$last_name', '$user_salt', '$password_hash', '$randomness', '$verified', '$key'
);
";

				mysql_query($sql) or die("Could not add user with $sql".mysql_error());
				//if we want to add email checking, then we can add it here..
				
                                $this->data['done'] = true;
                                $this->data['email'] = $email;

				//now we need to create a corresponding HybridAuthInstance
				//object so that we can load automatically from this newly created user instance...
				
        			$user_id = $_SESSION['user_id'];
                                $HybridUser = new HybridUserInstance();
                                $HybridUser->user_id = $user_id;

                                $HybridUser->id_provider = "EmailOpenid";
                                $HybridUser->user_key = $_SESSION['user_key'];
                                $HybridUser->displayName = $first_name ." ".$last_name;
                                $HybridUser->firstName = $first_name;
                                $HybridUser->lastName = $last_name;
				
                                $HybridUser->email = $email;
                                $HybridUser->identifier = $email;
				

                                $HybridUser->save();

				$this->data['sharing_works'] = true;


			}else{
                                $this->data['error'] = true;
                                $this->data['passwords_do_not_match'] = true;


			}

		}

	}


	function verify(){




	}

}//end controller class


?>
