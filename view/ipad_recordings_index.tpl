


	<fieldset>
		<legend> Recordings </legend>

	{if empty($recordings)}
		<h2> You have no recordings </h2>
		{if $has_phone}
		<h3>Have you made your test call to <a href='tel:{$recording_phonenumber|phone}'>{$recording_phonenumber|phone}</a>?</h3> 
		If you have not already, go ahead and make the test call now, so that you can see how the recording system works.<br> 
		Then you can <a href='javascript:this.location.reload();'>refresh this page</a> to see your test recording.
		{else}
			You need to have a phone number associated with your account. 
			<a href='/index.php/account/manage_phones/'>Add your Phone</a>

		{/if}
	{else}

	{include file='recording_message.tpl'}

		{foreach from=$recordings key=id item=rec_array}

	<fieldset>
	<legend>  {$rec_array.name} {if $rec_array.is_owner} <a name='{$id}' onclick='toggle("edit_name_{$id}");' href='#{$id}'>(rename)</a> {/if} </legend>	

<div id='edit_name_{$id}' style='display: none'>
<form id='form_{$id}' action='/index.php/recordings/changename' method='POST'>
<input type='text' name='new_name' size='40' value='{$rec_array.name}'> 
<input type='hidden' name='recording_id' value='{$id}'>
<input type='submit' value='Rename'>
</form>
</div>
<span class='accessible'>
<a href=\"javascript:niftyplayer('niftyPlayer_{$id}').play()\">play {$id}</a>,
<a href=\"javascript:niftyplayer('niftyPlayer_{$id}').pause()\">pause {$id}</a>,
<a href=\"javascript:niftyplayer('niftyPlayer_{$id}').stop()\">stop {$id}</a><br>
</span>
<div id='share_div_{$id}' style='float: right;'>
{if !empty($rec_array.share)}
	<ul>
	{foreach from=$rec_array.share item=share_info}
		<li>			
			<div class='buttons'>
			<a class='negative' href='/index.php/sharing/stopsharing/?user_id={$share_info.user_id}&recording_id={$id}'>   
			<img src='/images/icons/stop.png' alt='stop sharing recording {$id}'/>
			</a>
			</div>
			{$share_info.name}
		</li>
	{/foreach}
	</ul>
{else}
	<h5>Not Shared</h5>
{/if}
</div>
		<br>
		<audio controls='true' src='{$rec_array.mp3url}&as=0'>  Audio not supported</audio>
		<br><br>
	<div class='buttons'>
	{if $rec_array.is_owner}
		
		<a class='negative' href='{$rec_array.deleteurl}'> <img src='/images/icons/delete.png' alt=''/> Delete</a>
		{if $rec_array.is_locked}
			<a href='{$rec_array.unlockurl}'> <img src='/images/icons/lock.png' alt=''/> Unlock </a>
		{else}
			<a class='positive' href='{$rec_array.shareurl}'> <img src='/images/icons/email_go.png' alt=''/> Share</a>
			<a href='{$rec_array.lockurl}'> <img src='/images/icons/key.png' alt=''/> Lock </a>
		{/if}
	{/if}
	</div>
	</fieldset>
	<br>
			{/foreach}

	{/if} {* end else for not empty of recordings*}

	</fieldset>
