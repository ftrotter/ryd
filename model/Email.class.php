<?php
/**
 * contains the email model class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */

require_once('../config.php');
require_once('../util/enchelp.php');
require_once("../util/phpmailer/class.phpmailer.php");


/**
 *  The Email class wraps the phpmailer functions, with configuration settings
 * providing a simpler view of emailing to the whole application.
 * @package YDA
 */
class Email {

	var $mail; 
	var $FromName = '';
	var $Subject = '';
	var $Body = '';
	var $AltBody = '';
	var $toCount = 0;
	
/**
 * constructor sets up PHPmailer object will all of the always true parameters
 * @return object email object
 */
	function __construct(){

		//NOTE: this is setup to use gmail as the sending vehicle. 
		//This file, and the config file needs to modified to support other sending mechanisms
		//But the community will need to put that together...


		$this->mail = new PHPMailer();

		$this->mail->IsSMTP();                                      // set mailer to use SMTP
		$this->mail->Host = $GLOBALS['smtp_host'];  // specify main and backup server
		$this->mail->SMTPAuth = true;     // turn on SMTP authentication
		$this->mail->Username = $GLOBALS['smtp_username'];  // SMTP username
		$this->mail->Password = $GLOBALS['smtp_password']; // SMTP password
		$this->mail->WordWrap = 50;                                 // set word wrap to 50 characters
		$this->mail->IsHTML(true);                                  // set email format to HTML
		$this->mail->From = $GLOBALS['smtp_username'];


	} //end constructor   

/**
 * wraps the send phpmail function, but checks to make sure there are ok values for everything...
 */
	function send(){

		if($this->toCount == 0){
			echo "ERROR: Email.class.php, send called without setting any to addresses";
			exit();
		}

		if(strlen($this->FromName) == 0){
			echo "ERROR: Email.class.php, send called without setting FromName";
			exit();
		}
		if(strlen($this->Subject) == 0){
			echo "ERROR: Email.class.php, send called without setting Subject";
			exit();
		}
		if(strlen($this->Body) == 0){
			echo "ERROR: Email.class.php, send called without setting Body";
			exit();
		}
		if(strlen($this->AltBody) == 0){
			echo "ERROR: Email.class.php, send called without setting AltBody";
			exit();
		}

		$this->mail->FromName = $this->FromName;
		$this->mail->Subject = $this->Subject;
		$this->mail->Body    = $this->Body;
		$mail->mail->AltBody = $this->AltBody;

		if(!$this->mail->Send()){
   			echo "ERROR: Email.class.php send: Message could not be sent. <p>";
   			echo "Mailer Error: " . $mail->ErrorInfo;
   			exit;
		}


	}

/**
 * wraps the AddAddress phpmailer function..
 */
	function addBCC($address){

		if(strlen($address) == 0){
			echo "ERROR: Email.class.php: addAddress() cannot add a blank string";
			exit();
		}

		if($this->validEmail($address)){
			$this->mail->AddBCC($address);
			//does not count towards the count...
			//we still need to have at least one AddAddress call...
		}else{
			echo "ERROR: Email.class.php: $address failed validation..";
			exit();
		}

	}


/**
 * wraps the AddAddress phpmailer function..
 */
	function addAddress($address){

		if(strlen($address) == 0){
			echo "ERROR: Email.class.php: addAddress() cannot add a blank string";
			exit();
		}

		if($this->validEmail($address)){
			$this->mail->AddAddress($address);
			$this->toCount++;
		}else{
			echo "ERROR: Email.class.php: $address failed validation..";
			exit();
		}

	}

//Thanks Douglas Lovell!!
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}


}//end Email class
?>
