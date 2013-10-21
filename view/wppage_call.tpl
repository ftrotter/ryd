<div class='container_16'>
<div class='grid_16'>

{if $valid_login}
<h2> {$page_title} </h2>
{$page_content}

{else}
<a href='/'>
<img src='/images/logo.gif'> 
</a>
<br>
{include file='big_menu.tpl'}
<div style='width: 80%; margin-left: auto ; margin-right: auto ;'>
<h2> {$page_title} </h2>
{$page_content}
</div>
<br><br>

{/if}
</div>
</div>
