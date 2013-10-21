<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="yes" name="apple-mobile-web-app-capable" />

<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<link href="/images/iphone_logo.gif" rel="apple-touch-icon" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
<link href="/css/style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="/css/openid.css" rel="stylesheet" media="screen" type="text/css" />
<script src="/js/functions.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/jquery-1.2.6.min.js"></script>
<script type="text/javascript" src="/js/openid-jquery.js"></script>
{literal}
	<script type="text/javascript">
	$(document).ready(function() {
	    openid.init('openid_identifier');
	});
	</script>
{/literal}
<title>YourDocsAdvice</title>
<meta content="YDA" name="Keywords" />
<meta content="This website lets you record healthcare conversations." name="description" />
</head>

{$view_contents}

{literal}
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
</body>
</html>

