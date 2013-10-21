<?php
/**
 * The xhtml Medium file.
 * @author Fred Trotter <fred.trotter@gmail.com>
 * @package RYD
 * @copyright Copyright (c) 2010 Fred Trotter and Patient Always First
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public L
icense v3 or later
 */

require_once('Medium.class.php');
require_once('../util/PhoneFormat.php');
/**
 * The xhtml medium handles the basic menu building for standard browsers. 
 * @package RYD
 * @todo implement
 */
class Medium_xhtml extends Medium{


/**
 * The __toString method finds the view for this controller, calls it, 
 * packages the results in xhtml for a standard browser reasonable menu, and returns it. 
 * That means you can just echo the Medium object and everything works...
 */	
	function __toString(){

		// the view file could add stuff to the header
		// so we must do this first...
		$view_object = $this->findView();		

		//defines get_view
		$view_contents = $view_object->get_view($this->data);	
		$header = $GLOBALS['head'];
		$header->addCSS("<link rel='stylesheet' type='text/css' media='screen' title='main' href='/css/main.css' />");
		$header->addCSS("<link rel='stylesheet' type='text/css' media='screen' title='main' href='/css/button.css' />");
		$header->addCSS("<link rel='stylesheet' type='text/css' media='screen' title='nifty' href='/css/nifty_main.css' />");

		$return = $header->getHeader();	

		$return .= $this->above($this->data['main_menu'],$this->data['sub_menu']);	
		
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
<!--From: xhtml.class.php start end of the page and content divs -->
    </div>
</div>
<!--From: xhtml.class.php stop -->

<script type=\"text/javascript\">
  var uservoiceOptions = {
    key: 'yourdoctorsadvice',
    host: 'yourdoctorsadvice.uservoice.com', 
    forum: '47087',
    alignment: 'right',
    background_color:'#FF0000', 
    text_color: 'white',
    hover_color: '#0066CC',
    lang: 'en',
    showTab: true
  };
  function _loadUserVoice() {
    var s = document.createElement('script');
    s.src = (\"https:\" == document.location.protocol ? \"https://\" : \"http://\") + \"uservoice.com/javascripts/widgets/tab.js\";
    document.getElementsByTagName('head')[0].appendChild(s);
  }
  _loadSuper = window.onload;
  window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>

";


	}

/**
 * Opens divs for the main xhtml page, creates the menus if they exist.
 * If there is not submenu then use a different body class. 
 * Almost everything that is not part of the main content comes from here.
 */
	function above(){

	$main_menu = $this->data['main_menu'];
	$sub_menu = $this->data['sub_menu'];


	$logo = $GLOBALS['logo_url'];
	$name = $GLOBALS['app_name'];

	$return_me = "<!--From: xhtml.class.php start -->
<div id='page'>
 <div id='header'>
<img src='$logo' class='align-left' alt='$name logo'>
<br>
";

//TODO Convert this into a system messaging engine...
//What is the right place to do this logic. In the controller?
//Perhaps in the parent controller?
//Not sure, but here it does not transfer to the iphone...
//not good.

	$return_me .= $this->data['header_message'];


/*
	$return_me .= "	
<span class='float-left'>
<div class='xsnazzy'>
<b class='xtop'><b class='xb1'></b><b class='xb2 color_a'>
</b><b class='xb3 color_a'></b><b class='xb4 color_a'></b></b>
<div class='xboxcontent'>
<h1 class='color_a'>Phone Numbers</h1>
<p>
<h4> To record call $record_number</h4>
<h4> To listen call $play_number</h4>
<br>
</p>
</div>
<b class='xbottom'><b class='xb4'></b><b class='xb3'></b>
<b class='xb2'></b><b class='xb1'></b></b>
</div>
</span>
";
*/

$return_me .= "
<div style='clear: left'> </div>
";

	$menu_html = "<ul id='navlist'>\n";

	foreach($main_menu as $item => $menu_array){
		$url = $menu_array['url'];
		$active = $menu_array['active'];

		if($active){
			$liid = " id='active' ";
			$aid = " id='current' ";
			$span = "<span class='hidden'>active</span>\n";
		}else{
			$liid = "";
			$span = "";
			$aid = "";
		}

		$menu_html .=  "\t\t<li$liid> $span <a $aid href='$url'>$item</a></li>\n";
	}

	$menu_html .= "</ul>\n";
	$return_me .= $menu_html;

/*
	<li><a href=''>Recordings</a> </li>
	<li id='active'>
		<span class='hidden'>active</span> 
		<a href='' id='current'>
			Sharing</a> 
	</li>
	<li><a href=''>Account</a> </li>
</ul>
*/
$return_me .= "
</div> ";

if($sub_menu){

	if(isset($data['sub_menu_title'])){
		$title = $data['sub_menu_title'];
	}

$return_me .= "
 <div id='menu'>";

if(isset($title)){
 $return_me .= "<h4>$title</h4>";
}

	$sub_menu_html = "<ul id='vertnavlist'>\n";

	foreach($sub_menu as $item => $menu_array){

		$url = $menu_array['url'];
		$active = $menu_array['active'];
		if($active){
			$liid = " id='active' ";
			$aid = " id='vertcurrent' ";
			$span = "<span class='hidden'>active</span>";
		}else{
			$liid = "";
			$aid = "";
			$span = "";
		}

		$sub_menu_html .=  "\t\t<li$liid> $span <a $aid href='$url'>$item</a></li>\n";
	}

	$sub_menu_html .= "</ul>\n";
	$return_me .= $sub_menu_html;



/*
 <ul id='vertnavlist'>
	<li><a href=''>Mine</a> </li>
	<li>
	<li id='active'>
		<span class='hidden'>active</span> 
		<a id='vertcurrent' href=''>Fred Trotter's</a> </li>
	<li><a href=''>Laura Trotter's</a> </li>
	<li><a href=''>Turo Arhio's</a> </li>
	<li><a href=''>Hari Arhio's</a> </li>
	<li><a href=''>A Person_with_A  VERY</a> </li>
</ul>
*/

$return_me .= "
</div>
<div id='sub_menu_content'>
";

}else{

$return_me .= "
 <div id='full_content'> ";
}

//sign the bottom...
$return_me .= "
<!--From: xhtml.class.php stop -->
";
	return($return_me);
	}


}

?>
