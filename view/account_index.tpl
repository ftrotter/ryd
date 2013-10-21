
	<h2> Account Options </h2>
	
	<ul>
	{foreach from=$links key=link_text item=link_url}
		<li><a href='{$link_url}'>{$link_text} </a> </li>
	{/foreach}
	</ul>
	
	<h3> Subscription Status </h3>

	{if $paid }
		Your Recording Subscription is current, and is good until {$good_til}
	{else}
		You do not yet have a <a href='{$upgrade_link}'>Recording Subscription</a>
	{/if}
