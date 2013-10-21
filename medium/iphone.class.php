<?php
/**
 * The iphone Medium file.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package RYD
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public L
icense v3 or later
 */

require_once('Medium.class.php');


/**
 * The iphone medium handles the basic menu building for the iphone. 
 * @package RYD
 * @todo implement
 */
class Medium_iphone extends Medium{
	
/**
 * Typical constructor. set the data from the controller to a local object variable.
 */
	function __construct($data){
		$this->data = $data;
	}

/**
 * The __toString method finds the view for this controller, calls it, 
 * packages the results in an iphone reasonable menu, and returns it. 
 * That means you can just echo the Medium object and everything works...
 * @todo implement
 */
	function __toString(){
		return "just for the iphone";
	}

}
?>
