{if $header_message}
<br><br><br>
<h3>{$header_message}</h3>
{/if}
{if !$sharing_works}
<br><br><br>
<h3>The sharing feature cannot be used until you add <a href='/index.php/hybridauth/index/'>an email or Facebook login</a>. </h3>

{/if}
