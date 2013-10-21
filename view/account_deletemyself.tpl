{if $deleted}
	<h1>Your account was deleted, along with all recordings and phone information </h1>
	You are now being returned to the main screen
{else}
<h1> Warning, this is unrecoverable. </h1>
<p>
	Once you have deleted your account there is no way to recover your recordings.
Please do not take this decision lightly.

</p>
<form method='POST'>
	<input type='submit' name='really' value='Really Delete?' onclick="return confirm('Are you sure you want to do that?');">	
</form>
{/if}
