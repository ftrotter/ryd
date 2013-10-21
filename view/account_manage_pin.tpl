

	{if $pin_code_set }

<h2> You have set the PIN on your account. </h2>
		
	{/if}

{if $pin_code != 0}
<a href='#' onclick='toggle("pin_display");'>Click to here to reveal the PIN code on this account </a>
<div id='pin_display' style='display: none;'>
<h1> Your PIN Code is {$pin_code}</h1>
</div>
{/if}


<h2> Would you like to change your PIN code settings? </h2>
<form method='POST'>
When you call the "playback advice" number, if you have a PIN code set you will need to type it in order to get access to your recordings...<br>
<br>
{if $pin_code != 0}
     Use a PIN? <input type='checkbox' checked name='use_pin' onclick='toggle("pin_div");' >
{else}
     Use a PIN?<input type='checkbox' name='use_pin' onclick='toggle("pin_div");' >
{/if}
{literal}
  <script type="text/javascript">
    function checkPass(){

      //Store the password field objects into variables ...
      var pass1 = document.getElementById('pin_code');
      var pass2 = document.getElementById('pin_code_2');
      //Store the Confimation Message Object ...
      var message = document.getElementById('confirmMessage');
      //Set the colors we will be using ...
      var goodColor = "#66cc66";
      var badColor = "#ff6666";
      //Compare the values in the password field 
      //and the confirmation field
      if(pass1.value == pass2.value){
        //The passwords match. 
        //Set the color to the good color and inform
        //the user that they have entered the correct password 
        pass2.style.backgroundColor = goodColor;
        message.style.color = goodColor;
        message.innerHTML = "PIN Codes Match!"
      }else{
        //The passwords do not match.
        //Set the color to the bad color and
        //notify the user.
        pass2.style.backgroundColor = badColor;
        message.style.color = badColor;
        message.innerHTML = "PIN Codes Do Not Match!"
      }
    }  
  </script>
{/literal}


<br>
	<div id='pin_div' {if $pin_code == 0} style='display: none' {assign var='pin_code' value=''} {/if}>
	PIN Code:	<input type='password' id='pin_code' name='pin_code' value='{$pin_code}'><br>
	Repeat PIN Code:	<input type='password' onkeyup="checkPass(); return false;" id='pin_code_2' name='pin_code_2' value='{$pin_code}'>
	<br>
	<div id='confirmMessage'>  </div>
	</div> 
<br>

<input type='submit' name='Change PIN Settings' value='Change PIN Settings'>

</form>



