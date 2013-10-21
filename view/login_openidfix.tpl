{if $done}
	{$done}
{else}
{if $message}
	{$message}
{/if}

<h1> Your login has been changed </h1>
<p>
Your login data has changed. (This may have happened because of a migration on our end).
In order to restore access to your recordings, you need to enter one of the phone numbers that you have been using with this site.
This will verify that you are the same user.
</p>
<form method='POST'>
Phone Number: <input type='text' name='phone'><br>
<input type='submit' name='submit' value='Submit'>

</form>
{/if}
