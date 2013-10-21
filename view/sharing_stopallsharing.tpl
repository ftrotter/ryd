


	{if $stopped}

	
<h3> You have stopped all sharing </h3>

	{else}


<h2> Are you sure? </h2>
<form method='POST'>
Are you sure you want to stop all sharing? This will delete ALL of your sharing relationships.<br>
You will not be able to recover who you were sharing with.<br>
You can always <a href='/index.php/sharing/index/'>Change your mind.</a><br>
<input type='hidden' name='sure' value='true'> <br>
<input type='submit' name='I am sure, Stop Sharing' value='I am sure, Stop Sharing'>

</form>

	{/if} {*end of else for stopped*}


