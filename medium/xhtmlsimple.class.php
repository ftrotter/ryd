<?php
/**
 * The xhtmlsimple file.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package RYD
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public L
icense v3 or later
 */

require_once('Medium.class.php');

/**
 * The xhtmlsimple medium provides an menuless xhtml interface for login pages etc etc. 
 * @package RYD
 * @todo implement
 */
class Medium_xhtmlsimple extends Medium{


/**
 * Magic method that allow you to simply echo the Medium object... 
 */	
	function __toString(){

	
		//the view can add stuff to the header
		//so we need to call it first!!!
		$view_object = $this->findView();		

		//defines get_view
		$view_contents = $view_object->get_view($this->data);	
		


		$header = $GLOBALS['head'];
		$header->addCSS("<link rel='stylesheet' type='text/css' media='screen' title='main' href='/css/main.css' />");

		$return = $header->getHeader();	

		$return .= $this->above();	
	
		//the only thing allowed to be in parallel with the logo is the login box

		if(strcmp($this->data['controller'], 'login') == 0){
			//then do not clear right...
		}else{
			$return .= "<div style='clear: both'></div>";
		}
		
	
		$return .= $view_contents;

		$return .= $this->below();
		$return .= $header->getFooter();

		return($return);
	}

/**
 * returns html comments and closes the divs that mark the bottom of the xhtml section.
 */
	function below(){

		return "
<!--From: xhtmllogin.class.php start end of the page and content divs -->
    </div>
</div>
<!--From: xhtmllogin.class.php stop -->

";


	}


/**
 * Opens divs for the main xhtml page, no menus here...
 * Almost everything that is not part of the main content comes from here.
 */
	function above(){

        $logo = $GLOBALS['logo_url'];
        $name = $GLOBALS['app_name'];

	$return_me = "	
<!--From: xhtmllogin.class.php start -->

		<div id='page'>
 <div id='header'>
<img src='$logo' class='align-left' alt='$name logo'>
</div>
</span>

</div>
 <div id='content'>
<!--From: xhtmllogin.class.php stop -->
";

	return($return_me);
	}


}

?>
