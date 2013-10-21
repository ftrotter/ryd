

	{assign var=manage_phones_url value='/index.php/account/manage_phones/'}

	{if !$paid}
<h2> You must <a href='/index.php/account/subscribe/'>Subscribe</a>
		 to be able to add phone numbers to your account.</h2>
		
	{else}

	{assign var=later_stage value=false} {*nothing has happened yet...*}

	{if $success }

<h2> Success! Your phone number was added to your account. </h2>
<div class='center'><p>
Any calls you make from this phone number to the main recording phone number {$recording_phonenumber|phone} 
will be recorded and added to your account. 
</p>
<h3>You should make a test call now to {$recording_phonenumber|phone}. Give it a try!!</h3>
<p>
You should save the {$print_url} phone number, {$recording_phonenumber|phone}, in your phone's contact list,
so that you can easily call it the next time you visit your doctor.
After your test call, you can access your test recording on the <a href='/index.php/recordings/index/'>Recording</a> tab. (You can also get there using the first menu item) </p><p>
You can also listen to any of your recordings by calling the {$print_url}
playback phone number: {$play_phonenumber|phone}
</p>
</div>

	{assign var=later_stage value=true} {*ok we are doing something later...*}
	{/if}

	{if $wrongpin}

<h2> Opps! The PINs did not match. </h2>
You can return to the <a href='{$manage_phones_url}'>manage phones</a> page to restart this process.

	{assign var=later_stage value=true} {*ok we are doing something later...*}
	{/if}

	{if $phoneused }

<h3> That phone number is already in use! </h3>
You can have many phone numbers associated with your account. <br>
But each phone number can only be assigned to one account.<br>
This phone number is already in use in this system. <br>
Return to the <a href='{$manage_phones_url}'>manage phones</a> page to add a different phone.<br>
<br><br>
Or you can continue adding {$phone|phone} to your account. <br>
By continuing you will remove the phone from the previous owner. <br>
We will send an email to that owner notifying them that your account is now associated with {$phone|phone}.

<form method='POST'>
Please enter the verification PIN from the call.<br>
<br>
<input type='checkbox' name='override'> Transfer this {$phone|phone} to this account?  <br>
<input type='hidden' name='phone' value='{$phone}'>
<input type='submit' name='Transfer' value='Transfer'>

</form>

	{assign var=later_stage value=true} {*ok we are doing something later...*}
	{/if}


	{if $called}
	{*we have just made the call, now gather the pin*}
<h3> We are calling you at {$called}. </h3>
<form method='POST'>
Please enter the verification PIN from the call.<br>
Verification PIN: <input type='text' name='pin' AUTOCOMPLETE='off' size='4' maxlength="4"> <br>
<input type='submit' name='Verify' value='Verify'>

</form>
<br><br><br>
If you need to try again, return to the <a href='{$manage_phones_url}'>manage phones</a> page


	{assign var=later_stage value=true} {*ok we are doing something later...*}
	{/if}


{if !$later_stage}

	{if !empty($phone_array) }
		<h2>Current Phone Numbers</h2>
		<ul>
	{/if}
		
	{foreach from=$phone_array key=id item=phone}
		<li> {$phone|phone} <a href='/index.php/account/delete_phone?id={$id}'>(delete this phone)</a></li>
	{/foreach}

	{if !empty($phone_array) }
		</ul>
	{/if}


<h3> Add a new phone number. </h3>
<form method='POST'>
<p>
<b><i>Your Doctor's Advice</i></b> (<b><i>YDA</i></b>) works by recognizing your cell phone number.  (You don't have to enter an account number.)
 </p>
<p>
When you add a phone to your account, we will verify that "it's you" by giving you a test call.
 </p>
<p>
Enter your 10-digit U.S. cell  phone number below, so that you can use that cell phone to use <b><i>YDA</i></b>.  Any phone format will work, as long as it has 10 digits (3-digit area code plus 7-digit local number).
 </p>
 

Phone: <input type='text' name='phone' size='10' maxlength="15"><br>
<p>
When you get your test call, you will be given a 4-digit PIN number that you will need to enter on the next page.
</p>
I am ready for my test call: <input type='submit' name='Verify' value='Verify Phone'>

</form>

{/if} {*end not later stage if*}



{/if} {* end not paid else*}
	

