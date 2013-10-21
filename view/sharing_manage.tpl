



	{if !$paid}
		<h3> You must <a href='/index.php/account/subscribe/'>subscribe</a> to be able to share recordings</h3>

	{else}


		

		
	<h3>Control Sharing with {$to_user_name} at ({$to_user_email})</h3>

{literal}
<script language='javascript'>

function legend_off(legend_name){
	var this_legend = document.getElementById(legend_name + '_legend');
	if(this_legend){
		this_legend.className = 'form_off';
	}
}

function legend_on(legend_name){
	var this_legend = document.getElementById(legend_name + '_legend');
	if(this_legend){
		this_legend.className = '';
	}
}

function button_off(button_name){
	var this_button = document.getElementById(button_name + '_button');
	if(this_button){
		this_button.disabled = true;
	}
	var this_button_div = document.getElementById(button_name + '_button_div');
	if(this_button_div){
		this_button_div.className = 'buttons_disabled';
	}
}

function button_on(button_name){
	var this_button = document.getElementById(button_name + '_button');
	if(this_button){
		this_button.disabled = false;
	}
	var this_button_div = document.getElementById(button_name + '_button_div');
	if(this_button_div){
		this_button_div.className = 'buttons';
	}
}

function form_off(){
	var checkboxes = document.forms['stop_some'].elements['recordings_array[]'];
	if(checkboxes){

		/* o the suffering if there are many checkboxes with the same name, then it is an array, but not if there is only one... but there is also an undefined length... */
		if (typeof checkboxes.length === 'undefined') {
			checkboxes.disabled = true;	
		}else{
			for(var i = 0, max = checkboxes.length; i < max; i++){
				checkboxes[i].disabled = true;
			}
		}
	}
	button_off('share');
	button_off('future');
	button_off('group');
	button_on('simple');
	legend_off('advanced');
	legend_off('global');
	legend_off('individual');

	var my_form = document.getElementById('advanced_form');
	if(my_form){
		my_form.className = 'form_off';
	}
	
	var my_table = document.getElementById('advanced_table');
	if(my_table){
		my_table.className = 'chart_form_off';
	}	
}


function form_on(){
	var checkboxes = document.forms['stop_some'].elements['recordings_array[]'];
	if(checkboxes){

		if (typeof checkboxes.length === 'undefined') {
			checkboxes.disabled = false;	
		}else{
			for(var i = 0, max = checkboxes.length; i < max; i++){
				checkboxes[i].disabled = false;
			}

		}

	}

	button_on('share');
	button_on('future');
	button_on('group');
	button_off('simple');
	legend_on('advanced');
	legend_on('global');
	legend_on('individual');

	var my_form = document.getElementById('advanced_form');
	if(my_form){
		my_form.className = '';
	}	

	var my_table = document.getElementById('advanced_table');
	if(my_table){
		my_table.className = 'chart';
	}	
}

</script>
{/literal}
<form method='post' action='/index.php/sharing/process_sharestop/'>
<input type='hidden' name='to_user_id' value='{$to_user_id}'>
<span class='hidden'>You can enable the more sharing options, by chosing 'Use Advanced Sharing Options' <br /></span>
<table>
<tr>
<td> <input type='radio' name='share' value='all' onclick='form_off();'> </td>
<td> <label for='all'> <h2>Share all unlocked recordings both current and future</h2></label> </td>
<td rowspan='3'>
<div id='simple_button_div' class='buttons_disabled'>
    <button id='simple_button' type='submit' disabled='true' >
        <h1>Save
</h1>
    </button>
</div>
</td>
</tr>
<tr>
<td>  <input type='radio' name='share' value='none' onclick='form_off();' ></td>
<td> <label for='none'> <h2>Stop all sharing with {$to_user_name} </h2></td>
</tr>
<tr>
<td> <input type='radio' name='share' value='control' onclick='form_on();'> </td>
<td> <label for='control'> <h2>Use Advanced Sharing Options for {$to_user_name} </h2> </label></td>
</tr>
</table>
</form>

<div style='clear: both;'></div>

		<div id='advanced_form' class='form_off' >
		<fieldset><legend id='advanced_legend' class='form_off' > Advanced Sharing Options</legend>
<div style='clear: both;'> </div>



		<fieldset> <legend id='global_legend' class='form_off' >Global Sharing Options </legend>
		<table id='advanced_table' class='chart_form_off'><thead><tr><th>Type</th><th>Status</th><th>Change</th></tr>

		<tr> <td>Share Future Recordings? </td><td> 
{if $future_sharing} Yes {else} No {/if}
</td><td> 
{*Future Sharing Form*}
{if $future_sharing}
<form id='future_form' method='POST' action='/index.php/sharing/process_future_recordings/'>
   <input type='hidden' name='to_user_id' value='{$to_user_id}'> 
   <input type='hidden' name='stop_future_sharing' value='true'>
<div id='future_button_div' class='buttons_disabled' > 
    <button id='future_button' type='submit' disabled='true' >
        <img src='/images/icons/stop.png' alt=''/> 
        Stop Future Sharing
    </button>
</div>
</form>
{else}
<form id='future_form' method='POST' action='/index.php/sharing/process_future_recordings/'>
   <input type='hidden' name='to_user_id' value='{$to_user_id}'> 
   <input type='hidden' name='start_future_sharing' value='true'> 
<div id='future_button_div' class='buttons_disabled'>
    <button id='future_button' type='submit' disabled='true' >
        <img src='/images/icons/add.png' alt=''/> 
        Start Future Sharing
    </button>
</div>
</form>


{/if}

</td> </tr>
		<tr> <td>Share All Records </td><td>
{if $shares_all}
	All
{else}
	{if $shares_some}
		Some
	{else}
		None
	{/if}
{/if}
</td><td> 

{*Share Form goes here..*}
{if $shares_some || $shares_all} 
<form id='share_form' method='POST' action='/index.php/sharing/process_recording_list/'>
   <input type='hidden' name='to_user_id' value='{$to_user_id}'> 
   <input type='hidden' name='stop_all_sharing' value='true'>
<div id='share_button_div' class='buttons_disabled'> 
    <button id='share_button' type='submit' disabled='true' >
        <img src='/images/icons/stop.png' alt=''/> 
        Stop All Current Sharing
    </button>
</div>
</form>

{else}
<form id='share_form' method='POST' action='/index.php/sharing/process_recording_list/'>
   <input type='hidden' name='to_user_id' value='{$to_user_id}'> 
   <input type='hidden' name='start_all_sharing' value='true'> 
<div id='share_button_div' class='buttons_disabled'>
    <button id='share_button' type='submit' disabled='true' >
        <img src='/images/icons/add.png' alt=''/> 
        Share All Current Recordings
    </button>
</div>
</form>

{/if}

</td> </tr>
		</table></fieldset>


		<br><fieldset> <legend id='individual_legend' class='form_off'>Individual Recordings Status</legend>


		<form id='stop_some' method='POST' action='/index.php/sharing/process_recording_list/'>
		<ul style='list-style-type: none;'>


		{foreach from=$shared_recordings_list key=recording_id item=recording_array}
			<li> 
				{if $recording_array.locked }
				&nbsp;<img src='/images/icons/lock.png' alt='Locked'>
				{else}
				<input type='checkbox' {if $recording_array.shared}checked='yes'{/if} name='recordings_array[]' id='check_{$recording_id}' value='{$recording_id}' disabled='true' />
				{/if}
				{$recording_array.recording_name }
			</li>		
		{/foreach}


		</ul> <div id='group_button_div' class='buttons_disabled'>
	<input type='hidden' name='to_user_id' value='{$to_user_id}'> 
    <button id='group_button' type='submit' disabled='true' >
        <img src='/images/icons/arrow_refresh.png' alt=''/> 
        Save Individual Recording Sharing Status
    </button>


</div> </form>
		{*end the big table*}
		</fieldset></fieldset>


	{/if} {*end if paid*}

