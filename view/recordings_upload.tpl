

	{if !$paid}
		<h3> You must <a href='/index.php/account/subscribe/'>subscribe</a> 
		to be able to upload.</h3>
	{else}

		{if $message }
			<h3> {$message} </h3> Would you like to <a href='/index.php/recordings/upload/'> upload another </a>?
		{else}	
		<form enctype='multipart/form-data' action='/index.php/recordings/upload/' method='POST' id='mp3_upload_form'>
		<input type='hidden' name='upload' value='true'>
		Choose a file to upload: <input name='mp3file' type='file' /><br>
		Type a short description for this file: <input name='name' type='text' size='40'><br>
		<input type='submit' value='Upload File' />
		</form>
		{/if}
	{/if}

