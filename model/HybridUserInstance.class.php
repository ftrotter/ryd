<?php
/**
 * contains the hybrid auth user instance model class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once('../config.php');
require_once("Model_Exceptions.php");
require_once("User.class.php");

/**
 *  The HybridUserInstance class handles persistance for the data from HyridAuth 
 * @package YDA
 */
class HybridUserInstance {

	var $hybrid_id; 
	var $user_id; 
	var $user_key;
	var $id_provider;
	var $identifier; //never saved to the database...
	var $identifier_hash;

	var $webSiteURL;
	var $profileURL;
	var $photoURL;
	var $displayName;
	var $description;
	var $firstName;
	var $lastName;
	var $gender;
	var $language;
	var $age;
	var $birthDay;
	var $birthMonth;
	var $birthYear;
	var $email;
	var $preferred_email; //not provided by Hybrid but needed to sort out communications
	//we save phones elsewhere and forget them when ID providers give them to us...
	//so there is no var phones even though Hybrid gives us that...
	var $country;
	var $address;
	var $region;
	var $city;
	var $zip;

	var $sometimes_null_array = array(
        	'webSiteURL',
        	'profileURL',
        	'photoURL',
        	'displayName',
        	'description',
        	'firstName',
        	'lastName',
        	'gender',
        	'language',
        	'age',
       		'birthDay',
        	'birthMonth',
        	'birthYear',
        	'email',
        	'preferred_email',
        	'country',
        	'address',
        	'region',
        	'city',
        	'zip',
	);
	

	var $is_new;
/**
 * This is contructor operates from a simple comment id call
 */
		function __construct($id = 0){

			$id = mysql_real_escape_string($id);

			if($id == 0){
				$this->is_new = true;
			}


						$hybrid_sql = "
SELECT *
FROM `hybrid_user_instance`
WHERE id = '$id'";

			$result = mysql_query($hybrid_sql) or die("ERROR: cannot check hybrid_user_instance table: sql = $hybrid_sql <br> error = ".mysql_error());
			
			$row = mysql_fetch_array($result);

			if($row){
				$this->hyrbid_id = $row['id'];
				$this->user_id = $row['user_id'];
				$this->user_key = $row['user_key'];
				$this->id_provider = $row['id_provider'];
				$this->identifier_hash = $row['identifier_hash'];
				$this->webSiteURL = $row['webSiteURL'];
				$this->profileURL = $row['profileURL'];
				$this->photoURL = $row['photoURL'];
				$this->displayName = $row['displayName'];
				$this->description = $row['description'];
				$this->firstName = $row['firstName'];
				$this->lastName = $row['lastName'];
				$this->gender = $row['gender'];
				$this->language = $row['language'];
				$this->age = $row['age'];
				$this->birthDay = $row['birthDay'];
				$this->birthMonth = $row['birthMonth'];
				$this->birthYear = $row['birthYear'];
				$this->email = $row['email'];
				$this->preferred_email = $row['preferred_email'];
				$this->country = $row['country'];
				$this->address = $row['address'];
				$this->region = $row['region'];
				$this->city = $row['city'];
				$this->zip = $row['zip'];
	
			}else{
				//row is false
				//therefore the comment does not yet exist
			
				$this->is_new = true;

			}
		}    //end constructor..


/**
 * takes an identifier and gets a hash of that identifier
 */
		static function get_id_hash($id){

			$hash = hash("sha512",$id);
			return($hash);

		}

/**
 * This intelligent save function does the work to save a given instance of hybrid auth data  to the database.
 */
		function save(){


			if(empty($this->user_id)){
				throw new MyDataNotSetException("HybridUserInstance: Trying to save without an user_id with hybrid_id $hybrid_id");
			}

			if(empty($this->id_provider)){
				throw new MyDataNotSetException("HybridUserInstance: Trying to save without an id_provider with hybrid_id $hybrid_id");
			}

			if(empty($this->user_key)){
				throw new MyDataNotSetException("HybridUserInstance: Trying to save without an user_key with hybrid_id $hybrid_id");
			}

			if(empty($this->identifier)){
				throw new MyDataNotSetException("HybridUserInstance: Trying to save without an identifier with hybrid_id $hybrid_id");
			}else{
				$this->identifier_hash = $this->get_id_hash($this->identifier);
			}

			if(empty($this->preferred_email)){
				$this->preferred_email = 0; //its not the preferred email
			}

                        if(isset($this->hybrid_id)){
                                $hybrid_id = "'$this->hybrid_id'";
                        }else{
                                $hybrid_id = 'NULL';
                        }

		
			foreach($this->sometimes_null_array as $might_be_null){
			//lets use the $$ trick to pull all of the might_be_null
			//variables into a null format that MySQl can chew...
				if(isset($this->$might_be_null)){
					$tmp = "'".$this->$might_be_null."'";
					$$might_be_null = $tmp;
				}else{
					$$might_be_null = 'NULL';
				}

			}
	
			$new_hybrid_sql = "REPLACE INTO `hybrid_user_instance` (
`id` ,
`user_id` ,
`user_key` ,
`id_provider` ,
`identifier_hash` ,
`webSiteURL` ,
`profileURL` ,
`photoURL` ,
`displayName` ,
`description` ,
`firstName` ,
`lastName` ,
`gender` ,
`language` ,
`age` ,
`birthDay` ,
`birthMonth` ,
`birthYear` ,
`email` ,
`preferred_email` ,
`country` ,
`address` ,
`region` ,
`city` ,
`zip`
) VALUES (
$hybrid_id, 
'$this->user_id', 
'$this->user_key', 
'$this->id_provider', 
'$this->identifier_hash', 
$webSiteURL, 
$profileURL, 
$photoURL, 
$displayName, 
$description, 
$firstName, 
$lastName, 
$gender, 
$language, 
$age, 
$birthDay, 
$birthMonth, 
$birthYear, 
$email, 
$preferred_email, 
$country, 
$address, 
$region, 
$city,
$zip
);";


			mysql_query($new_hybrid_sql) or die("<pre>HybridAuthInstance.class.php ERROR: cannot add to hyrid_auth_instance table:\n sql = $new_hybrid_sql \n error = ".mysql_error(). "</pre>");

			if($this->is_new){
				//then the user associated with this login
				//might have been the subject of invitations
				//lets notify those users using the UserObject
				$User = new User($this->user_id);
				$User->notifyWaitingUsers();
				//lets also pass along the proper name...
				//so that our core user object will have its name right...
				if(strlen($this->displayName) > 0){
					$User->name = $this->displayName;
					$User->save();
				}
				$this->hybrid_id = mysql_insert_id();		
	
			}


			return(true);


		}
/**
 * This handles all of the wonderful decryption functions... could need to deprecate this in favor of a system more like the recordings system..
 */

	function __toString(){

		$return_me = "Hybrid Id: ".$this->hybrid. "<br>";
		$return_me .= "User Id: ".$this->user_id ."<br>";
		$return_me .= "Identifier : ".$this->identifier ."<br>";
		$return_me .= "Identifier Hash: ".$this->identifier_hash ."<br>";
		$return_me .= "Identifier : ".$this->identifier ."<br>";
		
		return($return_me);
	}


	function delete(){

		$id = $this->hybrid_id;
		$delete_sql = "DELETE FROM `hybrid_user_instance` WHERE `hybrid_user_instance`.`id` = $id";
		mysql_query($delete_sql) or die ("Could not delete this hybird_user_instance with $delete_sql".mysql_error());

	}

/**
 *  returns true or false for a given user id if there are emails associated with the user...
 */

        static function canUserShare($user_id){
		$emails = HybridUserInstance::getAllEmails($user_id);
		if(count($emails) > 0){
			return(true);
		}else{
			return(false);
		}
	}

/**
 *  returns an array of all of the emails for a given user_id
 */

        static function getAllEmails($user_id){

		$user_id = mysql_real_escape_string($user_id);

		$search_sql = "
SELECT *
FROM `hybrid_user_instance`
WHERE `user_id` = $user_id
";


                $result = mysql_query($search_sql) or die ("Could not search through hybird_user_instances with $search_sql".mysql_error());

		$return_me = array();
		while($row = mysql_fetch_array($result)){

			$return_me[] = $row['email'];


		}

		return($return_me);

        }

/**
 * Gets an email from a user_id 
 */

                static function email_from_user_id($user_id){

                }




/**
 * Gets an user id from an email 
 */

                static function user_id_from_email($email){
                        //must load an scan the hybrid_auth_instances for a particular user...

                        $email = mysql_real_escape_string($email);

                        $search_sql = "SELECT 
`user_id`
FROM `hybrid_user_instance`
WHERE `email` LIKE  '$email'";

                        $result = mysql_query($search_sql) or die("User.class.php cannot find email with: $search_sql".mysql_error());

                        if($row = mysql_fetch_array($result)){
                        	$user_id = $row['user_id'];
                        	return($user_id);
			}else{
				return(false); //no such user
			}


                }


/**
 * Gets an user key from an email 
 */

                static function user_key_from_email($email){
                        //must load an scan the hybrid_auth_instances for a particular user...

                        $email = mysql_real_escape_string($email);

                        $search_sql = "SELECT 
`user_key`
FROM `hybrid_user_instance`
WHERE `email` LIKE  '$email'";

                        $result = mysql_query($search_sql) or die("User.class.php cannot find email with: $search_sql".mysql_error());

                        $row = mysql_fetch_array($result);

                        $user_key = $row['user_key'];

                        return($user_key);


                }




}//end Hybrid class
?>
