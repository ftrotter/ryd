

{if $error}

	{if $already_registered}
		<h1> This email is already registered </h1>
		<a href='/index.php/xhtml/emailopenid/signup'>Go back</a>
	{/if}

	{if $already_registered_provider}
		<h1> This email is already registered using {$already_registered_provider_id}</h1>
		<a href='/index.php/hybridauth/index/?provider={$already_registered_provider_id}'>login using {$already_registered_provider_id}</a>
	{/if}

		
	{if $passwords_do_not_match}
		<h1>  Your passwords do not match </h1>
		<a href='/index.php/xhtml/emailopenid/signup'>Go back</a>
	{/if}
	
	{if $emails_do_not_match}
		<h1>  Your emails do not match </h1>
		<a href='/index.php/xhtml/emailopenid/signup'>Go back</a>
	{/if}
	
	{if $need_name}
		<h1>  You must provide a first and last name </h1>
		<a href='/index.php/xhtml/emailopenid/signup'>Go back</a>
	{/if}

{else}


	{if $done}


	<h1> Great! thanks for registering this email address. </h1>
	<p>
	You can now login to this application using {$email}. 
Now you can get started with our 
	<a href='/index.php/sharing/index/'>sharing features.</a> 
Other users will now be able to share recordings with you and you can share recordings with other users!
	</p>
	

	{else}

	<h1> Associate your email address with this account. </h1>
	<form method='POST'>

		
Your first Name: <input name="first_name" value="" type="text"> 	<br>
Your last Name: <input name="last_name" value="" type="text"> 	<br>
Email: <input name="email" value="" type="text">	<br>
Retype Email: <input name="email_again" value="" type="text">	<br>
Password: <input name="password_one" value="" type="password">	<br>
Retype Password: <input name="password_two" value="" type="password">	<br>
<input value="Continue" type="submit">

	</form>

	{/if}

{/if}
