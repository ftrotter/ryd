
		 
{if $new_user}
<h3>This is your first login.</h3> To use this system you will need to create an encryption key. All of your recordings will be encrypted using this key. Without the key it will not be possible for you, or anyone else to access your data. This helps us to guarantee that no one will every gain access to your data without your permission. We will email you a copy of this key, keep this email safe because if you forget this key (depending your settings) it may not be possible to recover your recordings. Remember this key is case sensitive (blue is different than Blue or BLUE)  ";
{else}
<h3>Welcome back.</h3> You are using this system for the first time from this computer. As a result you will need to enter your encryption key again. Remember you have to get the encryption key correct to gain access to your recordings. Remember this key is case sensitive (blue is different than Blue or BLUE). If you have forgotten your key, you can check your email, when you opened your account the website sent you an email with your key in it.
{/if}

<br>
<form action='$form_action' method='post' id='user_key_form'>

	Encryption Key: <input type='text' id='user_key' name='user_key'>
	<br> <input type='submit' name='submit' value='Submit User Key'>
</form>
