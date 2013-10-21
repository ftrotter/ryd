



	{if $stopped_one}

<h3> You have stopped sharing this recording </h3>

	{else}

	{if $stopped_all}

<h3> You have stopped all sharing with this user </h3>
	{else}


	{if $recording_id}
<h3> Stop sharing just recording '{$recording_name}' with {$stop_user_name}? </h3>
<form method='POST'>
Are you sure you want to stop sharing this one recording?<br>

<input type='hidden' name='sure' value='true'> 
<input type='hidden' name='stop_one' value='true'> 
<input type='hidden' name='stop_user_id' value='{$stop_user_id}'> 
<input type='hidden' name='recording_id' value='{$recording_id}'> 
<br><br>
<input type='submit' name='Stop Sharing This Recording' value='Stop Sharing This Recording'>
</form>
<br><br>

	{/if}

<h3> Stop sharing with {$stop_user_name} entirely?</h3>
<form method='POST'>
Are you sure you want to stop all sharing with this user?<br>
<input type='hidden' name='sure' value='true'> 
<input type='hidden' name='stop_all' value='true'> 
<input type='hidden' name='stop_user_id' value='{$stop_user_id}'> 
<br> <br>
<input type='submit' name='Stop Sharing With This User' value='Stop Sharing With This User'>
</form>
<br>
You can always <a href='/index.php/sharing/index/'>Change your mind</a>.<br>
	{/if} {*end if stopped_all*}
	{/if} {*end if stopped_one*}

