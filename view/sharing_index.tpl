

	{if !$paid}
		<h3> You must <a href='/index.php/account/subscribe/'>
		subscribe</a> to be able to share recordings</h3>

	{else}


	{if !empty($new_users)}


		<h4> User(s) that you invited have joined, and you can share with them now.</h4>
		{foreach from=$new_users  key=user_id item=user_email}
<form method='GET' action='/index.php/sharing/manage/' > 
<input type='hidden' name='email' value='{$user_email}'>
<input type='submit' value='Share with {$user_email}'><br>

</form>

		{/foreach}
	{/if}
		<br>
	
<h4> Share your recordings with a new person.</h4>
<form method='GET' action='/index.php/sharing/manage/' > 
Enter the email address of the person who you want to share your recordings with:<br>
<input type='text' name='email'>
<br>
<input type='submit' value='Share'>

</form>


	{if !empty($to_users)}


<h4> You are currently sharing with the following person(s): </h4>
<table class='chart'>
<thead><tr><th>Sharing With</th><th>Share Future Recordings?</th><th>Records Shared</th><th>Manage Sharing </th></tr></thead>
<tfoot><tr>
					<th scope='row'>{$user_count} Total Users</th>

					<td colspan='4'></td>
</tr><tfoot>
	

	{foreach from=$to_users key=user_id item=user_array}

<tr class="{cycle values="odd,even"}"> 	<td>{$user_array.to_name}</td> 
		<td>{if $user_array.future_sharing == 1} Yes {else} No {/if}</td>
		<td>{if $user_array.count == 0} 0 {else} {$user_array.count} {/if} (of {$recordings_list|@count})</td>
		<td>  <a href='/index.php/sharing/manage/?user_id={$user_array.to_user_id}'>
			Manage sharing with {$user_array.to_name}</a>
		</td>
</tr>

	{/foreach}
	{/if}

	{if !empty($to_users)}
		</table>
		<br><br><br><h4> I want to <a href='/index.php/sharing/stopallsharing/'>
		Stop All Sharing Immediately</a>
		</h4>
	{/if}
	{/if}


