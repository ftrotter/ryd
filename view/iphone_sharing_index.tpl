<body class='list'>
<div id="topbar" class="transparent">
	<div id="title">
		{$app_name}</div>
	</div>
<div id="content">
<span class="graytitle">Sharing Menu</span>
	<ul>
	{if !$paid}
		<li class="textbox"><span class="header">
	You must subscribe to be able to share recordings
		</span><p>
		Access {$app_name} from a computer with a full browser to subscribe 		
		</p>
		</li>

	{else}


	{if !empty($to_users)}
		<li class="textbox"><span class="header">
	You are sharing with the following people
		</span><p>
		Access {$app_name} from a computer with a full browser to share with more people		
		</p>
		</li>


	<li class='title'>Sharing With:</li>

	{foreach from=$to_users key=user_id item=user_array}
<li class="withimage">
		<span class="name"> {$user_array.to_name} </span>
		<span class='comment'>	
		 		Sharing 
				{if $user_array.future_sharing} Future + {/if} 
				{if $user_array.count} {$user_array.count} {else} 0 {/if} 
				(of {$recording_list|@count}) 
		</span></li>

	{/foreach}
		
		<li class="textbox"><span class="header">
	This interface will allow you to stop all sharing
		</span><p>
		To control sharing with individual users, login with a computer	
		</p>
		</li>
	<li class="menu"><a href='/index.php/sharing/stopallsharing/'>
		<span class="name">
		Stop all sharing </span><span class="arrow"></span></a></li>


	{else}  {*you are not sharing with anyone*}
		<li class="textbox"><span class="header">
	You are not sharing with anyone
		</span><p>
		Access {$app_name} from a computer with a full browser to share with more people		
		</p>
		</li>


	{/if} 
	{/if} {*if not paid*}
	</ul>
</div>
