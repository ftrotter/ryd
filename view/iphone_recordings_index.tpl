

<body class="ipodlist">
<div id="topbar" class="transparent">
	<div id="title">
		{$app_name}
	</div>
</div>
<div id="content">

	{if empty($recordings)}
		<h2> You have no recordings </h2>
		{if $has_phone}
		<h3>Have you made your test call to <a href='tel:$record_phone'>$record_phone</a>?</h3> 
		If you have not already, go ahead and make the test call now, so that you can see how the recording system works.<br> 
		Then you can <a href='javascript:this.location.reload();'>refresh this page</a> to see your test recording.
		{else}
			You need to have a phone number associated with your account. 
			Please visit this website from a normal browser to add your phone number.
		{/if}
	{else}

	<ul>
		{*How to do paging?  probably at the controller level...*}
		{foreach from=$recordings key=id item=rec_array}
		<li>

		<span class="number">{$id}</span><span class="auto"></span><span class="name"> 
		{$rec_array.name} 
		</span>
		</li>
		<li>
		<span class="number"></span><span class="auto"></span><span class="name"> 
		<audio controls='true' src='{$rec_array.mp3url}'> Audio not supported</audio>
		</span>
		</li>
		{/foreach}

	</ul>
	
	{/if} {* end else for not empty of recordings*}
</div>

<br><br><br>
{include file='iphone_menu.tpl'}

<div id="footer">

</div>



