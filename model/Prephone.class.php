<?php
/**
 * contains the prephone model class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once('../config.php');
require_once('../util/functions.php');
require_once('../util/enchelp.php');
require_once('../model/Recording.class.php');


/**
 *  The Prephone model lives to create a space to record when a user 
 * making recordings before becoming a user...
 * @package YDA
 */
class Prephone {

		var $prephone_id;
		var $phone;
		var $sym_key;
		var $created;
		var $active;

/**
 * This is contructor accepts a phone
 * @param string $phone
 * @return object prephone object
 */
		function __construct($phone){
	

			$phone = PhoneFormat::forStorage($phone);
			$phone = mysql_real_escape_string($phone);
$phone_sql = "SELECT *
FROM `prephone`
WHERE `phone` = '$phone'";

		$result = mysql_query($phone_sql) or die("Could not search for prephone with $phone_sql :".mysql_error());

		if($row = mysql_fetch_array($result)){
			$this->active = $row['active'];
			$this->created = $row['created'];
			$this->sym_key = $row['sym_key'];
			$this->prephone_id = $row['id'];
		}    

		$this->phone = $phone;

	}

/**
 * addRecording accepts a recording_id and associates it with this prephone number
 */
		function addRecording($recording_id){
			$recording_id = mysql_real_escape_string($recording_id);
			$prephone_id = $this->prephone_id;
			$new_record_to_prephone_sql = "
INSERT INTO `prephone_to_recording` (
`id` ,
`prephone_id` ,
`recording_id`
)
VALUES (
NULL , '$prephone_id', '$recording_id'
);
";

			mysql_query($new_record_to_prephone_sql) or die("could not associate the recording and the prephone with $new_record_to_prephone_sql".mysql_error());

			

		}


/**
 * migrateToUser takes a user id as an argument and migrates all recordings
 * made with that prephone to that user... uses prephone_to_recording to 
 * figure out what recordings are associated with a given prephone... 
 */
		function migrateToUser($user_id){

			$user = new User($user_id);
			$prephone_id = $this->prephone_id;
		
			if(is_numeric($prephone_id) && $prephone_id > 0){
				//we will continue this function
			}else{
				//we will return rather than show an error
				return;
			}
			
	
			$dir = 'prephone_'.$prephone_id;
			$sym_key = $this->sym_key;


$recording_list_sql = "
SELECT *
FROM `prephone_to_recording`
WHERE prephone_id = $prephone_id
ORDER BY `id` DESC
";

		$result = mysql_query($recording_list_sql) or die("Could not get list of recordings with $recording_list_sql".mysql_error());

			while($row = mysql_fetch_array($result)){
				$old_recording_id = $row['recording_id'];
				$Oldrecording = new Recording($old_recording_id);
				$prephone_id = $row['prephone_id'];

				$file_name = $Oldrecording->decryptFile($sym_key,$dir);
				
			
				$Newrecording = new Recording();
				$Newrecording->user_id = $user->user_id;
				$Newrecording->source = 'was_pre';
				$Newrecording->name = 'Recorded before signup';
				$Newrecording->deleted = 0;

				$Newrecording->save();//will generate an id;
				$new_recording_id = $Newrecording->recording_id;
		
				$new_file_name = calculate_recording_path($user->user_id,$new_recording_id);

				if(!rename($file_name, $new_file_name)){ 
			    		echo "There was an error moving the file, please try again!";
			    		echo "tried moving : $file_name to $new_file_name";
					exit();
				}

				
				$Newrecording->initialEncryptFileUser();
				
				$Oldrecording->deleteRecording($dir);
			}

		//This can only happen one time... and we delete after it does...

$delete_links_sql = "DELETE FROM `prephone_to_recording` WHERE `prephone_id` = $prephone_id";
		$result = mysql_query($delete_links_sql) or die("Could not delete links with $delete_links_sql".mysql_error());
$delete_prephone_sql = "DELETE FROM `prephone` WHERE `id` = $prephone_id";
		$result = mysql_query($delete_prephone_sql) or die("Could not delete prephone with $delete_prephone_sql".mysql_error());


		}


/**
 * This intelligent save function does the work to save a given prephone to the database.
 * It checks to make sure the prephone is healthy. Then uses an "REPLACE INTO" to save the 
 * database record, which works if its an intial save or an overwrite.
 */
		function save(){



			if(isset($this->prephone_id)){
				$prephone_id = "'$this->prephone_id'";
				$get_id = false;
			}else{
				$prephone_id = 'NULL';
				$get_id = true;
			}

			if(isset($this->created)){
				$created = "'$this->created'";
			}else{
				$created = 'CURRENT_TIMESTAMP';
			}

			if(!isset($this->active)){
				$this->active = 1;
			}

			if(!isset($this->sym_key)){
				$this->sym_key = generatePassword(50);
			}



			$new_prephone_sql = "REPLACE INTO `prephone` (
`id` ,
`phone` ,
`sym_key` ,
`created` ,
`active`
)
VALUES (
$prephone_id , '$this->phone', '$this->sym_key', $created , '$this->active'
);";

			mysql_query($new_prephone_sql) or die("User.php ERROR: cannot add to prephone table: sql = $new_user_sql <br> error = ".mysql_error());


		if($get_id){
			$this->prephone_id = mysql_insert_id();
		}


			return(true);


		}

}
?>
