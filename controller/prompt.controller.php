<?php
/**
 * contains the prompt controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Controller.class.php');
/**
 * This is the prompt Controller. Used to prompt the user with various messages
 * @package YDA
 */
class Controller_prompt extends Controller{

/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - Prompt");

	}
/**
 * index. given the right arguments the action will nag with a message built elsewhere in the system. 
 * @todo make it do something new
 */
	function prompt(){
		//display the openid selector...
		$message = $_SESSION['prompt_message'];
		$prompt_name = $_SESSION['prompt_name'];
		$_SESSION['prompt_displayed'][$prompt_name] = true;
		$this->data['prompt'] = "$message";
	}


}//end controller class


?>
