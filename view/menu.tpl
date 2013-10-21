<div class='topmenuleft'>
<ul id='toplistleft' >
        <li id='active'> 
<a target='_blank' id='current' href='{$how_it_works_url}'>How it works</a> 
<!--[if IE 7]>
&nbsp; &nbsp; &nbsp;
<![endif]-->
	</li>
        <li> 
<a target='_blank' href='{$good_idea_url}'>Why it's a good idea</a>
<!--[if IE 7]>
&nbsp; &nbsp; &nbsp;
<![endif]-->

	</li>
        <li> 
<a target='_blank' href='/index.php/wppage/about-us/'>About us</a>
<!--[if IE 7]>
&nbsp; &nbsp; &nbsp;
<![endif]-->

	</li>
        <li> 
<a target='_blank' href='/index.php/wppage/privacy/'>Privacy</a>
<!--[if IE 7]>
&nbsp; &nbsp; &nbsp;
<![endif]-->

	</li>
</ul>
</div>

<div class='topmenuright'>
<ul id='toplistright'>
	<li><a href="/index.php/account/home">Home</a>
<!--[if IE 7]>
&nbsp; 
<![endif]-->
</li>
	<li><a href="/index.php/recordings/index">Recordings</a>
<!--[if IE 7]>
&nbsp; 
<![endif]-->
</li>
	<li><a href="/index.php/account/index/">Account</a>
<!--[if IE 7]>
&nbsp; 
<![endif]-->

{* Removed until Sharing using Hybrid Auth is enabled *}
</li>
{if $sharing_works}
	<li><a href="/index.php/sharing/index/">Sharing</a>
<!--[if IE 7]>
&nbsp; 
<![endif]-->

</li> {/if}
	<li><a href="/index.php/advanced/index/">Advanced</a>
<!--[if IE 7]>
&nbsp; 
<![endif]-->

</li>
	<li><a title='Logout from {$user_print} ' href="/index.php/hybridauth/logout/">Logout</a>
<!--[if IE 7]>
&nbsp; 
<![endif]-->

</li>
</ul>

</div>


