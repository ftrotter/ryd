<div class='container_16'>

<div class='grid_16'>
{include file='big_menu.tpl'}
</div>
<div class='grid_7'>
<img width="300" src='/images/logo.gif' alt='Logo' \> 
</div>

<div class='grid_7'>
<br><br>
<fieldset>
	<legend>Sign up options: </legend>
	<ul style='list-style: disc inside'>
		<li>
		<a href='/index.php/signup/subscribe/?code=trial'>
			Sign up for a six-month free trial.</a>
		</li>
		<li>
		<a href='https://yourdoctorsadvice.org/index.php/wppage/gift/'>
			Give as a gift! </a>
		</li>

	</ul>
</fieldset>
<br>
 <fieldset >
	<legend> Already have an account? Login here:</legend> 
                <p><a class="btn-auth btn-openid  " 
			href="/index.php/hybridauth/index/?provider={$open_id_phone_provider}"
			>Sign in with your <b>cell phone.</b></a> <br>
		</p>
                <p><a class="btn-auth btn-openid " 
			href="/index.php/hybridauth/index/?provider={$open_id_email_provider}"
			>Sign in with your <b>Email account</b></a></p>
  </fieldset>



{literal}
<script type="text/javascript">
  /*  $('#other_login_link').qtip(
    {
        id: 'modaldetails', // Since we're only creating one modal, give it an ID so we can style it        
	content: {
            text: $('#other_logins'),
            title: {
                text: 'Details',
                button: true
            }
        },
        position: {
            my: 'center', // ...at the center of the viewport
            at: 'center',
            target: $(window)
        },
        show: {
            event: 'click', // Show it on click...
            solo: true, // ...and hide all other tooltips...
            modal: true // ...and make it modal
        },
        hide: false,
        style: 'ui-tooltip-light ui-tooltip-rounded'
    });
*/
$('#other_login_link').qtip(
    {

   content: {
      text: $('#other_logins'),
      title: {
        text: 'Login using any of these services',
	button: 'Close'
      }
   },
           
   position: {
      my: 'top center',
      at: 'bottom center',
      target: $('#other_login_link')
   },
   show: {
      event: 'click', 
      modal: {
         on: true
      }
   }
});


{/literal}
</script>
</div>


{include file='home.tpl'}
</div>
