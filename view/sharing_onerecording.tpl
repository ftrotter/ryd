

{if $sharing_works}

	{if $message}
		<h3>{$message}</h3>

		{if $to_user_id != 0}
			You can <a href='/index.php/sharing/manage/?user_id={$to_user_id}'>
			manage sharing with this user.</a><br>
			Or you can return to the <a href='/index.php/recordings/index/'>recordings list</a>.
		{/if}

	{else}

<form method='POST' > 
Enter the email address of the person who you want to share your recordings with:<br>
<input type='text' name='email'>
<br>
<input type='submit' value='Share'>

</form>
	{/if}
{else}
{*Then sharing does not work... show nothing*}

{/if}
