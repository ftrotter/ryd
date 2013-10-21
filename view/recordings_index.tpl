


	<fieldset>
		<legend>{$display_name} {if $is_trash} Trash {else} Recordings {/if} </legend>

	{if empty($recordings)}
		{if $is_trash}
			<h2> Your trash is empty </h2>
			<p> You might like to see your <a href='{$recordings_url}'>recordings</a> </p>
		{else}
			<h2> You have no recordings. </h2>
			{if $has_phone}
			<br>
			<h3>Have you made your test call to <span class='green'>{$recording_phonenumber|phone}</span>? 
			If you haven't, make the test call now.</h3>
			<br>
			<h3>Then <a href='javascript:this.location.reload();'>refresh this page</a> to see your test recording.
			{else}
				You need to have a phone number associated with your account. 
				Please visit this website from a normal browser to add your phone number.
			{/if}
		{/if}
	{else}

	{include file='recording_message.tpl'}

		{foreach from=$recordings key=id item=rec_array}

	<fieldset>
	<legend> #{$rec_array.recording_number}       
 {$rec_array.name} {* <a href="#http://www.youtube.com/watch?v=Z6zIUvUIKOQ#{$id}_idhelp" title="Each recording has a Recording Name and a Recording Number. The Recording Number is constant. If you remember the Recording Number you can quickly use the Playback Phone Number to play your recordings using your phone. The phone menu that you will hear when you call the Playback Phone Number will use the same Recording Numbers that you see here. For instance, if you can remember that you wanted to show your friend Recording #15, you can just call the Playback Phone Number, and enter 1 then 5 then #(pound sign) on your phone. This will play Recording Number 15. You cannot change the Recording Number of a recording, but you can change the Recording Name at any time. You should change the Recording Name to something that will help you remember what the recording is back. You can do this at any time by clicking the Rename button. " onclick="return false;" >(help)</a>*} </legend>	



<span class='accessible'>
<a href=\"javascript:niftyplayer('niftyPlayer_{$id}').play()\">play {$rec_array.recording_number}</a>,
<a href=\"javascript:niftyplayer('niftyPlayer_{$id}').pause()\">pause {$rec_array.recording_number}</a>,
<a href=\"javascript:niftyplayer('niftyPlayer_{$id}').stop()\">stop {$rec_array.recording_number}</a><br>
</span>
{if $rec_array.first_recording}
<div style='font-size: .8em'>Press the play button (<img style="vertical-align:middle" width='20' height='20' src='/images/play_button.jpg'>) to hear a recording. After you start a recording you can pause it at any time by pressing the pause button (<img style="vertical-align:middle" width='20' height='20' src='/images/pause_button.jpg'>). If you want to return to the beginning of a recording, you can press the reset (<img style="vertical-align:middle" width='20' height='20' src='/images/reset_button.jpg'>) button.
<!--[if IE 9]>
<br>
(If you see this symbol 
<img style="vertical-align:middle" width='13' height='13' src='/images/blue_x.jpg'>
in the box below,
go the top of the screen and click on that symbol to "Turn off ActiveX Filtering.")
<![endif]-->
</div>
{/if}
{if $is_owner}
<div id='share_div_{$id}' style='float: right; width: 250px; '>
{if !empty($rec_array.share)}
	<h4> Shared With </h4>
	<ul style='list-style-type: none;'>
	{foreach from=$rec_array.share item=share_info}
		<li>			
			<a class='nomarkup' title='Stop sharing with {$share_info.name}' href='/index.php/sharing/stopsharing/?user_id={$share_info.user_id}&recording_id={$id}'>   
			<img border='0' src='/images/icons/stop.png' alt='stop sharing recording {$rec_array.recording_number}'/>
			</a>
			{$share_info.name}
		</li>
	{/foreach}
	</ul>
{else}
	{if $rec_array.is_locked}
		<h4>  Recording Locked </h4>

	{else}
		<h4>Not Shared </h4>
	{/if}
{/if}
</div>
<div style='float: right; padding: 10px'> {*<a class='showLeft' href="http://www.youtube.com/watch?v=2xqDn8nCd9E#{$id}_sharedwithhelp" alt="" onclick="return false;" >(help)</a>*} </div>
{/if} {*end if owner*}


<div id='flash_div_{$id}' style='float: left'>
		<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0' width='250' height='60' id='niftyPlayer_{$id}' align=''>
		 <param name=movie value='/flash/niftyplayer_big.swf?file={$rec_array.mp3url}&as=0'>
		 <param name=quality value=high>
		 <param name=bgcolor value=#FFFFFF>
		 <embed src='/flash/niftyplayer_big.swf?file={$rec_array.mp3url}&as=0' quality=high bgcolor=#FFFFFF width='250' height='60' name='niftyPlayer_{$id}' align='' type='application/x-shockwave-flash' swLiveConnect='true' pluginspage='https://www.macromedia.com/go/getflashplayer'>
		</embed>
		</object>
</div>

	{if $rec_array.is_owner}

<div id="content" style='clear: left'>
<div class="center">
      {*<a href="http://www.youtube.com/watch?v=tCtHZQ_daQ4#{$id}_buttonshelp" alt="" onclick="return false;" >(help)</a>*}
</div>
</div>


	<div class='buttons' style='clear: both'>


		{if $is_trash}
			<a class='positive' title='Restore {$rec_array.recording_number} to the inbox' href='{$rec_array.untrashurl}' > <img src='/images/icons/arrow_left.png' alt=''/>Restore to Inbox</a>

			<a class='negative' title='Permenantly Delete recording number {$rec_array.recording_number}' href='{$rec_array.deleteurl}'> <img src='/images/icons/bin_closed.png' alt=''/> Permanently Delete</a>


		{else}
			<a class='positive' title='Rename recording {$rec_array.recording_number}' href='#{$id}' onclick='toggle("edit_name_{$id}"); return false;' > <img src='/images/icons/field_edit.png' alt=''/>Rename Recording</a>
{if $rec_array.first_recording}
<div style='float: left; font-size: .8em'> Rename this recording so that you can remember what the recording is about<br> (for example 'My Last Foot appointment for my Diabetes') </div>
{/if}
<div id='edit_name_{$id}' style='display: none; clear: both'>
<br>
<form id='form_{$id}' action='/index.php/recordings/changename' method='POST'>
<input type='text' name='new_name' size='40' value='{$rec_array.name}'> 
<input type='hidden' name='recording_id' value='{$id}'>
<input type='submit' value='Rename'>
</form>
</div>
<br>
<br>

<div style='clear: both'>

			{if !$rec_array.has_notes}
			<a class='positive' title='Create comments on {$rec_array.recording_number}' href='#{$id}' onclick='turn_on("notes_{$id}"); return false;' > <img src='/images/icons/comments.png' alt=''/>Notes</a>
			{/if}
			{if $use_trash}
			<a class='negative' title='Trash recording number {$rec_array.recording_number}' href='{$rec_array.trashurl}'> <img src='/images/icons/bin_closed.png' alt=''/> Trash</a>
			{else}
			<a class='negative' title='Delete recording number {$rec_array.recording_number}' href='{$rec_array.deleteurl}'> <img src='/images/icons/bin_closed.png' alt=''/> Delete</a>

			{/if}
			<a class='smaller positive' title='Display Advanced Options for recording number {$rec_array.recording_number}' href='#{$id}' onclick='toggle("advanced_{$id}"); return false;' > 
				<img src='/images/icons/add.png' alt=''/>Advanced Functions</a>
			<div style='display: none;'  id='advanced_{$id}'>
			<a class='positive' title='Download recording number {$rec_array.recording_number}' href='{$rec_array.downurl}'> <img src='/images/icons/disk.png' alt=''/>Download</a>


			{if $rec_array.is_locked}
				<a title='Allow sharing of recording {$rec_array.recording_number}' href='{$rec_array.unlockurl}'> <img src='/images/icons/lock_open.png' alt=''/> Unlock </a>
			{else}
{if $sharing_works}
				<a title='Share recording {$rec_array.recording_number}' class='positive' href='{$rec_array.shareurl}'> <img src='/images/icons/email_go.png' alt=''/> Share</a>
				<a title='Prevent all sharing of recording {$rec_array.recording_number}' href='{$rec_array.lockurl}'> <img src='/images/icons/key.png' alt=''/> Lock </a>
{/if} {*end if sharing works *}
			{/if} {* end is_locked*}
			</div>
</div>
		{/if} {* end is_trash*}
		
	</div>
	{/if} {* end is_owner*}

{if $is_owner}
{if $rec_array.comment_id > 0}
	<div id='notes_{$id}' style='clear: both;'>
{else}
	<div id='notes_{$id}' style='display: none; clear: both;'>
{/if}
<br>
<form id='notes_form_{$id}' action='{$rec_array.commentsaveurl}' method='POST'>
<textarea name="editor_{$id}">{$rec_array.comment_text}</textarea>
	
			<script type="text/javascript">
				CKEDITOR.replace( 'editor_{$id}' {literal},
    {
        toolbar : 'YDAtoolbar',
        uiColor : '#96D550',
	customConfig : '/ckeditor/YDA_config.js'

    }
  {/literal} );
			</script>
<input type='hidden' name='comment_id' value='{$rec_array.comment_id}'>
<input type='hidden' name='recording_id' value='{$id}'>
<input type='submit' value='Save Notes'>

</form>
</div>
{else} {* else for is owner.. this is what happens if you are not the owner of a comment *}
	{if $rec_array.comment_id > 0}

	<div id='notes_{$id}' style='clear: both;'>
	<h2>The recordings owner has commented on this recording: </h2>
	{$rec_array.comment_text}
	</div>
	{/if}
{/if} {*end if is owner for the editable note*}


	</fieldset>
			{/foreach}

	{/if} {* end else for not empty of recordings*}

	</fieldset>
