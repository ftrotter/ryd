<?php


class HybridTest extends PHPUnit_Framework_TestCase{	
	

	protected $hybridObject;


	protected function tearDown(){
		//$this->hybridObject->delete();
	}

	
	protected function setUp(){

		
		require_once("../model/HybridUserInstance.class.php");

		$this->hybridObject = new HybridUserInstance();

		$test_data = array(
   'hybrid_id' => 1,
   'provider_id' => 'Facebook',
   'user_id' => 1,
   'enc_priv_key' => '123456789',
   'identifier' => '672163382',
   'webSiteURL' => 'http://www.fredtrotter.com',
   'profileURL' => 'http://www.facebook.com/fredericktrotter',
   'photoURL' => 'https://graph.facebook.com/672163382/picture?type=square',
   'displayName' => 'Fred Trotter',
   'description' => 'I do not use facebook to communicate. Please do not message me and assume that you will get an answer. I read facebook messages about once every two months. If you need to contact me, please use fred.trotter@gmail.com',
   'firstName' => 'Fred',
   'lastName' => 'Trotter',
   'gender' => 'male',
   'language' => NULL,
   'age' => NULL,
   'birthDay' => '21',
   'birthMonth' => '12',
   'birthYear' => '1975',
   'email' => 'fred.trotter@gmail.com',
   'phone' => NULL,
   'address' => NULL,
   'country' => NULL,
   'region' => NULL,
   'city' => NULL,
   'zip' => NULL,
);

	
		foreach($test_data as $type => $data){
			$this->hybridObject->$type = $data;
		}

	}


	function testSave(){
		$this->hybridObject->save();
	}

	function testFindUserID(){
		//performs a static lookup using the email address..
		

		$email = $this->hybridObject->email;
		$user_id = $this->hybridObject->user_id;

		$found_user_id = HybridUserInstance::user_id_from_email($email);
		$this->assertEquals($user_id,$found_user_id);

	}

    /**
     * @expectedException MyDataNotSetException
     */
	function testBlankID(){
		$temp = $this->hybridObject->identifier;
		$this->hybridObject->identifier = '';
		$this->hybridObject->save();
		$this->hybridObject->identifier = $temp;
	}


    /**
     * @expectedException MyDataNotSetException
     */
	function testBlankUser(){
		$temp = $this->hybridObject->user_id;
		$this->hybridObject->user_id = '';
		$this->hybridObject->save();
		$this->hybridObject->user_id = $temp;
	}



    /**
     * @expectedException MyDataNotSetException
     */
	function testBlankProvider(){
		$temp = $this->hybridObject->provider_id;
		$this->hybridObject->provider_id = '';
		$this->hybridObject->save();
		$this->hybridObject->provider_id = $temp;
	}



    /**
     * @expectedException MyDataNotSetException
     */
	function testBlankKey(){
		$temp = $this->hybridObject->enc_priv_key;
		$this->hybridObject->enc_priv_key = '';
		$this->hybridObject->save();
		$this->hybridObject->enc_priv_key = $temp;
	}



}

?>
