<?php
/**
 * contains the comment model class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once('../config.php');
require_once('../util/enchelp.php');
require_once('../util/htmlpurifier/library/HTMLPurifier.auto.php');

/**
 *  The Comment class handles comments and comment encryption 
 * @package YDA
 */
class Comment {

	var $comment_id; 
	var $recording_id;
	var $user_id;
	var $enc_text;
	var $plain_text;


	var $is_new;
	
/**
 * This is contructor operates from a simple comment id call
 */
		function __construct($id){


			$id = mysql_real_escape_string($id);

						$comment_sql = "
SELECT *
FROM `recording_comment`
WHERE id = '$id'";




			$result = mysql_query($comment_sql) or die("ERROR: cannot check user table: sql = $comment_sql <br> error = ".mysql_error());
			
			$row = mysql_fetch_array($result);

			if($row){
				$this->is_new = false;
				$this->comment_id = $row['id'];
				$this->recording_id = $row['recording_id'];
				$this->user_id = $row['user_id'];
				$this->enc_text = $row['enc_text'];
				$this->plain_text = $row['plain_text'];
	
			}else{
				//row is false
				//therefore the comment does not yet exist
			
				$this->is_new = true;
				$this->comment_id = 0;

			}
		}    //end constructor..

/**
 * This intelligent save function does the work to save a given comment to the database.
 */
		function save(){

			$comment_id = $this->comment_id;

			if(!isset($this->recording_id)){
				echo "ERROR: trying to save a user without a recording id $comment_id";
				exit(1);
			}

			if(!isset($this->user_id)){
				echo "ERROR: trying to save a user without a user id $comment_id";
				exit(1);
			}

			if(!isset($this->enc_text)){
				//this needs to be changed!!
				//eventually we need to support full encryption...
				$this->enc_text = '';				
			}

			if(!isset($this->plain_text)){
				//this needs to be changed!!
				//eventually we need to support full encryption...
				echo "ERROR: trying to save a user without a plain text $comment_id";
				exit(1);
			
			}

			//perhaps the right place to do the work of encryption 
			//is right here in the save function?
			// probably not, should be just like the recordings...
			// of course the plain text needs to pruified first... 
			// and that needs to be universally enforced...
			// perhaps here is the right place... 
			// for now lets enforce the plain text goodness here...

			$purifier = new HTMLPurifier();
			$dirty_html = $this->plain_text;
   		 	$clean_html = $purifier->purify($dirty_html);
			$clean_and_prepped_html = mysql_real_escape_string($clean_html);
			$this->plain_text = $clean_and_prepped_html;
			
		
	
			$new_comment_sql = "REPLACE INTO `recording_comment` (
`id` ,
`recording_id` ,
`user_id` ,
`enc_text`,
`plain_text`
)
VALUES (
$comment_id , '$this->recording_id', '$this->user_id', 
'$this->enc_text', '$this->plain_text'
);";

			mysql_query($new_comment_sql) or die("Comment.class.php ERROR: cannot add to recording_comments table: sql = $new_comment_sql <br> error = ".mysql_error());

			if($this->is_new){
				//do something...
			}


			return(true);


		}
/**
 * This handles all of the wonderful decryption functions... could need to deprecate this in favor of a system more like the recordings system..
 */

	function getPlainText(){
		//update to do encryption
		return($this->plain_text);
	}


	function __toString(){

		$return_me = "Comment Id: ".$this->comment_id. "<br>";
		$return_me .= "Recording Id: ".$this->recording_id ."<br>";
		$return_me .= "User Id: ".$this->user_id ."<br>";
		$return_me .= "Plain Text : ".$this->plain_text ."<br>";
		$return_me .= "Enc Text : ".$this->enc_text ."<br>";
		
		return($return_me);
	}


}//end Comment class
?>
