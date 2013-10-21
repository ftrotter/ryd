<?php
/**
 * contains a admin controller
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once("../config.php");
require_once('Controller.class.php');
require_once("../model/User.class.php");
require_once("../model/Email.class.php");
/**
 * This is the template Controller
 * @package YDA
 */
class Controller_admin extends Controller{
	


/**
 * Constructor. pulls in the global header, app name and controls the title
 * @todo make it do something new
 */
	function __construct(){
		parent::__construct();// do security work and general display..
		$header = $GLOBALS['head'];
		$app_name = $GLOBALS['app_name'];
		$header->addTitle("$app_name - CHANGE");

		$this->_check_admin();	//only for admin users...

	}
/**
 * index. adds some things to the data variable, just an example
 * @todo make it do something new
 */
	function index(){
	}


	function thecount(){

		$last_seven_days_sql = "SELECT count(id) as logins_last_seven_days FROM `users` WHERE last_login > (now() - interval 1 week)";
		$result = mysql_query($last_seven_days_sql) or die("Could not load last_seven_days_sql with $last_seven_days_sql <br> ".mysql_error());
		$row = mysql_fetch_array($result);
		$last_seven_days = $row['logins_last_seven_days'];
		echo "<h1> $last_seven_days users have logged in, over the last seven days </h1>";


		$total_paying_users_sql = "SELECT count(id) as total_paying_users FROM `users` WHERE `pay_status` = 'paypal'";
		$result = mysql_query($total_paying_users_sql) or die("Could not load total_paying_users_sql with $total_paying_users_sql <br> ".mysql_error());
		$row = mysql_fetch_array($result);
		$total_paying_users = $row['total_paying_users'];
		echo "<h1> $total_paying_users total users have paid to use this system </h1>";


		exit();
	}

	function report(){

		exit();

$sql = "SELECT `email` , `users`.`name` , COUNT( recording.id ) AS recording_count
FROM `users`
JOIN recording ON recording.user_id = users.id
GROUP BY users.id
ORDER BY recording_count DESC";

	$result = mysql_query($sql) or die("could not load user report with $sql".mysql_error());

	$user_count = 0;
	$recordings_count = 0;
	while($user_row = mysql_fetch_array($result)){
		echo "<br>";
		var_export($user_row);
		$user_count ++;
		$recordings_count = $user_row['recording_count'] + $recordings_count;
	}

	echo "<br> User Count = $user_count Recordings Count =  $recordings_count";

	exit();

	}


	function _check_admin(){

		$user_email = $_SESSION['email'];
		if(in_array($user_email,$GLOBALS['admin_users'])){
			return;
		}else{
		 	echo "You are not an admin user";
			exit();
		}

	}


	function email(){

		if(isset($_POST['email'])){

			$body = $_POST['email'];

$user_sql = "SELECT * FROM `users` WHERE 1";
		
	
			$result = mysql_query($user_sql) or die("Could not get users with $user_sql".mysql_error());
			
			while($user_row = mysql_fetch_array($result)){
				$email = $user_row['email'];

			}
			$email = "fred.trotter@gmail.com";
			$app_name = $GLOBALS['app_name'];			

	                $mail = new Email();
	                $mail->FromName = "$app_name Notice";
       	         	$mail->AddAddress($email);                  // name is optional
                	$mail->Subject = "$app_name Notice";
                	$mail->Body    = $body;
                	$mail->AltBody = $body;
                	$mail->send();

			echo "Send email to $email <br>";
			exit();

		}
	}

}//end controller class


?>
