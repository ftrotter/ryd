

	{if $sent}
		{$message}
	{else}




{$message}
<table>
<tr>
<td class='two_choice'>
<form method='POST'>
<input type='hidden' name='email' value='{$email}'>
<input type='hidden' name='send_invite' value='true'>
<input type='submit' name='Invite {$email}' value='Yes, send an invitation to {$email}' onclick='toggle(\"sending_message\");'>
</form>

</td>
<td class='two_choice'>
<span class='andor'>OR</span>
</td>
<td class='two_choice'>
No, I would rather return to <a href='/index.php/recordings/index/'>my recordings</a>
</td>
</tr>
</table>
<div id='sending_message' style='display: none'>
<br>
<h1>Please wait while we send that invitation! </h1>
<img src='/images/progress.gif'>
</div>
<br>

	{/if}
