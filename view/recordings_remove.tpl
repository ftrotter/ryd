

	{if $deleted}
<h3> "{$name}" has been deleted.</h3>
<br>
Return to the <a href='{$recordings_url}'>the recordings list.</a> {if $use_trash} or you can view the recordings in your <a href='{$trash_url}'>trash </a> {/if}
	{else}
<h3>Are you sure you want to delete recording "{$name}"? </h3>
<form  action='/index.php/recordings/remove/' method='POST' id='mp3_upload_form'>
<input type='hidden' name='delete' value='true'>
<input type='hidden' name='recording_id' value='{$recording_id}'>
<input type='submit' value='I am sure. Delete.' />
</form>
<br>
Or return to the <a href='{$recordings_url}'>the recordings list.</a> {if $use_trash} or you can view the recordings in your <a href='{$trash_url}'>trash </a> {/if}


	{/if}
<p>
</p>


