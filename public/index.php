<?php

	session_start();

	require_once('../config.php');
	require_once('../util/functions.php');
	require_once('../util/PhoneFormat.php');

	// Cookie work
	//if the cookie for the user_key has been set... transfer it to the session and 
	//postpone its expiration for another ten years...

	//first lets ensure that we are asscesing the proper domain with no subdomains
	//google and other directed identity Openid providers will give different
	//keys on a per-sub-domain basis. So we cannot have "www.recordingdomain.com" becasue that would have different 
	//keys from just the recoringdomain.com

	$domain = $_SERVER['SERVER_NAME']; 
  
	$domain_array = explode('.',$domain); 
	$should_be_domain_array = explode('.',$GLOBALS['base_url']);
	if(count($domain_array) > count($should_be_domain_array)) //then we have www or something...
	{
		header('Location: '.$GLOBALS['base_url']); 
		exit();
	}


	if (strpos('https://',$GLOBALS['base_url']) === false ) {
		//then we are not running https for some reason, strange..

	}else{
		//then we should be certain that we have the https protection
		$domain = strtolower('https://' . $domain . '/'); //lets just make sure everything is good...
		if(strcmp($domain,$GLOBALS['base_url']) == 0){
			//then we are good
		}else{
			header('Location: '.$GLOBALS['base_url']); 
			exit();
		}
	}
	// URI work
	$uri_no_gets = explode('?',$_SERVER['REQUEST_URI']);	
	$parts_array = explode('/',$uri_no_gets[0]);
	//instatiate to avoid warnings...
	$uri_array = array();
	$controller_name = '';
	$action_name = '';
	$medium_name = '';
	foreach($parts_array as $value){
		if(strlen($value)>1){
			$uri_array[] = $value;
		}else{
			//nothing erasing all of the '' values
		}
	}
	
	if(count($uri_array) > 0){
	if(strcmp($uri_array[0],'index.php')==0){

		//then we are not using a re-writing rule...
		
		array_shift($uri_array); //gets rid of the index.php	

	}else{
		if(strlen($uri_array[1])==0){
			//default link
			//this should be login dependant...
		}else{
			echo "write code to handle mod_rewrite";
		}
	}//if str cmp
	}//if count uri_array
	
	// so there are three potential options...
	// medium / controller / function
	// and we will accept the following combinations
	// controller - assume the xhtml medium and index function
	// controller / function - use browser detection to determine iphone or xhtml 
	// medium / controller / function - assume nothing

	if(count($uri_array) >= 3){
		$medium_name = $uri_array[0];	
		$controller_name = $uri_array[1];	
		$action_name = $uri_array[2];	

	}else{
		//we need to detect the medium
		if(detect_iphone()){
			$medium_name = 'iphone';
		}else{
			if(detect_ipad()){
				$medium_name = 'ipad';
			}else{		
				$medium_name = 'xhtml';
			}
		}

		if(count($uri_array) == 1){
			$controller_name = strtolower($uri_array[0]);
			$action_name = 'index';
		}

		if(count($uri_array) == 2){
			$controller_name = strtolower($uri_array[0]);
			$action_name = strtolower($uri_array[1]);
		}

	}

	if(strlen($action_name)==0){	
			$action_name = 'index';
	}

	if(strlen($controller_name)==0){	
			$controller_name = 'login';
	}

	if(!isset($_SESSION['valid_login'])){
		@$_SESSION['valid_login'] == false;
	}

	$outside = false;

	if(@!$_SESSION['valid_login']){
		//$controller_name = 'login';
		//too limiting...
		$outside = true;	
	}


	$GLOBALS['controller_name'] = $controller_name;
	$GLOBALS['action_name'] = $action_name;
	$GLOBALS['medium_name'] = $medium_name;


	$controller = $GLOBALS['base_dir']."controller/$controller_name.controller.php";
	$controller_full_name = "Controller_" .$controller_name;
	$login_controller = $GLOBALS['base_dir']."controller/login.controller.php";
	$login_controller_full_name = "Controller_login";
	if(file_exists($controller)){
		require_once($controller);
		//we name the Controller classes this way so that they sort well in the documentation...
		$ControllerObject = new $controller_full_name;
		if($outside){
			//then we are outside
			if(isset($ControllerObject->outside) && !$ControllerObject->outside){
				//then this controller is not accessible outside...
				//we need to forward too the login controller...
				require_once($login_controller);
				$ControllerObject = new $login_controller_full_name;				
			}
		}
		$ControllerObject->$action_name();
		$action_name = $GLOBALS['action_name']; //just in case the controller changed it...
	}else{
		echo "ERROR: no such controller as $controller_name looked in $controller";
		exit();
	}

	$controller_data = $ControllerObject->data;
	$controller_data['controller'] = $controller_name;
	$controller_data['action'] = $action_name;


        require('../util/intsmarty.class.php');
        $smarty = new IntSmarty();
        $smarty->lang_path = $GLOBALS['base_dir']."translations/";
	$view_dir = $GLOBALS['base_dir']."view";
        $smarty->template_dir = $view_dir;
        $smarty->compile_dir = $GLOBALS['base_dir'].'/tmp/templates_c';
        $smarty->cache_dir = $GLOBALS['base_dir'].'/tmp/cache';
        $smarty->config_dir = $GLOBALS['base_dir'].'/tmp/configs';
//	$smarty->debugging = true;


	$smarty->register_modifier('phone',array('PhoneFormat','forPrinting'));

        foreach($GLOBALS['config'] as $key => $data){
                $smarty->assign($key,$data);
        }

        foreach($_SESSION as $key => $data){
                $smarty->assign($key,$data);
        }


        foreach($controller_data as $key => $data){
                $smarty->assign($key,$data);
        }

	$dynamic_header = $GLOBALS['head']->getHeader();
	$smarty->assign('dynamic_header',$dynamic_header);

	//$medium_name = 'iphone'; //so that I can debug in firefox
	$full_tpl_name = "$medium_name"."_$controller_name"."_$action_name.tpl";
	$short_tpl_name = "$controller_name"."_$action_name.tpl";


	if(file_exists($view_dir . "/$full_tpl_name")){
        	$view_contents = $smarty->fetch($full_tpl_name);
	}else{
        	$view_contents = $smarty->fetch($short_tpl_name);
	}
        $smarty->assign('view_contents',$view_contents);

	$smarty->display("$medium_name.tpl");
        //should not be called all the time for performance reasons...
        //but for now its fine.
        if($smarty->saveLanguageTable()){
                //echo "opening succeded";
        }else{
                echo "opening failed";
        }



?>
