<body>
{*paging logic here...*}
<div id="topbar" class="transparent">
	<div id="title">
		{$app_name}</div>
	</div>
<div id="content">
<span class="graytitle">Login to Get Started</span>
<form action='{$form_action}' method='get' id='openid_form'>
        <input type='hidden' name='action' value='verify' />
                <div id='openid_choice'>
                        <div id='openid_btns'></div>
                        </div>
                        
                        <div id='openid_input_area'>
                                <input id='openid_identifier' name='openid_identifier' type='text' value='http://' />
                                <input id='openid_submit' type='submit' value='Sign-In'/>
                        </div>
                        <noscript>
                        <p>OpenID is service that allows you to log-on to many different websites using a single indentity.
                        Find out <a href='http://openid.net/what/'>more about OpenID</a> and <a href='http://openid.net/get/'>how to get an OpenID enabled account</a>.</p>
                        </noscript>
</form>
<div id="footer">

</div>

</body>


{*
<div style='clear: both;'><br> </div>
<div style='margin: 0 auto; width: 80% '>
<h2> This site will let you do three things that you could not do before:</h2>
<ol class='purpose_list'>
        <li><p><span class='logo'>Record</span> your doctor with a cell phone</p></li>
        <li><p><span class='logo'>Play</span> recordings from this website </p> </li>
        <li><p><span class='logo'>Share</span> recordings safely with anyone </p> </li>

</ol>
<div id='bottomcontainer'>
<ul id='bottomlist'> 
        <li id='active'> <a id='current' href='{$how_it_works_url}'> How it works</a> </li>
        <li> <a href='{$good_idea_url}'> Why it's a good idea </a></li>
        <li> <a href='{$about_us_url}'> About Us</a></li>
</ul>
</div>
*}
