<?php
/**
 * contains the wppage controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once("../util/functions.php");
require_once('Controller.class.php');
/**
 * This is the Wordpress Page Controller
 * @package YDA
 */
class Controller_wppage extends Controller{


        /**
        *       This controller is accesible outside as well as inside
        */
        var $outside = true;


/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - CHANGE");

	}
/**
 * __call allows this function to load data from the wordpress mysql pages table by using the name of the page
 * as the name of the function. so /index.php/wppages/freds-lastest-page loads the page freds-latest-page  
 */
	function __call($name,$arguements){
		//display the openid selector...
		//to get this to work you need to run: GRANT SELECT ON `blog` . * TO 'record'@'localhost';
		//on the mysql instance...
	
		if(count($arguements) > 0){
			echo "wppage.controller.php: error: there should be no arguments to this function";
			exit();
		}

		$name = mysql_real_escape_string($name);

	$sql = "
SELECT 
`post_content`,
`post_title`
FROM blog.wp_posts 
WHERE `post_status` = 'publish' 
AND `post_name` = '$name' 
AND `post_type` = 'page'
";
	
	
		$result = mysql_query($sql) or die("Failed to load page with $sql <br>".mysql_error());


		header('Content-Type: text/html; charset=iso-8859-1');
		//this is such a hack. I hate it, but I have to get 
		// on with my life. WTF why would Wordpress do such strange things
		//with its database data??

		
		$row = mysql_fetch_array($result);
		if(isset($row['post_content'])){
			$page_content = $row['post_content'];
			$page_content = wpautop($page_content);
	
			$page_title = $row['post_title'];
	//		$page_title = nl2br($page_title);
//			$page_content = htmlspecialchars($page_content);
		}else{
			$page_content = "<h3>Page content for $name not found </h3>";
		}
		$this->data['page_content'] = $page_content;
		$this->data['page_title'] = $page_title;
		$GLOBALS['action_name'] = 'call';
			

		

	}


	


}//end controller class


?>
