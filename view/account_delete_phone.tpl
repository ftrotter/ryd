



	{if $deleted }

<h2> You have deleted this phone number. </h2>
Do you want to <a href='/index.php/account/manage_phones/'>manage phones</a>? You can add or delete more phones from there.
			{else}



<h2> Are you sure?? </h2>
<form method='POST'>
Are you sure you want to delete the phone {$phone|phone} from your account?<br>
If you do not have the phone with you, you will not be able to add it again.<br>
You can always <a href='/index.php/account/manage_phones/'>Change your mind</a>.<br>
<input type='hidden' name='phone_id' value='{$phone_id}'> <br>
<input type='hidden' name='sure' value='true'> <br>
<input type='submit' name='I am sure, delete.' value='I am sure, delete.'>

</form>

	{/if}


