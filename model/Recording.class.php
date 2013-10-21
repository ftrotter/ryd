<?php
/**
 * contains the recording model class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */
require_once('../config.php');
require_once('../util/enchelp.php');
require_once('../util/functions.php');

/**
 *  The Recording Model handles the recordings record in the database, and file on the filesystem or in the cloud
 * @package YDA
 */
class Recording {

		
		var $recording_id;
		var $user_id;
		var $source;
		var $create;
		var $name;
		var $recording_number;
		var $deleted;
		var $deleted_when;
		var $locked;
		var $data;

/**
 * This is contructor accepts a recording id to load a existing recording.
 * If you want to create a new recording, just do not pass in an id.
 * @param integer $id the recording id
 * @return object recording object
 */
		function __construct($id = null){

		if(!is_null($id)){	

			$id = mysql_real_escape_string($id);

			$this->recording_id = $id;

			$recording_sql = "
SELECT *
FROM `recording`
WHERE id = '$id'";


			$result = mysql_query($recording_sql) or die("ERROR: cannot check recording table: sql = $recording_data_sql <br> error = ".mysql_error());
			
			$row = mysql_fetch_array($result);

			//var_export($row);
			$this->user_id = $row['user_id'];
			$this->source = $row['source'];
			$this->created = $row['created'];
			$this->name = $row['name'];
			$this->recording_number = $row['recording_number'];
			$this->deleted = $row['deleted'];
			$this->deleted_when = $row['deleted_when'];
			$this->locked = $row['locked'];

			$recording_key_data_sql = "
SELECT *
FROM `recording`
LEFT JOIN recording_keys ON recording.id = recording_keys.recording_id
WHERE recording.id = $id
";


			$result = mysql_query($recording_key_data_sql) or die("ERROR: cannot check recording key table: sql = $recording_key_data_sql <br> error = ".mysql_error());
			
			while($row = mysql_fetch_assoc($result)){
				$this->data['keys'][$row['id']] = $row;

			}

			$recording_comment_data_sql = "
SELECT *
FROM `recording`
LEFT JOIN recording_comment ON recording.id = recording_comment.recording_id
WHERE recording.id = $id
";


			$result = mysql_query($recording_comment_data_sql) or die("ERROR: cannot check recording comment table: sql = $recording_comment_data_sql <br> error = ".mysql_error());
			
			while($row = mysql_fetch_assoc($result)){
				$this->data['comment'][$row['id']] = $row;

			}

			$recording_transcription_data_sql = "
SELECT *
FROM `recording`
LEFT JOIN recording_transcription ON recording.id = recording_transcription.recording_id";


			$result = mysql_query($recording_transcription_data_sql) or die("ERROR: cannot check recording comment table: sql = $recording_transcription_data_sql <br> error = ".mysql_error());
			
			while($row = mysql_fetch_assoc($result)){
				$this->data['transcription'][$row['id']] = $row;

			}
		}//then I am starting from scratch...
		
		}    
 //encryption functions...

/**
 * decrypt a file using the users key.
 * @param string $user_key the users key string
 * @param string $user_id the user id of the user, if it is not the owner of the recording
 * @return string the location of the file that we just decrypted
 */
		function decryptFileUserKey($user_key,$user_id = 0){


			if($user_id == 0){
				//then use the owner of the recording...
				$user_id = $this->user_id;
			}

			$password = $this->_getFilePassword($user_key,$user_id);
			$file_name = $this->decryptFile($password,$user_id);

			return($file_name);

		}

/**
 * decrypt a file using the symetric key, whereever it came from..
 * All other functions should use this internally. It allows any password
 * and supports any storage bucket...
 * @param string $sym_key
 * @param string $user_id the user id of the user, if it is not the owner of the recording
 * @return string the location of the file that we just decrypted
 */
		function decryptFile($sym_key,$dir = ''){


			if(strlen($dir) == 0){
				//then use the owner of the recording...
				$dir = $this->user_id;
			}
			//note that the recording path is based on the -owner- of the recording...
			$file_name = calculate_recording_path($this->user_id,$this->recording_id);
			$enc_file_name = $file_name . ".enc";
			$file_name = $file_name .".mp3";


			if(!file_exists($enc_file_name)){
				//this means that the local cache has been cleaned..
				//we need to load the file from
				//either S3 or Rackspace File... depending...
				//TODO implement permenant Rackspace Cloud Files

				$this->loadFromRackspace($dir);
			

			//	echo "Error getting this file!!, it has been removed from the cache...<br> Hurry up and implement cloudfile will you!!!";
			//	exit();


			}

			Decrypt_File($enc_file_name,$file_name, $sym_key);

			return($file_name);



	}


/**
 * decrypt a file using the users plaintext phone number
 * @param string $phone the users plaintext phone number
 * @param string $user_id the user id of the user, if it is not the owner of the recording
 * @return string the location of the file that we just decrypted
 */
		function decryptFileFromPhone($phone,$user_id = 0){


			if($user_id == 0){
				//then use the owner of the recording...
				$user_id = $this->user_id;
			}
			//note that the recording path is based on the -owner- of the recording...

			$password = $this->_getFilePasswordFromPhone($phone,$user_id);
			$file_name = $this->decryptFile($password,$user_id);


			return($file_name);

		}

/**
 * Stop all sharing for this recording
 */
		function stopAllSharing(){

			//GET list to delete

			$owner_id = $this->user_id;
			$recording_id = $this->recording_id;
$delete_list_sql = "SELECT recording_keys.id as delete_me  FROM `recording`
JOIN recording_keys ON recording_keys.recording_id = recording.id
WHERE recording_keys.user_id != recording.user_id AND
recording.user_id = $owner_id AND 
recording.id = $recording_id";

			$result = mysql_query($delete_list_sql) 
				or die("ERROR: Recording.class.php stopAllSharing: could not get list to delete sql = $delete_list_sql <br> error = ".mysql_error());
			
			while($row = mysql_fetch_assoc($result)){
				$delete_us[] = $row['delete_me'];
			}

			foreach($delete_us as $delete_me_id){

				$delete_sql = "DELETE FROM `recording_keys` WHERE `recording_keys`.`id` = $delete_me_id";
				mysql_query($delete_sql) 
					or die("ERROR: Recording.class.php stopAllSharing: could no do delete sql = $delete_sql <br> error = ".mysql_error());

			}

			//they should all be gone!!

		}

/**
 * Shares this recording with a given user id
 * @param string $owner_key the secret key of the owner of the shared file
 * @param integer $target_user_id the id of the user we want to share with 
 */
		function shareRecording($owner_key,$target_user_id){
		
			//dont do it if this recording is locked!!
			if($this->locked){
				$record_id = $this->recording_id;
				echo "ERROR: Recording.class.php: you have attempted to share a locked recording #$record_id";
				exit();

			}
	
			//first we get the password from the user...
			//note that we do -not- user the target user id for this process...
			$password = $this->_getFilePassword($owner_key,$this->user_id);

			//now we encrypt the password with the target users public key...
			$User = new User($target_user_id);
			
			$encrypted_password = $User->publicEncrypt($password);

			//now we save the keys
			$success = $this->_saveRecordingKeys($encrypted_password,$target_user_id);

			return($success);

		}


/**
 * given a plaintext phone number and a user id,
 * get the password need to symmetrically decrypt the file
 * @param string $phone the plaintext phone of the user
 * @param integer $user_id the person whos phone number unlocks the password..
 * @return string the password to unlock the file
 */
		function _getFilePasswordFromPhone($phone,$user_id){
	
			$recording_id = $this->recording_id;
			//maybe this should be a static function
			//and require a recording id?
			//not sure...
			$recording_key_sql = 
"
SELECT *
FROM `recording_keys`
WHERE `user_id` = $user_id
AND `recording_id` = $recording_id
";
			$result = mysql_query($recording_key_sql) or die("Recording.class.php: cannot load recording key". mysql_error());

			//this will return nothing if a user does not have access to a file...
			//this would be a hacking attempt then and we should log it
			//TODO lock this down...
			$row = mysql_fetch_assoc($result);
			$encrypted_password = base64_decode($row['enc_key']);

			$user = new User($user_id);
			$password = $user->privateDecryptPhone($encrypted_password,$phone);
			return($password);
		}

	
				//$user_key comes from the session for the current user
				//$user_id is the id of the current user...

/**
 * given a user key and a user id,
 * get the password need to symmetrically decrypt the file
 * @param string $user_key the plaintext phone of the user
 * @param integer $user_id the person whos phone number unlocks the password..
 * @return string the password to unlock the file
 */
		function _getFilePassword($user_key,$user_id){
	
			$recording_id = $this->recording_id;
			//maybe this should be a static function
			//and require a recording id?
			//not sure...
			$recording_key_sql = 
"
SELECT *
FROM `recording_keys`
WHERE `user_id` = $user_id
AND `recording_id` = $recording_id
";
			$result = mysql_query($recording_key_sql) or die("Recording.class.php: cannot load recording key". mysql_error());

			//this will return nothing if a user does not have access to a file...
			//this would be a hacking attempt then and we should log it
			//TODO lock this down...
			$row = mysql_fetch_assoc($result);
			$encrypted_password = base64_decode($row['enc_key']);

			$user = new User($user_id);
			$password = $user->privateDecrypt($encrypted_password,$user_key);
			return($password);
		}

/**
 * helper function that saves an encrypted password to the database.
 * @param string $encrypted_password the encrypted password
 * @param integer $user_id the user who is saving the password
 * @return string the password to unlock the file
 */
		function _saveRecordingKeys($encrypted_password, $user_id = 0){

			if($user_id == 0){
				$user_id = $this->user_id;
			}

			$encrypted_password_base_64 = base64_encode($encrypted_password);
	
			$recording_key_sql = 
"
REPLACE INTO `recording_keys` (
`id` ,
`user_id` ,
`recording_id` ,
`enc_key`
)
VALUES (
NULL , '$user_id', '$this->recording_id', '$encrypted_password_base_64'
);
";

			mysql_query($recording_key_sql) or die("Recording.class.php: could not create record of recording key with $recording_key_sql ".mysql_error());

			return(true);

		}


/**
 * encrypt the file using user information and do all the user work needed to make that make sense
 */
		function initialEncryptFileUser(){


			$password = $this->initialEncryptFile($this->user_id);

			$user = new User($this->user_id);

			//save the password to the database in a way that only the owning user can read it
			$encrypted_password = $user->publicEncrypt($password);
			$this->_saveRecordingKeys($encrypted_password);


			// get the list of users that the user trusts
			$share_user_list = $user->getSharingList();
			//for each user publicencrypt the file password
			foreach($share_user_list as $share_user_id => $user_info_array){
		
				$UserToShareWith = new User($share_user_id);
				$share_encrypted_password = $UserToShareWith->publicEncrypt($password);
				$this->_saveRecordingKeys($share_encrypted_password,$share_user_id);
			// use _saveRecordingKeys to save the access
			}



		}
		
/**
 * given an uncrypted file, loaded to the right location on the file system, as per the handy 
 * calculate_recording_path function, encrypt that file and load it to rackspace/amazon
 */
		function initialEncryptFile($dir = NULL,$password_override = NULL){
			if(is_null($dir)){
				$dir = $this->user_id;
			}
	

			$file_name = calculate_recording_path($dir,$this->recording_id);			

			if(is_null($password_override)){
				$password = generatePassword(50);
			}else{
				$password = $password_override;
			}
			$enc_file_name = $file_name . ".enc";		

			Encrypt_File($file_name,$enc_file_name, $password);

				
			//save the file to rackspace...
			syslog(LOG_INFO,"Recording.class.php: initialEncrypt 3 dir is $dir");
			$this->saveToRackspace($dir);
						
			//erase the unencrypted file
			//unlink($file_name);
			rename($file_name, $file_name . ".old");

			return($password);
		
		}





/**
 * Once a file has been deleted from the local cache, it must be loaded from rackspace files
 * this is half of the usage of rackspace as an unlimited hard drive.
 */
		function loadFromRackspace($dir = ''){
			// downloads an encrypted file from the rackspace cloud
			require_once("../util/cloudfiles/cloudfiles.php");

			if(strlen($dir) < 1){
				$dir = $this->user_id;
			}	

			$local_file_name = calculate_recording_path($dir,$this->recording_id) . ".enc";

			if(file_exists($local_file_name)){
				//then this is not needed...
				//return();
				//run for now... just to check...
				//TODO stop generating secondary files...
				$local_file_name = $local_file_name . ".frm_rks_files";
			}

			$auth = new CF_Authentication(
				$GLOBALS['rackspace_user'],
				$GLOBALS['rackspace_key']);
			$auth->authenticate();
			$conn = new CF_Connection($auth);
			
			$container_name = $GLOBALS['rackspace_container'] . "_". $dir;				
			//we know the container exists
			//we created it earlier. If it does not exist
			//this will generate an error... and it should....
		ob_start();
			$container = $conn->get_container($container_name);
			$object = $container->get_object($this->recording_id . ".enc");
			$object->save_to_filename($local_file_name);
		ob_end_clean();


		}


/**
 * This is the untrash function, this moves a recording back into the inbox
 */
		function untrashRecording(){
	
			$recording_id = $this->recording_id;
			//sql delete code
			$delete_sql_array = array(
					"UPDATE `recording` SET 
							`deleted` = '0',
							`deleted_when` = NOW( ) 
						WHERE `recording`.`id` = $recording_id
					",	
			);

			foreach($delete_sql_array as $sql_to_delete){
				mysql_query($sql_to_delete) or 
					die("Recording.class.php: Error: tried to trash recording with $sql_to_delete failed bc <br>"
						.mysql_error());
				
			}

		}



/**
 * This is the trash function, this will move a recording into the "trash" which will deleted by the system after a long time... 
 */
		function trashRecording(){
	
			$recording_id = $this->recording_id;
			//sql delete code
			$delete_sql_array = array(
					"UPDATE `recording` SET 
							`deleted` = '1',
							`deleted_when` = NOW( ) 
						WHERE `recording`.`id` = $recording_id
					",	
			);

			foreach($delete_sql_array as $sql_to_delete){
				mysql_query($sql_to_delete) or 
					die("Recording.class.php: Error: tried to trash recording with $sql_to_delete failed bc <br>"
						.mysql_error());
				
			}

		}

/**
 * This is the no-shit delete function that both removes the database entry, the cache file, and the rackspace copy...
 */
		function deleteRecording($dir = '',$name = null){
	
			
			if(strlen($dir) == 0){
				//then use the owner of the recording...
				$dir = $this->user_id;
			}	
	
			//file delete code

			$base_file_name = calculate_recording_path($dir,$this->recording_id);
			$enc_file_name = $base_file_name . ".enc";
			$unenc_file_name = $base_file_name . ".mp3";
			$old_file_name = $base_file_name . ".old";
			$rks_backup_file_name = $base_file_name . ".frm_rks_files";

			$file_name_array = array(
					$base_file_name, 
					$enc_file_name, 
					$unenc_file_name, 
					$old_file_name, 
					$rks_backup_file_name, 
			);
		
			foreach($file_name_array as $file_to_delete){
				if(file_exists($file_to_delete)){//and many will not 
					unlink($file_to_delete);
				}
			}

			//rackspace delete code
			$this->_deleteFromRackspace($dir,$name);
			$recording_id = $this->recording_id;
			//sql delete code
			$delete_sql_array = array(
					"DELETE FROM `recording` WHERE `recording`.`id` = $recording_id",	
					"DELETE FROM `recording_keys` WHERE `recording_keys`.`recording_id` = $recording_id",
					"DELETE FROM `recording_comment` WHERE `recording_comment`.`recording_id` = $recording_id",
			);

			foreach($delete_sql_array as $sql_to_delete){
				mysql_query($sql_to_delete) or 
					die("Recording.class.php: Error: tried to delete recording with $sql_to_delete failed bc <br>"
						.mysql_error());
				
			}

		}
/**
 * Deletes the file from rackspace, called from deleteRecording 
 */
		function _deleteFromRackspace($dir,$name){

			require_once("../util/cloudfiles/cloudfiles.php");
			
			$auth = new CF_Authentication(
				$GLOBALS['rackspace_user'],
				$GLOBALS['rackspace_key']);
			$auth->authenticate();
			$conn = new CF_Connection($auth);
			
			$container_name = $GLOBALS['rackspace_container'] . "_". $dir;				
		//curl_exec is generating text that is being dumped to our screen	
		//we have to stop it...
		//output buffer is a hack, but I cant think of anything else fast...
		ob_start();
			$container = $conn->create_container($container_name);

		//	$object = $container->get_object($this->recording_id . ".enc");
			$container->delete_object($this->recording_id . ".enc");

		ob_end_clean();


		}

/**
 * When a file is initially imported, it must be saved to rackspace, this function does that. 
 */
		function saveToRackspace($dir){
			// uploads an encrypted file to the rackspace cloud

	syslog(LOG_INFO,"Recording.class.php: saveToRackspace dir is $dir");
			

			require_once("../util/cloudfiles/cloudfiles.php");
			
			$auth = new CF_Authentication(
				$GLOBALS['rackspace_user'],
				$GLOBALS['rackspace_key']);
			$auth->authenticate();
			$conn = new CF_Connection($auth);
			
			$container_name = $GLOBALS['rackspace_container'] . "_". $dir;				
		ob_start();
			$container = $conn->create_container($container_name);

			//get the encrypted local filename...
			$local_file_name = calculate_recording_path($dir,$this->recording_id) . ".enc";
			
			$object = $container->create_object($this->recording_id . ".enc");
			$object->load_from_filename($local_file_name);
		ob_end_flush();
		}


/**
 * This intelligent save function does the work to save a given recording to the database.
 * It checks to make sure the record is healthy. Then uses an "REPLACE INTO" to save the 
 * database record, which works if its an intial save or an overwrite.
 */
		function save(){


			if(!isset($this->user_id)){
				echo "ERROR: trying to save a recording without a user_id";
				exit(1);
			}

			if(!isset($this->source)){
				echo "ERROR: trying to save a recording without a source";
				exit(1);
			}



			if(!isset($this->name)){
				//we do not always have a good source for a name!!
				//Lets just tell the user some useful information...
				//this one has "13th"
				//$this->name = "Recorded ". date('l, F jS, Y');			
				//this one has "13"
				$this->name = date('l, F j, Y');			


			}

			if(!isset($this->recording_number)){
				$user_id = $this->user_id;
				$highest_sql = "SELECT MAX(recording_number) as highest  FROM `recording` WHERE `user_id` = $user_id GROUP BY `recording`.`user_id`";
				$result = mysql_query($highest_sql) or die("Recording.class.php ERROR: cannot add to recording table: sql = $new_recording_sql <br> error = ".mysql_error());

				if($row = mysql_fetch_assoc($result)){
					$highest = $row['highest'];
					$this->recording_number = $highest + 1;
				}else{
					$this->recording_number = 1;
				}

			}

			if(!isset($this->deleted)){
				echo "ERROR: trying to save a recording without a deleted status";
				exit(1);
			}

			if(!isset($this->deleted_when)){
				$this->deleted_when = "0000-00-00 00:00:00";
			}

			if(!isset($this->locked)){
				$this->locked = 0;
			}


			if(isset($this->recording_id)){
				$recording_id = "'$this->recording_id'";
			}else{
				$recording_id = 'NULL';
			}

			if(isset($this->created)){
				$created = "'$this->created'";
			}else{
				$created = 'NULL';
			}


			$this->name = mysql_real_escape_string($this->name); 

			$new_recording_sql = "REPLACE INTO `recording` (
`id` ,
`user_id` ,
`source` ,
`created` ,
`name`,
`recording_number`,
`deleted`,
`deleted_when`,
`locked`
)
VALUES (
$recording_id , '$this->user_id', '$this->source', $created, 
'$this->name', '$this->recording_number', '$this->deleted', '$this->deleted_when', '$this->locked'
);";

			mysql_query($new_recording_sql) or die("Recording.class.php ERROR: cannot add to recording table: sql = $new_recording_sql <br> error = ".mysql_error());

			$this->recording_id = mysql_insert_id();

			return(true);


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
				  	"User id = $this->user_id <br>".
				  	"Source = $this->source <br>";
				  	"Created = $this->created <br>";
				  	"Deleted = $this->deleted <br>";
				  	"Locked = $this->locked <br>";

			foreach($this->data as $label => $data){
				foreach($data as $deeplabel => $deepdata){
					$result .= "&nbsp;&nbsp;&nbsp; $label: $deeplabel = $deepdata <br>";
				}
			}

			return($result);
		}
}//class Recording

/**
 * Calculates the recording file path.
 * this globally defined function allows for consistent placement of recordings on the file system
 * @param integer $user_id the user id of the owner of the file
 * @param integer $recording_id the id of the specific recording 
 * @return string $file_name the location of the file on the disk
 * @package YDA
 */
function calculate_recording_path($dir,$recording_id){

	if(isset($GLOBALS['tmp_dir'])){
		$path = $GLOBALS['tmp_dir'];
	}else{
		$path = "/var/www/tmp/";
	}
	

	$user_path = $path . $dir . "/";
	if(!file_exists($user_path)){
		mkdir($user_path);
	}
			
	$file_name = $user_path . $recording_id;

	return($file_name);
}

?>
