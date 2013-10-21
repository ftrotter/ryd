<?php
/**
 * contains the user model class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once('../config.php');
require_once('../util/enchelp.php');
require_once('../util/functions.php');
require_once('Email.class.php');
require_once('HybridUserInstance.class.php');
require_once("Model_Exceptions.php");

/**
 * User model
 *  The User Model handles the users database record, and holds most of the security helper functions for key handling etc etc.
 * @package YDA
 */
class User {

 /** @type int Phone Number. */
		var $phone;
/**
 * Full Name
 */
		var $name;
		var $data;
/**
 * user Id
 */
		var $user_id;
/**
 * Eula Agree.
 * Holds whether the EULA has been agreed to and which version
 */
		var $eula_agree;
		var $splash_seen;
		var $motd;
		var $pay_status;
		var $pay_code;
		var $pay_good_til;
		var $recording_limit;
		var $pin_code;
		var $enc_priv_key;
		var $public_key;
		var $is_new;
		var $openid_md5;
		var $last_login;

	//calculated...
		var $paid;

/**
 * Constructor
 * If you want to create a new user, just do not pass in an id.
 * @param a user_id 
 * @return object user object
 */
		function __construct($email_or_id = 0){

                        $email_or_id = mysql_real_escape_string($email_or_id);

                        if(is_numeric($email_or_id)){
                                $this->user_id = $email_or_id;
                                $id = $email_or_id;
                        }else{
				//then we get the user_id from the email address...
				$id = HybridUserInstance::user_id_from_email($email_or_id);
				$this->user_id = $id;

			}

                $user_sql = "
SELECT *
FROM `users`
WHERE id = '$id'";

			$result = mysql_query($user_sql) or die("ERROR: cannot check user table: sql = $user_sql <br> error = ".mysql_error());
			
			$row = mysql_fetch_array($result);

			if($row){
			$this->is_new = false;
			$this->user_id = $row['id'];
			$this->phone = $row['phone'];
			$this->last_login = $row['last_login'];
			$this->phone = $row['phone'];
			$this->name = $row['name'];
			$this->eula_agree = $row['eula_agree'];
			$this->splash_seen = $row['splash_seen'];
			$this->motd = $row['motd'];
			$this->pay_status = $row['pay_status'];
			$this->pay_code = $row['pay_code'];
			$this->pay_good_til = $row['pay_good_til'];
			$this->recording_limit = $row['recording_limit'];
			$this->pin_code = $row['pin_code'];
			$this->enc_priv_key = $row['enc_priv_key'];
			$this->public_key = $row['public_key'];
			$this->openid_hash = $row['openid_hash'];

//depreciated all of the user_data stuff...


		//has this user paid?
			if(strcmp($this->pay_status,'not') == 0){
				$this->paid = false;
			}else{
				$this->paid = true;
			}

		//but what if the payment is too old?

			if(strtotime($this->pay_good_til) < time()){
				//then the subscription has expired..
				$this->paid = false;
			}

			}else{
			//row is false
			//therefore the user does not yet exist
			
			$this->is_new = true;

			}
		}    
 //encryption functions...

/**
 * markInvited
 * Marks an email address as invited in the database.
 * @param string email
 */
                function markInvited($email){

		$user_email = $this->getEmail();
		$my_user_id = $this->user_id;
               $invite_create_sql = "
INSERT INTO `invites` (
`id` ,
`to_email` ,
`from_email` ,
`from_user_id` ,
`sent`
)
VALUES (
NULL , '$email', '$user_email', $my_user_id, CURDATE( )
);";

                $result = mysql_query($invite_create_sql) or die("sharing.controller.php: Error trying to get invites with <br> $invite_create_sql <br>".mysql_error());


		}
/**
 * invitedRecently
 * This function accepts an email address, and determines how recently it has been invited.
 * @param string email
 * @return boolean was invited recently
 */
                function invitedRecently($email){

                $email = mysql_real_escape_string($email);
		$user_email = $this->getEmail();
                $one_week_ago = date( 'Y-m-d', strtotime('-1 week') );
                //Should this be a configurable time?   
                $invite_sql = "SELECT *  FROM `invites` WHERE `to_email` = '$email' AND `from_email` = '$user_email' AND `sent` > '$one_week_ago'";

                $result = mysql_query($invite_sql) or die("sharing.controller.php: Error trying to get invites with <br> $invite_sql <br>".mysql_error());
                if(mysql_num_rows($result) > 0){
                        return(true);
                }else{
			return(false);
		}

}


/**
 * Get Email
 * getEmail enables the user to leveral the HybridUserInstance class to carry multiple emails
 * returns false when there is no email address
 * @return string $email
 */
                function getEmail(){
		
			$my_emails = HybridUserInstance::getAllEmails($this->user_id);
	                if(count($my_emails) == 0){
				return(false);
                	}
                	$user_email = array_pop($my_emails);
			return($user_email);

                }




/**
 * Public Encrypt
 * Using this users public key, encrypt data. Ensuring that only the users private key will be able to unlock the information. 
 * @param string $data the data to encrypt
 * @return string $encrypted_data or false on failure.
 */
		function publicEncrypt($data){
			$success = @openssl_public_encrypt($data,$encrypted,$this->public_key);
			if($success){	
				return($encrypted);
			}else{
				return(false);
			}
		}

/**
 * Private Decrypt
 *
 * Using this users user_key (which can unlock the users private key), decrypt data.
 * @param string $encrypted_data the data to decrypt
 * @param string $user_key the users key (can unlock the private key)
 * @return string $cleartext_data or false on failure.
 */
		function privateDecrypt($encrypted_data,$user_key){
			$private_key = $this->getPrivateKey($user_key);
			$success = @openssl_private_decrypt($encrypted_data,$cleartext_data,$private_key);

			if($success){			
				return($cleartext_data);			
			}else{
				return(false);
			}
		}


/**
 * Using this users plaintext phone (which can unlock the users private key), decrypt data.
 * @param string $encrypted_data the data to decrypt
 * @param string $phone plain text phone number (can unlock the private key)
 * @return string $cleartext_data or false on failure.
 */

		function privateDecryptPhone($encrypted_data,$phone){
			$private_key = $this->getPrivatekeyFromPhone($phone);
			$success = @openssl_private_decrypt($encrypted_data,$cleartext_data,$private_key);

			if($success){			
				return($cleartext_data);			
			}else{
				return(false);
			}
		}

/**
 * Using this users user key, return the private key. The user_key is used to symetrically encrypt the private key. Allowing us to use x509 for asymmetric encryption without needing to make a user responsible for tracking a x509 private key.
 * @param string $user_key the secret passcode from the user that unlocks the private key (probably a openid url)
 * @return string $private_key.
 */
		function getPrivateKey($user_key){

			$priv_key = Decrypt($this->enc_priv_key,$user_key);
			return($priv_key);

		}


/**
 * Using this users user key, encrypt the private key. The user_key is used to symetrically encrypt the private key. Allowing us to use x509 for asymmetric encryption without needing to make a user responsible for tracking a x509 private key. This function should only be used on user creation.
 * @param string $priv_key the plaintext private key for this user
 * @param string $user_key the secret passcode from the user that unlocks the private key (probably a openid url)
 */
		function setPrivateKey($priv_key,$user_key){

			$this->enc_priv_key = Encrypt($priv_key,$user_key);

		}
/**
 * use this function to see if the openid_hash has changed between logins
 * @param string $user_key the secret passcode from the user that unlocks the private key (probably a openid url)
 */ 
		function checkOpenidHash($user_key){

			$calculated_hash = $this->_calculate_hash($user_key);
			if(strcmp($calculated_hash,$this->openid_hash) == 0){
				//everything looks great!!
				return(true);
			}else{
				//opps something has changed...
				mylogger($this->phone . " OpenID hash has changed");	
//update email centric hash here?

				return(false);
			}

		}
/** 
 * One function to calculate the open id hash so that we can be sure the algorithum is always the same.
 * @param string $user_key the secret passcode from the user that unlocks the private key (probably a openid url)
 */
		function _calculate_hash($user_key){

//we are hashing with sha512 which is less broken according to Google.
//We are salting with the site name which will ensure that the hashes break when we change the URL (which we want)
//hopefully this, plus the length of the openid strings will serve to prevent rainbow table attacks.
//have no idea how I am going to make this work with facebooks login???

//we need to seperate email address out of this hash function...
//this breaks everything... shit shit shit.


                        $pre_hash_string = $user_key.":".$GLOBALS['base_url'];
                        $post_hash_string = hash('sha512',$pre_hash_string);
			return($post_hash_string);

		}


/**
 * Create hash for the user_key so we can tell when an openid url has changed (i.e. when we change the site name) 
 * This function should only be used on user creation. or when the site url has been changed, or if google screes up with the directed identity etc..
 * @param string $user_key the secret passcode from the user that unlocks the private key (probably a openid url)
 */
                function setOpenidHash($user_key){

                        $this->openid_hash = $this->_calculate_hash($user_key);


                }



/**
 * Create a private/public key pair for a given user. This saves both keys to object variables.
 * @param string $user_key the secret passcode from the user that unlocks the private key (probably a openid url)
 */
		function newPrivateKey($user_key){

			$PKEY_obj = openssl_pkey_new();
			openssl_pkey_export($PKEY_obj,$privkey);
			$this->setPrivateKey($privkey,$user_key);
			$this->setOpenidHash($privkey,$user_key);
			$pubkey = openssl_pkey_get_details($PKEY_obj);
			$this->public_key = $pubkey["key"];

		}


/**
 * This intelligent save function does the work to save a given user to the database.
 * It checks to make sure the user is healthy. Then uses an "REPLACE INTO" to save the 
 * database record, which works if its an intial save or an overwrite.
 */
		function save(){

			$user_id = $this->user_id;
			if(empty($this->phone)){
				echo "<pre>";
				var_export($this);
				echo "</pre>";
				throw new MyDataNotSetException("Trying to save without phone for $user_id");
			}

			if(!isset($this->eula_agree)){
				throw new MyDataNotSetException("Trying to save without eula_agree for $user_id");
			}

			if(!isset($this->splash_seen)){
				throw new MyDataNotSetException("Trying to save without splash_seen for $user_id");
			}

			if(!isset($this->motd)){
				throw new MyDataNotSetException("Trying to save without motd for $user_id");
			}

                        if(empty($this->enc_priv_key)){
				throw new MyDataNotSetException("Trying to save without enc_priv_key for $user_id");
                        }

                        if(empty($this->public_key)){
				throw new MyDataNotSetException("Trying to save without public_key for $user_id");
                        }

                        if(empty($this->openid_hash)){
				throw new MyDataNotSetException("Trying to save without openid_hash for $user_id");
                        }





			if(empty($this->pay_status)){
				//we do not handle payment on user creation...
				//so this is not a failure...
				$this->pay_status = 'not';
	
			}
			if(empty($this->pay_code)){
				$this->pay_code = 'none';
			}
			if(empty($this->pay_good_til)){
				$this->pay_good_til = 'NOW()';
			}else{
				//brains to handle date calculation needs to go here..
				//for now its got to be a valid date..
				//like 2010-03-10 and then we will change it to 	
				$this->pay_good_til = "'".$this->pay_good_til."'";
				//we do this so we can use a function sometimes
				//and a date other... 
				//so this function handles the single quotes in the sql below...			

			}

			if(empty($this->recording_limit)){
				$this->recording_limit = 0;
			}

			if(empty($this->pin_code)){
				$this->pin_code = 0;
			}


			if(isset($this->user_id)){
				$user_id = "'$this->user_id'";
			}else{
				$user_id = 'NULL';
			}

			$new_user_sql = "REPLACE INTO `users` (
`id` ,
`email` ,
`phone` ,
`name` ,
`last_login`,
`eula_agree`,
`splash_seen`,
`motd`,
`pay_status`,
`pay_code`,
`pay_good_til`,
`recording_limit`,
`pin_code`,
`enc_priv_key`,
`public_key`,
`openid_hash`
)
VALUES (
$user_id ,'depricated@example.com', '$this->phone', '$this->name', 
NOW( ), '$this->eula_agree', '$this->splash_seen', '$this->motd',
'$this->pay_status', '$this->pay_code', $this->pay_good_til , $this->recording_limit , $this->pin_code ,
'$this->enc_priv_key', '$this->public_key','$this->openid_hash'

);";
//note that pay_good_til does not need single quotes above... 
//it has to be taken care of elsewhere..

			mysql_query($new_user_sql) or die("User.php ERROR: cannot add to user table: sql = $new_user_sql <br> error = ".mysql_error());

			if($this->is_new){
				//then this is the first time this user has ever logged in
				//we need to notify all of the other users who where waiting for this, 
				//so that they can choose to share... if they still want too...
				//$this->notifyWaitingUsers();
				
				//this really cannot happen here
				//because the HybridUser, where the email is actually stored...
				//comes later...
				//we need to move this logic to the HybirdUserClass..

				$this->user_id = mysql_insert_id();
			}


			return(true);


		}


/**
 * This returns a list of people we have invited, but not yet shared with...
 * (deprecated until we can review sharing mechanism )
 */
		function getInviteAddedList(){

		//this sql returns nulls for the to_user_id for the rows we want... backwards
		//if it was not 4 am I would rewrite it correctly.

		$user_id = $this->user_id;

	$invites_sql = "
SELECT 
from_hybrid.user_id AS from_invite_user_id, 
from_hybrid.email AS from_invite_email, 
to_hybrid.user_id AS to_invite_user_id, 
to_hybrid.email AS to_invite_email,
user_user_access.id as uua_id
FROM `users`
JOIN hybrid_user_instance AS from_hybrid ON users.id = from_hybrid.user_id
JOIN invites ON invites.from_email = from_hybrid.email
JOIN hybrid_user_instance AS to_hybrid ON invites.to_email = to_hybrid.email
LEFT JOIN user_user_access ON to_hybrid.user_id = user_user_access.to_user_id
WHERE users.id = $user_id
";

		$return_array = array();
		$result = mysql_query($invites_sql) or die("User.class.php: error looking up invites with <br> $invites_sql <br>".mysql_error());
		while($row = mysql_fetch_assoc($result)){
			if(is_null($row['uua_id'])){ //then we do not have any sharing happening
					//but we still have an invite... lets add it to the list!!!
				$return_array[$row['to_invite_user_id']] = $row['to_invite_email'];
			} 
		}

		return($return_array);

		}


/**
 * this loops through everyone who is waiting on the user to login to the system (from the invitation table), and sends a notice saying that this person is now 
 * a user...
 */
		function notifyWaitingUsers($user_email = ''){

			$app_name = $GLOBALS['app_name'];
			$base_url = $GLOBALS['base_url'];
			$spam_email = $GLOBALS['spam_email'];
		
			$user_name = $this->name;
			if(strlen($user_email) == 0){
				$user_email = $this->getEmail();
			}
			if(strlen($user_name) == 0){
				$user_name = $user_email;
			}


			$html_body = "
Hello, <br> 
 &nbsp; &nbsp; &nbsp; &nbsp;   $user_name has logged in to <a href='$base_url'>$app_name</a> for the first time. 
If you would like, you can now share recordings with this user.<br>
<br>
Thanks,<br
the $app_name team!<br>
<br>
<br>
(note: we sent this email because you invited $user_name to use $app_name. If you feel that this is an unwelcome or unsolicited message, please send an email to $spam_email and we will do our best to ensure that you do not get further emails)
";

			$text_body = "
Hello, 
      $user_name has logged in to $app_name (at $base_url) for the first time. 
If you would like, you can now share recordings with this user.

Thanks, 
the $app_name team!


(note: we sent this email because you invited $user_name to use $app_name. If you feel that this is an unwelcome or unsolicited message, please send an email to $spam_email and we will do our best to ensure that you do not get further emails)
";

		
		require_once("../util/phpmailer/class.phpmailer.php");
		$mail = new Email();

		$mail->FromName = "$app_name Invitations";
		//loop over recipients and add bcc
		
		$users_to_email = $this->getWhoInvitedMe($user_email);

		foreach($users_to_email as $id => $email){
			$mail->addBCC($email);
		}
	
		
		$mail->AddAddress($GLOBALS['smtp_username']); //send the "to" as ourselves.. lets us bcc this process which is faster... just one call         


		$mail->Subject = "$user_name has logged to $app_name";
		$mail->Body    = $html_body;
		$mail->AltBody = $text_body;

		$mail->send();

		}

/**
 * This functon returns an array of users who have invited me to this service.
 */
		function getWhoInvitedMe($my_email = ''){

                        if(strlen($my_email) == 0){
                                $my_email = $this->getEmail();
                        }

                        if(strlen($my_email) == 0){
				throw new Exception("getWhoInvitedMe: getEmail is not returning anything for this user");
                        }
			$send_to_sql = "
SELECT *
FROM `invites`
WHERE `to_email` = '$my_email'
";


			$return_array = array();
			$result = mysql_query($send_to_sql) or die("User.class.php: getWhoInvitedMe() Error: looking up invites with <br> $send_to_sql <br>".mysql_error());
			while($row = mysql_fetch_assoc($result)){
				$return_array[$row['from_user_id']] = $row['from_email'];
			}
		
			return($return_array);	
		}



/**
 * The magic __toString merely calls the non-magic toString
 */
		function __toString(){
			return($this->toString());
		}
	
/**
 * The non-magic toString prints the database values for debugging
 */
		function toString(){

			$result = 	"Name = $this->name <br>".
				  	"Email = ".HybridUserInstance::user_id_from_email($this->user_id)." <br>".
				  	"Eula = $this->eula <br>";
				  	"Splash = $this->slash_seen <br>";
				  	"MOTD = $this->motd <br>";
				  	"Data Array =  <br>";

			foreach($this->data as $label => $data){

				$result .= "&nbsp;&nbsp;&nbsp; $label = $data <br>";
			}

			return($result);
		}


/**
 * Gets the phone id from the phone number
 */
		public function getPhoneIdFromPhone($phone){

			$phone = PhoneFormat::ForStorage($phone);

			$phone_hash =  hash("sha512", $phone);
			$user_id = $this->user_id;

$phone_count_sql = "
SELECT phones.id,  
`phone_hash`,
phones.user_id,
FROM `phones`
JOIN users ON phones.user_id = users.id
WHERE `phone_hash` = '$phone_hash'
AND active = 1
GROUP BY `phone_hash`";

			$results = mysql_query($phone_count_sql) 
				or die("User.class.php: could not count matching phones with <br> $phone_count_sql <br>".mysql_error());

			if($row = mysql_fetch_array($results)){
			//this has already been used...
	
				$id = $row['id'];

				return($id);
			}else{
				//then this does not appear yet... it is safe to add..
				return(false);
			}
	
		}//function end



/**
 * phones must only occur once in the database, but they are hashed, which means that we need to hash each new phone and check to see if already exists in the database. We will need to contact users to let them know when a phone changes accounts, so we go ahead and provide the email address rather than "true" as the result of this function.
 */
		public function phoneUsed($phone){

			$phone = PhoneFormat::ForStorage($phone);

			$phone_hash =  hash("sha512", $phone);
			$user_id = $this->user_id;

$phone_count_sql = "
SELECT count( phones.id ) AS count,  
`phone_hash`,
phones.user_id,
users.id
FROM `phones`
JOIN users ON phones.user_id = users.id
WHERE `phone_hash` = '$phone_hash'
AND active = 1
GROUP BY `phone_hash`";

		$results = mysql_query($phone_count_sql) 
			or die("User.class.php: could not count matching phones with <br> $phone_count_sql <br>".mysql_error());

		if($row = mysql_fetch_array($results)){
			//this has already been used...
	
			$count = $row['count'];
			$email = HybridUserInstance::user_id_from_email($row['id']);
			if($count > 1){//this should never happen...
				mylogger("The following phone appears to be in the phone database twice: $phone_hash");
			}

			mylogger("The following phone collides $phone_hash");

			return($email);
		}else{
			//then this does not appear yet... it is safe to add..
			return(false);
		}
	
	

		}

/**
 * We have to be able to delete phones from the database.
 * @param integer $id the $id of the phone to delete.
 */
		public function deletePhone($id){
			//uses the user_key to get a list of the users phone numbers...
			$user_id = $this->user_id;
			$delete_sql = 
"
DELETE FROM `phones`
WHERE `user_id` = $user_id AND `id` = $id
";
			mysql_query($delete_sql) 
				or die("User.class.php: unable to delete phone number with <br> $delete_sql <br>".mysql_error());

		}

/**
 * A user needs to be able to see their phone numbers, even if admin cannot.
 * So we encrypted the plaintext phone numbers with the users public key.
 * This way, the user can see what the phone numbers are, but we "see" only hashes 
 */
		public function decryptPhone($id,$user_key){
			//uses the user_key to get a list of the users phone numbers...
			$user_id = $this->user_id;
			$phone_sql = 
"
SELECT *
FROM `phones`
WHERE `user_id` = $user_id AND `id` = $id
";
			$result = mysql_query($phone_sql) 
				or die("User.class.php: unable to load phone number with <br> $phone_sql <br>".mysql_error());
			if($row = mysql_fetch_array($result)){
				$phone = $this->privateDecrypt(base64_decode($row['enc_phone']),$user_key);
				return($phone);
			}else{
				return(false);
			}

		}

		
/**
 * Returns true or false depending on whether the results of listPhones has 0 elements
 * @param string $user_key the users user_key which unlocks the users private key.
 */
		public function hasPhone($user_key){

			$phone_array = $this->listPhones($user_key);
			if(count($phone_array) > 0){
				return(true);
			}else{
				return(false);
			} 

		}
/**
 * Returns a whole list of phones for this user.
 * @param string $user_key the users user_key which unlocks the users private key.
 */
		public function listPhones($user_key){
			//uses the user_key to get a list of the users phone numbers...

			$user_id = $this->user_id;
			$phone_sql = 
"
SELECT *
FROM `phones`
WHERE `user_id` = $user_id
";
			$result = mysql_query($phone_sql) 
				or die("User.class.php: unable to load phone list with <br> $phone_sql <br>".mysql_error());
			$phone_array = array();
			while($row = mysql_fetch_array($result)){
				$phone = $this->privateDecrypt(base64_decode($row['enc_phone']),$user_key);
				$phone_array[$row['id']] = $phone;
			}

			return($phone_array);
		}

/**
 * Adds a phone to users account, after checking to make sure it is not already used.
 * @param string $phone the users plaintext phone number.
 * @param string $user_key the users user_key which unlocks the users private key.
 */
		public function addPhone($phone,$user_key){
		
			if($this->phoneUsed($phone)){

				echo "User.class.php: Error attempted to add a phone that already existed";
				exit();

			}

			$phone = PhoneFormat::ForStorage($phone);

			//each phone record has a copy of the private key for the user
			//that is encrypted -with- the phone number.
			//TODO this section needs to be done -only- if 
			//the user has "enable phone access" clicked
			//otherwise this is an easy way to get access to the private key
			$private_key = $this->getPrivateKey($user_key);
			$enc_private_key = Encrypt($private_key,$phone);
			$enc_phone = base64_encode($this->publicEncrypt($phone));		

			$phone_hash =  hash("sha512", $phone);
			$user_id = $this->user_id;
			$phone_sql = "
INSERT INTO `phones` (
`id` ,
`enc_phone` ,
`phone_hash` ,
`enc_priv_key` ,
`user_id` ,
`created` ,
`active`
)
VALUES (
NULL , 
'$enc_phone', 
'$phone_hash', 
'$enc_private_key',
'$user_id',
CURRENT_TIMESTAMP , '1'
);


";

		mysql_query($phone_sql) 
			or die("User.class.php: could not add a phone number with <br>$phone_sql <br>".mysql_error());

		}


    public function delete()
    {

	$user_id = $this->user_id;
	$emails = HybridUserInstance::getAllEmails($user_id);

	foreach($emails as $email){
		$sql = "DELETE FROM `openid_email_users` WHERE `openid_email_users`.`email` = '$email'";
		mysql_query($sql) or die("Could not $sql ".mysql_error());
	}
	//all emails are deleted..

	$sql = "DELETE FROM `hybrid_user_instance` WHERE `hybrid_user_instance`.`user_id` = $user_id";
	mysql_query($sql) or die("Could not $sql ".mysql_error());


	$sql = "DELETE FROM `phones` WHERE `user_id` = $user_id";
	mysql_query($sql) or die("Could not $sql ".mysql_error());


	//clean recordings
	$recordings = $this->getRecordingList();

	foreach($recordings as $recording_id => $recording_array){

		$Recording = new Recording($recording_id);
		//very important do do this via the object, since we want to free
		//up the rackspace files area...
		$Recording->deleteRecording();

	}

	//finally we actually delete the record...

	$sql = "DELETE FROM `user_user_access` WHERE `user_user_access`.`from_user_id` = $user_id";
        mysql_query($sql) or die( "failed to delete from_user sharing using  $sql ".mysql_error());

	$sql = "DELETE FROM `user_user_access` WHERE `user_user_access`.`to_user_id` = $user_id";
        mysql_query($sql) or die( "failed to delete to_user sharing using  $sql ".mysql_error());

        $sql = "DELETE FROM
                    users
                WHERE
                    id=" . mysql_escape_string($this->user_id) ."
		;";
 
        mysql_query($sql) or die( "failed to delete with $sql ".mysql_error());
    }

/**
 * deletes all sharing for the current user... 
 */
	function stopSharing($stop_user_id, $recording_id = 0){


		$my_user_id = $this->user_id; 
		if($my_user_id == $stop_user_id){
			//that makes no sense...
			echo "Error: you cannot unshare yourself..";
			exit();
		}

		if($recording_id == 0){//cut off all access for this user

		$current_keys_sql = "SELECT recording_keys.id 
FROM `recording_keys`
JOIN recording ON recording_keys.recording_id = recording.id
WHERE recording.user_id = $my_user_id
AND recording_keys.user_id = $stop_user_id";
		
		$result = mysql_query($current_keys_sql) or 
			die("User.class.php: Error: could not get user sharing list with $current_keys_sql <br>".mysql_error());
		//then we delete them one by one.
		while($row = mysql_fetch_assoc($result)){
			$id_to_delete = $row['id'];
			$delete_sql = "DELETE FROM recording_keys WHERE recording_keys.id = $id_to_delete";
			mysql_query($delete_sql) or 
				die("User.class.php: Error: could not delete key with $delete_sql <br>".mysql_error());

		}//end delete while

		//then we delete future shares...
		$this->stopFutureSharing($stop_user_id);

		}else{// only cut of access for the one recording...


			$delete_just_one_sql = "DELETE FROM recording_keys WHERE recording_keys.recording_id = $recording_id AND recording_keys.user_id = $stop_user_id";

			mysql_query($delete_just_one_sql) or 
				die("User.class.php: Error: could not delete key with $delete_just_one_sql <br>".mysql_error());


		}


	}

/**
 * deletes future sharing with a given user, or with all users if no user_id is given
 */
	function stopFutureSharing($stop_user_id = 0){


		$my_user_id = $this->user_id;

		if($stop_user_id == 0){
			//then we delete all future sharing..
			$future_shares_delete_sql = "DELETE FROM user_user_access WHERE from_user_id = $my_user_id";
		}else{
			//then just to that user...
			$future_shares_delete_sql = "DELETE FROM user_user_access WHERE from_user_id = $my_user_id AND to_user_id = $stop_user_id";
		}


		
		mysql_query($future_shares_delete_sql) or 
			die("User.class.php: stopFutureSharing Error: could not delete future shares with $future_shares_delete_sql <br>".mysql_error());



	}

/**
 * deletes all sharing for the current user... 
 */
	function stopAllSharing(){

		$user_id = $this->user_id;
		//first we need to delete all of the current shares
		//we get the list of them
		$current_keys_sql = "SELECT recording_keys.id 
FROM `recording_keys`
JOIN recording ON recording_keys.recording_id = recording.id
WHERE recording.user_id = $user_id
AND recording.user_id != recording_keys.user_id";
		
		$result = mysql_query($current_keys_sql) or 
			die("User.class.php: Error: could not get user sharing list with $current_keys_sql <br>".mysql_error());
		//then we delete them one by one.
		while($row = mysql_fetch_assoc($result)){
			$id_to_delete = $row['id'];
			$delete_sql = "DELETE FROM recording_keys WHERE recording_keys.id = $id_to_delete";
			mysql_query($delete_sql) or 
				die("User.class.php: Error: could not delete key with $delete_sql <br>".mysql_error());

		}

		//then we delete future shares...
		$this->stopFutureSharing();
		//calling it with no arguement means that we stop all future sharing...

	}

/**
 * Returns an array of the users (name and email) that this user is sharing data with, indexed by user_id
 * @return array $share_array an array of the users that this user is sharing with.
 */
	function whoSharesWithMe(){

		$this_user = $this->user_id;

		if($this_user > 0){
			//then it is a real id
		}else{
			//then this user has not been saved yet
			return(array());
		}


		$share_sql = "
SELECT 
from_user_id,
to_user_id,
active,
name as from_name
 FROM `user_user_access` as uua
JOIN users on uua.from_user_id = users.id
WHERE active = 1 AND
to_user_id = $this_user
";

	$result = mysql_query($share_sql)
			or die("User.class.php whoSharesWithMe() Error: share sql failed <br> $share_sql <br>". mysql_error());

	$return_me = array();
	while($row = mysql_fetch_assoc($result)){

		if(User::sharingVerify($row['to_user_id'],$this_user)){
			$row['future_sharing'] = true;
			$return_me[$row['to_user_id']] = $row;
		}
	}

$recording_share_sql = "
SELECT 
recording.user_id as owner_user_id,
recording.id as recording_id,
recording_keys.user_id as shared_user_id,
COUNT(recording_keys.user_id) as count,
users.name
FROM `recording` 
JOIN recording_keys on recording.id = recording_keys.recording_id
JOIN users on recording.user_id = users.id
WHERE recording.user_id != recording_keys.user_id AND
recording_keys.user_id = $this_user
GROUP BY recording_keys.user_id";


	$result = mysql_query($recording_share_sql)
			or die("User.class.php whoSharesWithMe() Error: recording_share sql failed <br> $recording_share_sql <br>". mysql_error());

	while($row = mysql_fetch_assoc($result)){
			if(isset($return_me[$row['shared_user_id']])){
				//then we just need the count...
				$return_me[$row['shared_user_id']]['count'] = $row['count']; 
			}else{
				$share_array = array();
				//we have to make it look like it came from the first query...
				$share_array['from_user_id'] = $row['owner_user_id'];
				$share_array['to_user_id'] = $row['shared_user_id'];
				$share_array['count'] = $row['count'];
				$share_array['active'] = 0;
				$share_array['from_name'] = $row['name'];
				$share_array['from_email'] = HybridUserInstance::user_id_from_email($row['owner_user_id']);
				$share_array['future_sharing'] = false;
				$return_me[$row['shared_user_id']] = $share_array; 
			}
			//$return_me[$row['to_user_id']] = $row;
	}


	return($return_me);

	}

/**
 * Creates a zip file of all of the recordings passed in, without an recordings array argument
 * it returns a zip file with all of this users recordings.
 * requires user_key to run..
 * @return string $zip_file_location 
 */
	function makeZip($user_key){
	
		$recording_array = $this->getRecordingList();

		foreach($recording_array as $recording_id => $one_recording){
			
			$aRecording = new Recording($recording_id);
			$file_name = $aRecording->decryptFileUserKey($user_key,$this->user_id);
			$one_recording['file_name'] = $file_name;
			$file_list[$recording_id] = $one_recording;
		}

		foreach($file_list as $recording_id => $one_recording){
			$file = $one_recording['file_name'];
			if(!file_exists($file)){

				echo "ERROR: Please contact support. If appears that your files have become corrupted";
				exit();
			}
		}	
		
		$zip_file_name = calculate_recording_path($this->user_id,'_archive').".zip"; 
		$zip = new ZipArchive();

		$zip_dir = 'recordings';


		$pretty_date = date('l jS \of F Y ');
		$app_name = $GLOBALS['app_name'];
		$count = count($file_list);

		$readme_txt = "This is a zip archive of your $app_name recordings made $pretty_date. You will find your recordings under the 'recording' directory. Inside you will find a backup of $count recordings.";

		$readme_file_name = calculate_recording_path($this->user_id,'README').".txt"; 
		
		$fh = fopen($readme_file_name, 'w') or die("cannot open file");

		fwrite($fh, $readme_txt);
		fclose($fh);

		if($zip->open($zip_file_name,ZIPARCHIVE::OVERWRITE) !== true){
			echo "User.class.php: makeZip: failed to create zip file ";
			exit();
		}

		$zip->addEmptyDir($zip_dir);
		$zip->addFile($readme_file_name, 'README.txt');

		foreach($file_list as $recording_id => $one_recording){	
			$name = preg_replace('#[^a-z0-9]#i','_', $one_recording['recording_name']). ".mp3";
			$zip->addFile($one_recording['file_name'],"$zip_dir/$name");
		}

		$zip->close();
		//the whole zip file should exist now...

		//lets erase the unencrypted mp3 files
		//rather than let the controller do it
		//we will let the controller erase the zip file!!
		foreach($file_list as $recording_id => $one_recording){
			unlink($one_recording['file_name']);
		}
	
		//unlink the readme
		unlink($readme_file_name);

		if(file_exists($zip_file_name)){
			return($zip_file_name);
		}else{
			echo "Error: User.class.php tried to make a zip and it failed, there is no file";
			exit();

		}

		
	}//end makeZip



/**
 * Return a list of all this users recordings
 * @return array $recording_array 
 */
	function getRecordingList(){


		$this_user_id = $this->user_id;
	
$full_record_list_sql = "
SELECT 
recording.user_id as owner_user_id,
recording.name as recording_name,
recording.deleted as deleted,
recording.locked as locked,
recording.id as recording_id
FROM `recording` 
WHERE 
recording.user_id = $this_user_id
ORDER BY recording.id DESC
";
		$result = mysql_query($full_record_list_sql)
			or die("User.class.php getSharingList() Error: full record list sql failed <br> $full_record_list_sql <br>". mysql_error());

		$return_me = array();
		while($row = mysql_fetch_assoc($result)){
			$return_me[$row['recording_id']] = $row;
		}

		return($return_me);

}

/**
 * Stop sharing a given recording by deleting the recordings keys. Note that once this is done, it means that 
 * the given users file access is totally deleted
 */
	function stopSharingOneRecording( $recording_id,$stop_user_id){
		
		$this_user_id = $this->user_id;

		if($this_user_id == $stop_user_id){
			echo "User.class.php Error: A user cannot delete thier own recording keys. They need to delete the file instead.";
			exit();
		}

		//this_user_id should ensure that no user can delete anothers recording		
		
		$search_sql = "
SELECT 
recording_keys.id as key_to_delete
  FROM `recording_keys` 
JOIN recording ON recording.id = recording_keys.recording_id
JOIN users ON recording.user_id = users.id
WHERE `recording_keys`.`user_id` = $stop_user_id AND `recording_keys`.`recording_id` = $recording_id AND users.id = $this_user_id
";
	
		$result = mysql_query($search_sql)
			or die("User.class.php stopSharingOneRecording() Error: shared record list sql failed <br> $search_sql <br>". mysql_error());

		if($row = mysql_fetch_assoc($result)){
			//then we know what the delete id is, and that this user has the right to delete it..
			$to_delete =  $row['key_to_delete'];
			$delete_sql = "DELETE FROM `recording_keys` WHERE `recording_keys`.`id` = $to_delete";
			mysql_query($delete_sql)
				or die("User.class.php stopSharingOneRecording() Error: delete sql failed <br> $delete_sql <br>". mysql_error());
			
		}



	}

/**
 * Given another user_id this lists the recordings that are shared with that user.
 * This ignores the future sharing status.
 * @TODO without an id, this should return a list of the sharing status as "is shared with anyone"
 * @return array $share_array an array of the users that this user is sharing with.
 */
	function getSharedRecordingList($other_user_id){

		if($other_user_id == 0){
			//TODO add SQL to make this get the recording status of recordings from the perspective of -every- other user
			echo "User.class.php: getSharedRecordingList(): error no user id given";
			exit(); 
		}


	
		$return_me = $this->getRecordingList();
		$this_user_id = $this->user_id;

		$shared_record_list_sql = "
SELECT 
recording.user_id as owner_user_id,
recording.name as recording_name,
recording.deleted as deleted,
recording.locked as locked,
recording.id as recording_id,
recording_keys.user_id as shared_user_id,
users.name,
users.id
FROM `recording` 
JOIN recording_keys on recording.id = recording_keys.recording_id
JOIN users on recording_keys.user_id = users.id
WHERE recording.user_id != recording_keys.user_id AND
recording.user_id = $this_user_id AND recording_keys.user_id = $other_user_id
";

		$result = mysql_query($shared_record_list_sql)
			or die("User.class.php getSharedRecordingList() Error: shared record list sql failed <br> $shared_record_list_sql <br>". mysql_error());

		foreach($return_me as $recording_id => $record_array){
			$record_array['shared'] = false;//assume there is no sharing
			$return_me[$recording_id] = $record_array;
		}

		while($row = mysql_fetch_assoc($result)){
			$recording_id = $row['recording_id'];
			$return_me[$recording_id]['shared'] = true;		
		}

		return($return_me);

	}


/**
 * Returns an array of the users (name and email) that this user is sharing data with, indexed by user_id
 * @return array $share_array an array of the users that this user is sharing with.
 */
	function getSharingList(){

		$this_user = $this->user_id;

		if($this_user > 0){
			//then it is a real id
		}else{
			//then this user has not been saved yet
			return(array());
		}

		$share_sql = "
SELECT 
from_user_id,
to_user_id,
active,
name as to_name
 FROM `user_user_access` as uua
JOIN users on uua.to_user_id = users.id
WHERE active = 1 AND
from_user_id = $this_user
";

	$result = mysql_query($share_sql)
			or die("User.class.php getSharingList() Error: share sql failed <br> $share_sql <br>". mysql_error());

	$return_me = array();
	while($row = mysql_fetch_assoc($result)){

		if(User::sharingVerify($row['to_user_id'],$this_user)){
			$row['future_sharing'] = true;
			$row['to_email'] = HybridUserInstance::user_id_from_email($row['to_user_id']);
			$return_me[$row['to_user_id']] = $row;
		}
	}

$recording_share_sql = "
SELECT 
recording.user_id as owner_user_id,
recording.id as recording_id,
recording_keys.user_id as shared_user_id,
COUNT(recording_keys.user_id) as count,
users.name
FROM `recording` 
JOIN recording_keys on recording.id = recording_keys.recording_id
JOIN users on recording_keys.user_id = users.id
WHERE recording.user_id != recording_keys.user_id AND
recording.user_id = $this_user
GROUP BY recording_keys.user_id";


	$result = mysql_query($recording_share_sql)
			or die("User.class.php getSharingList() Error: recording_share sql failed <br> $recording_share_sql <br>". mysql_error());

	while($row = mysql_fetch_assoc($result)){
			if(isset($return_me[$row['shared_user_id']])){
				//then we just need the count...
				$return_me[$row['shared_user_id']]['count'] = $row['count']; 
			}else{
				$share_array = array();
				//we have to make it look like it came from the first query...
				$share_array['from_user_id'] = $row['owner_user_id'];
				$share_array['to_user_id'] = $row['shared_user_id'];
				$share_array['count'] = $row['count'];
				$share_array['active'] = 0;
				$share_array['to_name'] = $row['name'];
				$share_array['to_email'] = HybridUserInstance::user_id_from_email($row['shared_user_id']);
				$share_array['future_sharing'] = false;
				$return_me[$row['shared_user_id']] = $share_array; 
			}
			//$return_me[$row['to_user_id']] = $row;
	}


	return($return_me);

	}


/**
 * Mark a user as having paid until some date, using some method
 */
	function paidUntil($payment_type, $payment_code = '', $good_til = 0, $recording_limit = 0){

		//good_til is a year unless otherwise marked
		//payment code is not needed unless $payment_type is 'code'
		//this is for handling subscription codes.

		$this->pay_status = $payment_type;
		$this->pay_code = $payment_code;
		if($good_til == 0){
			$this->pay_good_til = date('Y-m-d', strtotime("+1 year"));
		}else{
			$this->pay_good_til = $good_til;

		}
		$this->recording_limit = $recording_limit;

		//TODO some kind of payment logging system here!!
		

	}


/**
 * an admin function to test that sharing is working cryptographically.
 * @return boolean $success
 */
	static function sharingVerify($to_user_id,$from_user_id){

		//find the user_user_access record for the sharing
		//get $from_user_id's public key using the same sql because you are that cool
		//remember that the signature is base64 encoded if you did that in the sharing_sign function
		//use the public key to verify the signature field
		//return true if its a valid signature...
		
		//for now just return true...

$share_check_sql = "
SELECT 
from_user_id,
to_user_id,
signature,
active,
public_key as from_public_key 
FROM `user_user_access` as uua JOIN users as from_user on from_user.id = uua.from_user_id
WHERE 
uua.from_user_id = $from_user_id 
AND
uua.to_user_id = $to_user_id";

	$result = mysql_query($share_check_sql)
			or die("Error: check sql failed <br> $share_check_sql". mysql_error());

	if($row = mysql_fetch_assoc($result)){
		$from_user_pub_key = $row['from_public_key'];
		$signature_to_verify = base64_decode($row['signature']);

		$openssl_result = openssl_verify(
					$to_user_id,
					$signature_to_verify,
					$from_user_pub_key,
					"SHA256"
					);

		if($openssl_result == 1){
			//it verifies
			return(true);
		}else{
			return(false);
		}

	}else{
		//no such permission is even claimed... the simple case!!
		return(false);
	}

		
		return(true);

	}

/**
 * Uses the users user_key to get the private key, and then signs a entry in the sharing table.
 * Signed sharing entries ensure that new recordings can be automatically encrypted for access 
 * by both of the owner and the sharee.
 * @param integer $to_user_id the user id the user wants to share with
 * @param string $user_key the users user_key which unlocks the users private key.
 */
	function sharingSign($to_user_id,$user_key){

		$from_user_id = $this->user_id;

		$priv_key = $this->getPrivateKey($user_key);
		$success = openssl_sign($to_user_id,$signature,$priv_key,"SHA256");
		$signature = base64_encode($signature);

		$uu_access_sql = "
REPLACE INTO `user_user_access` (
`id` ,
`from_user_id` ,
`to_user_id` ,
`signature` ,
`created` ,
`active`
)
VALUES (
NULL , '$from_user_id', '$to_user_id', '$signature', CURRENT_TIMESTAMP, '1' );
";

		mysql_query($uu_access_sql) 
			or die("User.class.php: sharing_sign() failed to save signature with <br> $uu_access_sql".mysql_error());

		//thats it... signed and saved.

	}

/**
 * This function gets a private key, from a plaintext phone number. This is a problematic function since phone numbers are much simpler attack vector than directed openid identities. We need a way to allow users to turn this off if they are cryptographically paranoid. Without this, there is no way to play the mp3s over the phone to a user.
 * @param string $phone the users plaintext phone number
 */
	public static function getPrivatekeyFromPhone($phone){
	
		
		$phone_hash =  hash("sha512", $phone);


		$find_phone_sql = "
SELECT `id` , `user_id`, `enc_priv_key`
FROM `phones` 
WHERE `phone_hash` = '$phone_hash' AND active = 1
";

		$result = mysql_query($find_phone_sql) 
				or die("User.class.php: could not get user from phone: <br>$find_phone_sql <br>".mysql_error());

		if($row = mysql_fetch_array($result)){
			$enc_priv_key = $row['enc_priv_key'];
			$priv_key = Decrypt($enc_priv_key,$phone);
			return($priv_key);


		}else{
			return(false);
		}

	}





/**
 * Gets a user id, when given a plaintext phone number. Does all the work to handle the hashing of the phone numbers.
 * @param string $phone the users plaintext phone number
 */
	public static function getUserFromPhone($phone){
	
		
		$phone_hash =  hash("sha512", $phone);


		$find_phone_sql = "
SELECT `id` , `user_id`
FROM `phones` 
WHERE `phone_hash` = '$phone_hash' AND active = 1
";

		$result = mysql_query($find_phone_sql) 
				or die("User.class.php: could not get user from phone: <br>$find_phone_sql <br>".mysql_error());

		if($row = mysql_fetch_array($result)){
			return($row['user_id']);
		}else{
			return(false);
		}

	}


/**
 * This user allows you find an array of users that match certain information recorded in this 
 * particular object. I am not sure we need this.
 */
    public function find()
    {
        $sql = "SELECT * FROM users";
        
        // This array will hold the where clause
        $where = array();
        
        // Using PHP 5's handy new reflection API
        $class = new ReflectionClass('User');
        // Get all of DO_User's vairable (or property) names
        $properties = $class->getProperties();
        
        // Loop through the properties
        for ($i = 0; $i < count($properties); $i++){
            $name = $properties[$i]->getName();
            if ($this->$name != ''){
                // Add this to the where clause
                $where[] = "`" . $name . "`='" . mysql_escape_string($this->$name) . "'";
            }
        }
        
        // If we have a where clause, build it
        if (count($where) > 0){
            $sql .= " WHERE " . implode(' AND ', $where);
        }
            
        $rs = mysql_query($sql);
        include_once('ReadOnlyResultSet.class.php');
        return new ReadOnlyResultSet($rs);
    }
}
?>
