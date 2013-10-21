
<h3>DoctorsAdvice</h3> 
<h2>{$recording_phonenumber|phone}</h2>
<h3>(to record advice)</h3>
<br><br> 
<h3>DoctorsAdvicePlayback</h3> 
<h2>{$play_phonenumber|phone}</h2>
<h3>(to playback advice)</h3> 

</h4>

{if !empty($sub_menu)}
<br><br>

<ul>
<li> <a href='/index.php/recordings/index/'>Your Recordings</a></li>
</ul>
<h5> Recordings shared with you: </h5>
<ul>
{foreach from=$sub_menu key=item item=menu_array }
	
	<li {if $menu_array.active} id='active' {/if}>
		<a href='{$menu_array.url}' {if $menu_array.active} id='current' {/if}>
		 {if $menu_array.active}<span class='accessible'> active </span> {/if}	
		 {$item}
		</a>
	</li>

{/foreach}
</ul>
{/if}
