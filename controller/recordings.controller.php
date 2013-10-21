<?php
/**
 * contains the recordings controller.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */
require_once("../config.php");
require_once('Controller.class.php');
require_once('../model/User.class.php');
require_once('../model/Comment.class.php');
require_once('../model/Recording.class.php');

/**
 *  The recordings controller displays, plays and generally handles the recordings
 * This is the workhorse of the controllers, containing lots of key-based muinging of files etc etc. 
 * @package YDA
 */
class Controller_recordings extends Controller{

/**
 * Typical constructor.
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - Your Recordings");

		//sets the main menu tab to be active
		$this->data['main_menu']['Recordings']['active'] = true;

	}

/**
 * Displays the list of recordings, the main interface of the system.
 */
	function index(){
		//display the openid selector...
		$header = $GLOBALS['head'];
		$this->data['form_action'] = "/index.php/change/change/";
		$this->data['recordings_url'] = "/index.php/recordings/index/";

		$this->data['use_trash'] = $GLOBALS['use_trash'];

		if(isset($_SESSION['user_id'])){
			$user = new User($_SESSION['user_id']);
		}else{
			echo "Error: recordings/index needs a user_id. How did you get here without one?";
			exit();
		}
		if(isset($_SESSION['user_key'])){
			$user_key = $_SESSION['user_key'];
		}else{
			echo "Error: recordings/index needs an user_key. How did you get here without one?";
			exit();
		}
		$this->data['has_phone'] = $user->hasPhone($user_key);
		$this->data['is_owner'] = true;

		$this_user_id = $user->user_id;
		if(isset($_GET['regarding_uid'])){
			$regarding_user_id = $_GET['regarding_uid'];
		}else{
			$regarding_user_id = $this_user_id;
		}

		if(isset($_GET['trash'])){
			if($_GET['trash']){
				$this->data['is_trash'] = true;
				$trash_sql = "1";
			}else{
				$this->data['is_trash'] = false;
				$trash_sql = "0";
			}
		}else{
				$this->data['is_trash'] = false;
				$trash_sql = "0";
		}

// handling the count for trash...
		$trash_count_sql = "
SELECT
COUNT(*) as trash_count
FROM `recording`
WHERE deleted = 1 AND
recording.user_id = $this_user_id 
GROUP BY recording.user_id
";


		$trash_results = mysql_query($trash_count_sql) or die("recording.controller.php: could not query for trash recordings with $trash_count_sql". mysql_error());

		if($row = mysql_fetch_assoc($trash_results)){
			//if there are results at all, then the number is positive...
			$trash_count = $row['trash_count'];
		}else{
			$trash_count = 0;
		}		



		$recording_sql = "
SELECT
recording_keys.user_id,
recording_keys.recording_id,
sharing_keys.user_id as sharing_user_id,
users.name as sharing_user_name,
source,
recording.name,
recording.recording_number,
recording.locked,
recording.deleted,
created
FROM `recording_keys`
JOIN recording ON recording_keys.recording_id = recording.id
LEFT JOIN recording_keys AS sharing_keys ON sharing_keys.recording_id = recording.id
LEFT JOIN users ON users.id = sharing_keys.user_id
WHERE deleted = $trash_sql AND
recording_keys.user_id = $this_user_id AND
recording.user_id = $regarding_user_id 
ORDER BY created DESC
";


		$results = mysql_query($recording_sql) or die("recording.controller.php: could not query for recordings with $recording_sql". mysql_error());
	
		$recordings = array();

		$first_recording = true;
		// the big while... this loops over the recordings..
		while($row = mysql_fetch_assoc($results)){

			$this_recording = array();
			$rid = $row['recording_id'];

// handling comments...
		$comment_sql = "
SELECT
*
FROM `recording_comment`
WHERE
recording_comment.recording_id = $rid 
";


		$comment_results = mysql_query($comment_sql) or die("recording.controller.php: could not query for recording comment with $comment_sql". mysql_error());

		$comment_id = 0;
		$comment_text = '';  //friendly default text!!
		while($comment_row = mysql_fetch_assoc($comment_results)){
			//if there are results at all, then the number is positive...
			$comment_id = $comment_row['id'];
			$Comment = new Comment($comment_id);
			$comment_text = $Comment->getPlaintext();
			
		}

			if($first_recording){
				$this_recording['first_recording'] = true;
				$first_recording = false;
			
			}else{
				$this_recording['first_recording'] = false;
				
			}

			$this_recording['name'] = $row['name'];
			$this_recording['recording_number'] = $row['recording_number'];
			$this_recording['comments'] = '';
			$this_recording['mp3url'] = "/index.php/recordings/download?recording_id=$rid";
			$this_recording['downurl'] = "/index.php/recordings/download?recording_id=$rid&download=true";
			$this_recording['shareurl'] = "/index.php/sharing/onerecording?recording_id=$rid";
			$this_recording['deleteurl'] = "/index.php/recordings/remove?recording_id=$rid";
			$this_recording['trashurl'] = "/index.php/recordings/trash?recording_id=$rid";
			$this_recording['commentsaveurl'] = "/index.php/comments/save?recording_id=$rid";
			$this_recording['comment_id'] = $comment_id;
			$this_recording['comment_text'] = $comment_text;
			$this_recording['untrashurl'] = "/index.php/recordings/untrash?recording_id=$rid";
			$this_recording['is_locked'] = $row['locked'];
			$this_recording['lockurl'] = "/index.php/recordings/lockrecording?recording_id=$rid";
			$this_recording['unlockurl'] = "/index.php/recordings/unlockrecording?recording_id=$rid";
			if($this_user_id == $regarding_user_id){
				$this_recording['is_owner'] = true;
				$this->data['is_owner'] = true;
			}else{
				$this_recording['is_owner'] = false;
				$this->data['is_owner'] = false;
			}			

					
			$this_share = array();
			if($this_user_id != $row['sharing_user_id']){
				$this->data['display_name'] = $row['sharing_user_name'];
				$this_share['name'] = $row['sharing_user_name'];
				$this_share['user_id'] = $row['sharing_user_id'];
			}else{
				$this->data['display_name'] = 'Your';
			}
			

			if(!isset($recordings[$rid])){				
				$recordings[$rid] = $this_recording;
			}
			if(count($this_share) > 0){//sometimes the "share user" is myself, which means there is no share array
				$recordings[$rid]['share'][] = $this_share;
			}
			//this allows us to add multiples of sharing relationships
			//we all love multiple level arrays!!!
		}
		//TODO add loop to get recording comments here
		if(!isset($_GET['page'])){
			//if there is no paging, the we just return all of the recordings
			$this->data['recordings'] = $recordings;
		}else{
			//completely untested...
			$current_page = $_GET['page'];

			if(isset($_GET['page_size'])){
				$page_size = $_GET['page_size'];
			}else{
				$page_size = 5; //about right for the iphone
			}

			$total_recordings = count($recordings);
			$total_pages = ceil($total_recordings / $page_size);

			$chunked_recordings = array_chunk($recordings,$page_size, true);
			$new_recordings = $chunked_recordings[$current_page - 1]; //because we count from 0!!
			$this->data['recordings'] = $new_recordings;
			$this->data['current_page'] = $current_page;
			$this->data['page_size'] = $page_size;
			$this->data['last_page_text'] = ($current_page - 1 ) * $page_size . "-". ($current_page * $page_size ) - 1;
			$this->data['next_page_text'] = ($current_page + 1 ) * $page_size . "-". ($current_page + 1) * $page_size + $page_size;
 			

		}

$sharing_sql = "
SELECT 
users.name as user_name,
recording.user_id
 FROM `recording_keys` 
JOIN recording ON recording_keys.recording_id = recording.id
JOIN users ON recording.user_id = users.id
WHERE deleted = 0 AND
recording_keys.user_id = $this_user_id AND
recording.user_id != $this_user_id
ORDER BY created DESC
";
		$results = mysql_query($sharing_sql) or die("recording.controller.php: could not query for sharing with $sharing_sql". mysql_error());

		$sub_menu = array();
		//we put this in first so that it shows at the top...
		//$sub_menu['My Recordings']['url'] = '/index.php/recordings/index/';
		//whoops never mind.. we hard code this one in the template so it can have its own section...
		if($trash_count > 0){
			//then we need a link to my trash can!!
			$sub_menu['My Trash']['url'] = '/index.php/recordings/index/?trash=true';
			
		}
		$is_mine_active = true;
		while($row = mysql_fetch_assoc($results)){
			$this_menu_item = array();
			$this_menu_item['url'] = '/index.php/recordings/index/?regarding_uid='.$row['user_id'];
			if($row['user_id'] == $regarding_user_id){
				$this_menu_item['active'] = true;
				$is_mine_active = false;
			}else{
				$this_menu_item['active'] = false;
			}
			$sub_menu[$row['user_name']. "'s Recordings"] = $this_menu_item;
		}

		//$sub_menu['My Recordings']['active'] = $is_mine_active;

		array_reverse($sub_menu);	
		
		//this is a magic portion of the data array
		//that creates the left hand sub-menu
		if(count($sub_menu) > 0){ //do not display just "my recordings"
			$this->data['sub_menu'] = $sub_menu;	
		}
	}



/**
 * Allows recordings to be implemented.
 * @todo make a progress bar for file upload.
 * @todo use MP3 tagging to detect re-uploads.
 * @todo force human readable name on upload.
 */
	function upload(){
		//upload a particular mp3
		//sets the main menu tab to be active
		//disguise that its just this one function and give us a whole tab on the 
		//user interface!!!
		$this->data['main_menu']['Recordings']['active'] = false;
		$this->data['main_menu']['Upload']['active'] = true;

		//upload is a privilege for subscribers
		if(!$this->user->paid){
			return;
		}
		if(isset($_POST['upload'])){
		
		//	echo "We are seeing the POST";

			//lets do some error handling

			if($_FILES['mp3file']['error'] != UPLOAD_ERR_OK){
				//then we have an error
				$error = $_FILES['mp3file']['error'];
				$error_message = file_upload_error_message($error);

				if($error == 1){
					// This file is too big to upload 
					$size = ini_get('upload_max_filesize');
					echo "This site has a limit on the size of the mp3 file that you can upload. Currently that limit is set to $size <br> Your file was too large. Please press the back button to upload a different file.  ";
				}else{
					echo "There was error uploading your file: the following error occured<br><br> $error_message";
				}


				exit();
			}

			
			$file_name = $_FILES['mp3file']['name'];
			$tmp_location = $_FILES['mp3file']['tmp_name'];


			$browser_file_type = $_FILES['mp3file']['type'];

			if($browser_file_type != 'audio/mpeg'){
				//what is the browser is confused, lets
				//do another check..
				$mime = finfo_open(FILEINFO_MIME, "/usr/share/misc/magic");
				if ($mime ===FALSE) {
    					throw new Exception ('Finfo could not be run');
				}
				$filetype = finfo_file($mime, $tmp_location);
				finfo_close($mime);
				if($filetype != 'audio/mpeg'){
					$this->data['message'] = "Error: you can only upload mp3 files to this system";
					//lets delete this file now...
					unlink($tmp_location);
					return;
				}
			}


			
			//TODO check for mp3 here and error if it is not
			//can we truly check for mp3 content?

	
			if(isset($_POST['name'])){
				if(strlen($_POST['name'])>1){
					$name = $_POST['name'];
				}else{
					$name = $file_name;
				}
			}else{
				$name = $file_name;
			}
	
	
			$user = new User($_SESSION['user_id']);
			
			$recording = new Recording();
			$recording->user_id = $user->user_id;
			$recording->source = 'upload';
			$recording->name = $name;
			$recording->deleted = 0;

			$recording->save();//will generate an id;
			$id = $recording->recording_id;
		
			$new_file_name = calculate_recording_path($user->user_id,$id);

			if(!move_uploaded_file($tmp_location, $new_file_name)){ 
			    echo "There was an error uploading the file, please try again!";
			    echo "tried moving : $tmp_location to $new_file_name";
				exit();
			}

			$recording->initialEncryptFileUser();

			$this->data['message'] = "Recording $name uploaded, and saved";

		}

	}

/**
 * an action for a user to download a zip archive of all of thier recordings
 */
	function downzip(){

		$user_key = $_SESSION['user_key'];
		$User = new User($_SESSION['user_id']);

		$zip_name = $User->makeZip($user_key,$User->user_id);	
			
		header("Pragma: public"); 
      		header("Expires: 0"); 
      		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
      		header("Cache-Control: private",false); 

		header("Content-type: application/zip;\n");
		header("Content-Transfer-Encoding: Binary");
		header("Content-length: ".filesize($zip_name).";\n");
		header("Content-disposition: attachment; filename=\"backup.zip\"");
		readfile($zip_name);

		unlink($zip_name);



	}

/**
 * Given a recording id, check to ensure that this user owns it
 */
	function _owner_check($recording_id){
		

		$User = new User($_SESSION['user_id']);
		$recording = new Recording($recording_id);
		if($User->user_id == $recording->user_id){
			//everything is fine
			return;
		}else{
			echo "You do not own this recordings, and cannot perform that action...";
			exit();
		}

	}

/**
 * Sets a recording as unlocked, you can shared unlocked recordings.
 */
	function unlockrecording($recording_id = 0){
		
		if(isset($_GET['recording_id'])){
			$recording_id = $_GET['recording_id'];
		}

		if($recording_id == 0){
			echo "ERROR: recordings.controller.php lock I need a recording_id to lock";
			exit();
		}
		//only owners can do this
		$this->_owner_check($recording_id);	

		$Recording = new Recording($recording_id);
		
		$Recording->locked = 0;
		$Recording->save();

		$index_url = "/index.php/recordings/index/";

		bounce($index_url);

		exit();
	}



/**
 * Sets a recording as locked, locked recordings cannot be shared.
 */
	function lockrecording($recording_id = 0){
		
		if(isset($_GET['recording_id'])){
			$recording_id = $_GET['recording_id'];
		}

		if($recording_id == 0){
			echo "ERROR: recordings.controller.php lock I need a recording_id to lock";
			exit();
		}
		//only owners can do this
		$this->_owner_check($recording_id);	

		$Recording = new Recording($recording_id);
		
		$Recording->locked = 1;
		$Recording->save();

		//but we now want to delete all of the existing shares on it.
		$Recording->stopAllSharing(); //this will delete all of the user relationships with this recording
		

		$index_url = "/index.php/recordings/index/";

		bounce($index_url);
		exit();
	}


/**
 * Downloads a file, depending on headers, it plays in the browser or does save as...
 */
	function download(){
		//download a particular mp3

		//TODO make this aware of access control
		// how do I ensure that a particular user is involved....??
		//
		$recording_id = $_GET['recording_id'];

		if(isset($_GET['download'])){
			$attachment = 'attachement; ';

			//only owners can do this
			$this->_owner_check($recording_id);

		}else{
			$attachment = ' ';
		}	



		$user_key = $_SESSION['user_key'];

		$User = new User($_SESSION['user_id']);

		$recording = new Recording($recording_id);
		$file_name = $recording->decryptFileUserKey($user_key,$User->user_id);
		$name = $recording->name;
		
		if(stristr($name,'.mp3') === FALSE){
			//mp3 not found in string
			$name = $name . ".mp3";

		}

		if(file_exists($file_name))
		{
			header("Pragma: public"); 
	      		header("Expires: 0"); 
      			header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
      			header("Cache-Control: private",false); 
			header('Content-type: audio/mpeg');
    			header('Content-length: ' . filesize($file_name));
    			header("Content-Disposition: $attachment filename=\"$name\"");
    			header('Content-Transfer-Encoding: binary');
    			header('X-Pad: avoid browser bug');
    			Header('Cache-Control: no-cache');
    			ob_clean();
    			ob_flush();    
    			readfile($file_name);
		}

		//now we erase the unencrypted file...
		unlink($file_name);

		exit();

	}


/**
 * Deletes a recording, ....
 * @todo implement... carefully.
 */
	function untrash(){
		$index_url = "/index.php/recordings/index/";

		$User = new User($_SESSION['user_id']);
		if(isset($_GET['recording_id'])){//then this is first call

			$recording_id = $_GET['recording_id'];
			$recording = new Recording($recording_id);
			$this->data['name'] = $recording->name;
			$this->data['recording_id'] = $recording_id;
			
			if($User->user_id == $recording->user_id){// which means this user is the owner
				//then we continue processing all the way to the true delete..
			}else{
			//should we record this as an attack??
				echo "only the owner can delete a recording";
				exit();
			}
			
			$this->data['trashed'] = false;
			$recording->untrashRecording();	
			bounce($index_url);

			exit();
			
		}

		echo "How did you get here? There should have been a recording id passed to this function..";

		exit();

	}

/**
 * Moves a recording to the trash, not permenant.
 * @todo implement... carefully.
 */
	function trash(){
		$index_url = "/index.php/recordings/index/";

		$User = new User($_SESSION['user_id']);
		if(isset($_GET['recording_id'])){//then this is first call

			$recording_id = $_GET['recording_id'];
			$recording = new Recording($recording_id);
			$this->data['name'] = $recording->name;
			$this->data['recording_id'] = $recording_id;
			
			if($User->user_id == $recording->user_id){// which means this user is the owner
				//then we continue processing all the way to the true delete..
			}else{
			//should we record this as an attack??
				echo "only the owner can delete a recording";
				exit();
			}
			
			$this->data['trashed'] = true;
			$recording->trashRecording();	
			bounce($index_url);

			exit();
			
		}

		echo "How did you get here? There should have been a recording id passed to this function..";

		exit();

	}


/**
 * Deletes a recording, permanently....
 * @todo implement... carefully.
 */
	function remove(){
		$this->data['recordings_url'] = "/index.php/recordings/index/";
		$this->data['trash_url'] = "/index.php/recordings/index/?trash=true";


		$this->data['use_trash'] = $GLOBALS['use_trash'];

		$this->data['deleted'] = false;
		$User = new User($_SESSION['user_id']);
		if(isset($_GET['recording_id'])){//then this is first call
			$recording_id = $_GET['recording_id'];
			$recording = new Recording($recording_id);
			$this->data['name'] = $recording->name;
			$this->data['recording_id'] = $recording_id;
			//only the owner can remove...
		
			if($User->user_id == $recording->user_id){// which means this user is the owner
				//then we drop through to the GUI
			}else{
			//should we record this as an attack??
				echo "only the owner can delete a recording";
				exit();
			}
			
		}

		if(isset($_POST['delete'])){//then this is second call...
			$recording_id = $_POST['recording_id'];
			$recording = new Recording($recording_id);
			$this->data['name'] = $recording->name;
			$this->data['recording_id'] = $recording_id;
			
			if($User->user_id == $recording->user_id){// which means this user is the owner
				//then we continue processing all the way to the true delete..
			}else{
			//should we record this as an attack??
				echo "only the owner can delete a recording";
				exit();
			}
			
			$this->data['deleted'] = true;
			$recording->deleteRecording();	

		}

	}

/**
 * Changes the name of a recording.
 */
	function changename(){
		//share only a particular recording with a person
		$recording_id = $_POST['recording_id'];
		$new_name = $_POST['new_name'];

		$recording = new Recording($recording_id);

		$User = new User($_SESSION['user_id']);
		//only the owner can rename...
		
		if($User->user_id == $recording->user_id){// which means this user is the owner
			//then we can change the name
			$recording->name = $new_name;
			$recording->save();
		}else{
			//should we record this as an attack??
			
		}

		$index_url = "/index.php/recordings/index/";

		bounce($index_url);

/*
		echo "<html><head></head><body>
		Changed the recordings name to \"$new_name\" <br>
		Returning you to <a href='$index_url'>recordings list</a>
		</body></html>";
*/
		
		exit();

	}

/**
 * Emails a recording to someone
 * @todo implement... carefully.
 */
	function email(){
		// email this recording somewhere

	}

	


}//end controller class


?>
