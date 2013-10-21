<?php
/**
 * The basic Medium class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package RYD
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public L
icense v3 or later
 */


/**
 * This contains the basic Medium functions required by all Medium classes.
 * @package RYD
 * @todo implement
 */
class Medium{
    /**
     * The data array contains everything that is passed from the controller
     * @access public
     * @var array
     */
	var $data;
    /**
     * the location of the view files.
     * @access public
     * @var string
     */
	var $view_dir = '../view/';


/**
 * basic contructor
 */	
	function __construct($data){
		$this->data = $data;
	}


/**
 * Method to all a medium to find the right view file to include.
 * This is where the fall through and default view calculations occur.
 */	
	function findView(){
		$cont = $GLOBALS['controller_name'];
		$medi = $GLOBALS['medium_name'];
		$acti = $GLOBALS['action_name'];

		//specifically defined for this medium/controller/action
		$first_try = "$medi"."_$cont"."_$acti";
		$first_try_file = "$this->view_dir$first_try.php";

		if(file_exists($first_try_file)){
			require_once($first_try_file);
			$view_object_name = "View_" . $first_try;
			$view_object = new $view_object_name();
			return($view_object);
		}



		$second_try = "$cont"."_$acti";
		$second_try_file = "$this->view_dir$second_try.php";

		if(file_exists($second_try_file)){
			require_once($second_try_file);
			$view_object_name = "View_" . $second_try;
			$view_object = new $view_object_name();
			return($view_object);
		}



		//if we get here, then there is no template!!

		echo "ERROR: no template is defined for this controller I tried<br> $first_try_file <br> $second_try_file <br> medi = $medi <br> cont = $cont <br> acti = $acti";
		exit();

	}
}

?>
