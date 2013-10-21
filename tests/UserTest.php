<?php
require_once("../config.php");

class UserTest extends PHPUnit_Framework_TestCase{	
	

	protected $userObject;
	protected $userObject_2;
	protected $hybridObject;
	protected $hybridObject_2;
	protected $fake_user_key;
	protected $fake_phone;
	protected $fake_email;
	protected $fake_user_key_2;
	protected $fake_phone_2;
	protected $fake_email_2;


	protected function tearDown(){
		$this->userObject->delete();
		$this->userObject_2->delete();
		$this->hybridObject->delete();
		$this->hybridObject_2->delete();
	}

	
	protected function setUp(){

		
		require_once("../model/User.class.php");
		require_once("../model/HybridUserInstance.class.php");


		$this->fake_user_key = "http://fake.com/userid=1000";
		$this->fake_phone = "7134099506";
		$this->fake_email = "rick.tester.fred.trotter@gmail.com";

		$this->fake_user_key_2 = "http://fake.com/userid=5555";
		$this->fake_phone_2 = "2819083403";
		$this->fake_email_2 = "alice.tester.fred.trotter@gmail.com";


		$this->not_a_user_email = 'test@example.com';

	        $user = new User();
                $user->newPrivateKey($this->fake_user_key);
                $user->setOpenidHash($this->fake_user_key);
                $user->phone = $this->fake_phone;
                $user->eula_agree = 0;
                $user->splash_seen = 0;
                $user->motd = 0;

		$this->userObject = $user;
		$this->userObject->save();

	        $user = new User();
                $user->newPrivateKey($this->fake_user_key_2);
                $user->setOpenidHash($this->fake_phone_2);
                $user->phone = $this->fake_phone_2;
                $user->eula_agree = 0;
                $user->splash_seen = 0;
                $user->motd = 0;

                $this->userObject_2 = $user;
                $this->userObject_2->save();



		$hybrid = new HybridUserInstance();
		$hybrid->user_id = $this->userObject->user_id;
		$hybrid->user_key = $this->fake_user_key;		
		$hybrid->id_provider = "From Test Script";
		$hybrid->identifier = $this->fake_user_key;		
		$hybrid->email = $this->fake_email;		
		$hybrid->save();

		$this->hybridObject = $hybrid;


		$hybrid = new HybridUserInstance();
		$hybrid->user_id = $this->userObject_2->user_id;
		$hybrid->user_key = $this->fake_user_key_2;		
		$hybrid->id_provider = "From Test Script";
		$hybrid->identifier = $this->fake_user_key_2;		
		$hybrid->email = $this->fake_email_2;		
		$hybrid->save();

		$this->hybridObject_2 = $hybrid;
	
	}

        function testDeleteHybrid(){
                $hybrid_id = $this->hybridObject->hybrid_id;
                $this->hybridObject->delete();

                $search_sql = "SELECT COUNT( * ) AS the_count
FROM  `hybrid_user_instance` 
WHERE  `id` = $hybrid_id";

                $result = mysql_query($search_sql) or die("testDeleteHybrid: could not do search sql with $search_sql \n".mysql_error());
                $row = mysql_fetch_assoc($result);
                $this->assertEquals($row['the_count'],0);
                

        }


	function testDeleteUser(){
		$user_id = $this->userObject->user_id;
		$this->userObject->delete();
	
		$search_sql = "SELECT COUNT( * ) AS the_count
FROM  `users` 
WHERE  `id` = $user_id";

		$result = mysql_query($search_sql) or die("testDeleteUser: could not do search sql with $search_sql \n".mysql_error());
		$row = mysql_fetch_assoc($result);
		$this->assertEquals($row['the_count'],0);


	}


	function testInviteNotice(){

		$totally_new_email = "bob.tester.fred.trotter@gmail.com";
		$totally_new_fake_user_key = "http://example.com/11111";
		$totally_new_fake_phone = "1112223333";

		$this->userObject->markInvited($totally_new_email);

    		$user = new User();
                $user->newPrivateKey($totally_new_fake_user_key);
                $user->setOpenidHash($totally_new_fake_phone);
                $user->phone = $totally_new_fake_phone;
                $user->eula_agree = 0;
                $user->splash_seen = 0;
                $user->motd = 0;

		$user->save();

                $hybrid = new HybridUserInstance();
                $hybrid->user_id = $user->user_id;
                $hybrid->user_key = $totally_new_fake_user_key;
                $hybrid->id_provider = "Testing";
                $hybrid->identifier = $totally_new_fake_user_key;
                $hybrid->email = $totally_new_email;
                $hybrid->save();

		$this->assertTrue(true);

		$cleanup_sql = "DELETE FROM users WHERE phone = $totally_new_fake_phone";
                mysql_query($cleanup_sql) or die("testInviteNotice: Cleanup failed $cleanup_sql".mysql_error());

		$cleanup_sql = "DELETE FROM invites WHERE to_email = '$totally_new_email'";
                mysql_query($cleanup_sql) or die("testInviteNotice: Cleanup failed $cleanup_sql".mysql_error());

	}

	function testGetWhoInvitedMe(){

		$should_be_empty = $this->userObject_2->getWhoInvitedMe();
		$this->assertEmpty($should_be_empty);
		//get user_2s email...
		$user_2_email = $this->userObject_2->getEmail();
		$my_email = $this->userObject->getEmail();
		//have user 1 invite user 2
		$this->userObject->markInvited($user_2_email);
		//then ask user 2 to get who invited me
		$result_array = $this->userObject_2->getWhoInvitedMe();
		list($invited_id,$invited_email) = each($result_array);

		$this->assertEquals($this->userObject->user_id,$invited_id);
		$this->assertEquals($my_email,$invited_email);

		//make sure this test works next time...

		$cleanup_sql = "DELETE FROM `invites` WHERE 
`to_email` = '$user_2_email' AND
`from_email` = '$my_email'";		

		
		mysql_query($cleanup_sql) or die("testGetWhoInvitedMe: Cleanup failed $cleanup_sql".mysql_error());

	}


	function testInvitedRecently(){	

		$really_fake_email = "serious123@example.com";
		$was_invited = $this->userObject->invitedRecently($really_fake_email);
		$this->assertFalse($was_invited);
		//because there should be no record	
		$this->userObject->markInvited($really_fake_email);
		$was_invited = $this->userObject->invitedRecently($really_fake_email);
		$this->assertTrue($was_invited);
		
		//lets clean this up...
		$cleanup_sql = "DELETE FROM `yourdoctorsadvice`.`invites` WHERE `invites`.`to_email` = '$really_fake_email'";

		mysql_query($cleanup_sql) or die("testInvitedRecently: Cleanup failed $cleanup_sql".mysql_error());

	}

	function testGetEmail(){

		$email_result = $this->userObject->getEmail();
		$this->assertEquals($this->fake_email,$email_result);

		$email_result = $this->userObject_2->getEmail();
		$this->assertEquals($this->fake_email_2,$email_result);

	}


	function testEncryption(){

		$user = $this->userObject;

                $privkey = $user->getPrivateKey($this->fake_user_key);
                $public_key = $user->public_key;
                $enc_privkey = $user->enc_priv_key;

                //echo "Looks like you are $email <br>";
                //echo "Private Key based on $user_key <br> <pre>$privkey</pre>";
                //echo "How it looks in the db (encrypted) $enc_privkey<br>";
                //echo "Your public key <pre> $public_key </pre> ";

                $test_data = "--------FRED SURE IS A NICE GUY HE IS MARRIED TO LAURA AND HELUNA IS HIS PUPPY DOG heluna laura puppy +++++++++++++";

                //echo "lets practice public/private decryption, with an encrypted private key!!<br> We will use $test_data as a test<br>";

                //do not need a password to encrypted to a user...
                $encrypted = $user->publicEncrypt($test_data);

                //echo "encrypted message <pre>$encrypted </pre> <br>";

                //but we do need one to get the data out!!
                $cleartext = $user->privateDecrypt($encrypted,$this->fake_user_key);
		$this->assertEquals($test_data,$cleartext);

	}
}

?>
