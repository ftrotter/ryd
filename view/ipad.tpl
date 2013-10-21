<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Your Doctor's Advice (xhtml)</title>

<link rel='stylesheet' type='text/css' media='screen' title='buttons' href='/css/merged.css' />
{*
<link rel='stylesheet' type='text/css' media='screen' title='buttons' href='/css/button.css' />
<link rel='stylesheet' type='text/css' media='screen' title='menu' href='/css/menu.css' />
*}
{*<link rel='stylesheet' type='text/css' media='screen' title='main' href='/css/main.css' />*}
{*<link rel='stylesheet' type='text/css' media='screen' title='nifty' href='/css/nifty_main.css' />*}


<link rel="stylesheet" href="/css/openid.css" />
	<script type="text/javascript" src="/js/csspopup.js"></script>	
	<script type="text/javascript" src="/js/jquery-1.2.6.min.js"></script>
	<script type="text/javascript" src="/js/openid-jquery.js"></script>
{literal}
	<script type="text/javascript">
	$(document).ready(function() {
	    openid.init('openid_identifier');
	});
	</script>

<style type="text/css">
		/* Basic page formatting. */
		body {
			font-family:"Helvetica Neue", Helvetica, Arial, sans-serif;
		}
</style>
{/literal}	
<script type="text/javascript" src="/js/csspopup.js"></script>
<script type="text/javascript" src="/js/util.js"></script>

</head> <body>	
<div id='accessible_menu'>

<!--later -->

</div>

{if $valid_login}

<!--top left in natural alignment -->
<div id='logo' style='float: left; width: 25%'>
<img src='/images/logo.gif' alt='Logo' \>
</div>
<div id='top_message' style='float: left;  padding: 20px; width: 600px'>
<div id='menu_spacer' style='float: left;  height: 10px; width: 100%'>

</div>

{include file='top_message.tpl'}
</div>
<div id='below' style='clear: both; '>

<div id='left_bar' style='float: left;padding: 10px; width: 25%'>
{include file='left_bar.tpl'}
</div>

<div id='contents' style='float: left; padding: 10px; width: 70%'>

{$view_contents}

</div>
</div>
<div id='mainmenu'>

{include file='menu.tpl'}

</div>

{else}
{*display only the login screen*}

{$view_contents}


{/if}

{literal}
<script type="text/javascript">
  var uservoiceOptions = {
    key: 'yourdoctorsadvice',
    host: 'yourdoctorsadvice.uservoice.com', 
    forum: '47087',
    alignment: 'right',
    background_color:'#FF0000', 
    text_color: 'white',
    hover_color: '#0066CC',
    lang: 'en',
    showTab: true
  };
  function _loadUserVoice() {
    var s = document.createElement('script');
    s.src = ("https:" == document.location.protocol ? "https://" : "http://") + "uservoice.com/javascripts/widgets/tab.js";
    document.getElementsByTagName('head')[0].appendChild(s);
  }
  _loadSuper = window.onload;
  window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-3705396-8']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
{/literal}


</body></html>
