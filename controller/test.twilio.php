<?php
/**
 * contains a test twilio controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Twilio.class.php');
/**
 * This is the template Controller
 * @package YDA
 */
class Twilio_test extends Twilio{

/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){

	}
/**
 * index. adds some things to the data variable, just an example
 * @todo make it do something new
 */
	function index(){
		$post_content = var_export($_POST,true);
		$this->xml .= $post_content;
	}


}//end controller class


?>
