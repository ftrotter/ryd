<div class='container_16'>

	{if $good_code}
	
		<h3> Thanks for subscribing!</h3> 
		<a href='/index.php/recordings/index/'>You can listen to your recordings here</a>
	{else}

	{if $bad_code}
		<h3> That is not a valid subscription code.</h3> 
		 <a href='/index.php/account/subscribe/'>do you want to try again?</a>
	{else}


<h3> Sign up for <i>{$app_name}</i>: </h3>

<div class='grid_5'>
<h1>Sign up now!  </h1>
<p>
$19.95 per year per family to register up to six cell phones and to store up to 50 recordings at a time on this single account.<br>
</p>
	{*todo should I be managing paypal information in the view?*}
{*
<h3>{$print_url} is free to try, and then as cheap as possible...</h3>
<p>
We have two payment plans, <span class='green'>'Try it out'</span> is <u>free for six months</u>, and then only costs $9.99 a year. The only limitation on this plan is that you can only upload or record ten recordings. With the <span class='green'>'Mostly unlimited'</span> plan you can keep <u>hundreds of recordings</u> in the application. Under this plan, we will only limit you if it becomes obvious that you are abusing the system (i.e. uploading your music collection...) otherwise, the sky is the limit.  
</p>
<p>
Both of these plans represent our efforts to offer you the service of allowing you to record whatever your doctor tells you, at utility pricing. We are basically charging you only what it costs us to offer the service. We also want to make it easy for you to decide if the service is right for you. This is why both plans come with a free trial. If you decide that {$print_url} is not right for you, you can cancel your subscription during the free trail without paying anything. 
</p>
<p> Some organizations might step forward to pay for your subscription. They will give you a subscription code that you can enter below </p>
<h1> There are three ways to pay:</h1>
<br>
*}

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="EYNT8UF3JDD4C">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

</div>

<div class='grid_5'>
<h1> Use a code! </h1>
<i>
Certain organizations will pay for their clients to use the service.  If your organization has paid for this plan, then you will be given a subscription code to use when you sign up, and the subscription is free for you.   You will have the ability to store up to 10 recordings at a time.
</i>


<form method='POST'>
	<i>	Subscription Code: </i> <input type='password' name='code' autocomplete="off">
	<br>
	<input type='submit' value='Submit Code'>	
</form>
</div>
<div class='grid_5'>
<h1> I want to use the free trail!  </h1>
<p> You will be able to use this system for six months for free </p>

<form method='POST'>
	<input type='hidden' name='code' value='trial'>
	<input type='submit' value='Signup for Free Trial'>
</form>
</div>


{/if} {*else bad code end*}
{/if} {*else good code end*}


</div>
