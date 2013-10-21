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

{literal}
<style type="text/css">
		/* Basic page formatting. */
		body {
			font-family:"Helvetica Neue", Helvetica, Arial, sans-serif;
		}
</style>
{/literal}	
<script type="text/javascript" src="/js/csspopup.js"></script>
<script type="text/javascript" src="/js/util.js"></script>
<script type="text/javascript" src="/ckeditor/ckeditor_basic.js"></script>

{if $userfly}
<script type="text/javascript">
var userflyHost = (("https:" == document.location.protocol) ? "https://secure.userfly.com" : "http://asset.userfly.com");
document.write(unescape("%3Cscript src='" + userflyHost + "/users/59470/userfly.js' type='text/javascript'%3E%3C/script%3E"));
</script>
{/if}

</head> <body>	
<div id='accessible_menu'>

<!--later -->

</div>

{if $valid_login}

<!--top left in natural alignment -->
<div id='logo' style='float: left; width: 25%'>
<img src='/images/logo.gif' alt='Logo' \>
</div>

<div id='contents' style='float: left; padding: 10px; width: 75%'>

{$view_contents}

</div>
</div>
{else}
<a href='/'> You need to login to use the system </a>

{/if}


{literal}
	<script type="text/javascript" src="/js/csspopup.js"></script>	
	<script type="text/javascript" src="/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="/js/jquery.qtip-1.0.0-rc3.min.js"></script>
	<script type="text/javascript" src="/js/openid-jquery.js"></script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/swfobject/2.1/swfobject.js"></script>

<script class="qtip" type="text/javascript">



$(document).ready(function()
{
   // By suppling no content attribute, the library uses each elements title attribute by default
   $('#content a[href][title]').qtip({
      content: {
         text: false // Use each elements title attribute
      },
      style: 'green' // Give it some style
   });


   // Use each method to gain access to all youtube links
   $('a[href*="youtube."]').each(function()
   {
      // Grab video ID from the url
      var videoID = $(this).attr('href').match(/watch\?v=(.+)+/);
      videoID = videoID[1];

	//we need to make sure help tags on the right side of the screen
	//open the video in the middle of the screen... we use the showLeft class for this...
	if($(this).hasClass('showLeft')){
		var tooltipLoc = 'rightMiddle';
		var targetLoc = 'leftMiddle';
	}else{
		var tooltipLoc = 'leftMiddle';
		var targetLoc = 'rightMiddle';	
	}

      // Create content using url as base
      $(this).qtip(
      {
         // Create content DIV with unique ID for swfObject replacement
         content: '<div id="youtube-embed-'+videoID+'">You need Flash player 8+ to view this video.</div>',
         position: {
            corner: {
               tooltip: tooltipLoc, // ...and position it center of the screen
               target: targetLoc // ...and position it center of the screen
            }
         },
         show: {
            when: 'click', // Show it on click...
            solo: true // ...and hide all others when its shown
         },
         hide: 'unfocus', // Hide it when inactive...
         style: {
            width: 432,
            height: 264,
            padding: 0,
            tip: true,
            name: 'dark'
         },
         api: {
            onRender: function()
            {
               // Setup video paramters
               var params = { allowScriptAccess: 'always', allowfullScreen: 'false' };
               var attrs = { id: 'youtube-video-'+videoID };

               // Embed the youtube video using SWFObject script
               swfobject.embedSWF('http://www.youtube.com/v/'+videoID+'&enablejsapi=1&playerapiid=youtube-api-'+videoID,
                                 'youtube-embed-'+videoID, '425', '264', '8', null, null, params, attrs);
            },

            onHide: function(){
               // Pause the vide when hidden
               var playerAPI = this.elements.content.find('#youtube-video-'+videoID).get(0);
               if(playerAPI && playerAPI.pauseVideo) playerAPI.pauseVideo();
            }
         }
      }
      ).attr('href', '#');
   });
});
</script>


{/literal}
{literal}
	<script type="text/javascript">
	$(document).ready(function() {
	    openid.init('openid_identifier');
	});
	</script>
{/literal}

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
    s.src = ("https://cdn.uservoice.com/javascripts/widgets/tab.js";
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
