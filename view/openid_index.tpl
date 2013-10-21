<div class='container_16'>
<div class='grid_16'>
<img src='/images/logo.gif'>
</div>
<div class='grid_16 prefix_2'>
<br><br>

{if $show_pin_form}

<h1> We are calling you now.</h1>
<br>
<form method='POST' action=''>                  
Please enter the four digit "verification PIN" we are calling you with: <br>
<br>
<input type='text' width='4' name='pin' autofocus="autofocus"><br>
<br>
<input type='submit' value='Continue'><br>
</form>


{assign var='shown_something' value=true}
{/if}

{if $show_phone_form}
{if $show_wrong_pin}
	<h1> That was the wrong pin... Try again.. </h1>
{/if}
<h1>Cell phone login:</h1>
<br>
<form method='POST' action=''>

Please enter your ten digit cell phone number:<br> 
We will be calling you on this number in the next step.<br> 
<br>
<input type='text' name='phone' autofocus="autofocus"><br>
<br>
<input type='submit' value='Continue'>
</form>



{assign var='shown_something' value=true}
{/if}


{if !$shown_something}

Not sure how you got here. 
You cannot usefully access this page directly.


{/if}
</div> {*end grid*}
</div> {*end container*}
{*debug*}
