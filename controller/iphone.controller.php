<?php
/**
 * contains the iphone controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Controller.class.php');
/**
 * This is the iphone Controller used for iphone specific interfaces, like menus
 * @package YDA
 */
class Controller_iphone extends Controller{

/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("iphone main menu");

	}
/**
 * index. adds some things to the data variable, just an example
 * @todo make it do something new
 */
	function index(){
		//display the openid selector...
		$header = $GLOBALS['head'];
		$this->data['form_action'] = "/index.php/change/change/";
		$this->data['hello'] = "hi mom";
	}


	function navigation(){

		//nothing done here... look in the template for navigation menu

	}


}//end controller class


?>
