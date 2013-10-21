<?php
/**
 * contains a OpenID controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2012 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Controller.class.php');
require_once("../model/YDAOpenIDProvider.php");
/**
 * This is the OpenID Controller
 * @package YDA
 */
class Controller_openid extends Controller{

/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..

	}
/**
 * index. adds some things to the data variable, just an example
 * @todo make it do something new
 */
	function index(){
		//display the openid selector...

		$op = new YDAOpenIDProvider;
		$op->server();		

	}


}//end controller class


?>
