<?php
/**
 * contains the basic twilio controller class.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package YDA
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License v3 or later
 */
/**
 * the base twilio class. 
 * this class holds the xml that each twilio controller will need to make
 * and helps you print it with a lovely simple __toString 
 * 
 * @package YDA
 */
class Twilio {
 
    /**
     * This contains the xml to echo with the  toString, includes starting xml
     * @access public
     * @var xml
     */
	var $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";   
/**
 * construct. sets the header content type to xml
 */
	function __construct() {
		header("content-type: text/xml");
	}	
/**
 * __toString so that we can just print the controller.  
 */
	function __toString(){	
		return($this->xml);		
	}
}
?>
