<?php
/**
 * The comments controller intended to handle recording comments
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Controller.class.php');
require_once('../model/User.class.php');
require_once('../model/Comment.class.php');


/**
 * This is the comments Controller
 * @package YDA
 */
class Controller_comments extends Controller{

/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - Comments");

	}
/**
 * save function, allows for comments to be saved...
 */
	function save(){
		//display the openid selector...
		$header = $GLOBALS['head'];

		// will be 0 for a new comment
		$comment_id = $_POST['comment_id'];
		
		$recording_id = $_POST['recording_id'];
	
		$user = new User($_SESSION['email']);
		$this_user_id = $user->user_id;
		$Comment = new Comment($comment_id);
		$Comment->recording_id = $recording_id;
		$Comment->user_id = $this_user_id;
		$Comment->plain_text = $_POST["editor_$recording_id"];

		$Comment->save();

	//	echo $Comment;
	//	exit();

		$index_url = "/index.php/recordings/index/";

		bounce($index_url);
	}


}//end controller class


?>
