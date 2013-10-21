<?php
//this makes a failing HTTP_X_TWILIO_SIGNATURE header element
//which can be used to test twilio authentication
//it should fail :)


$demo_array = array (
  'AccountSid' => 'BLAH',
  'CallStatus' => 'completed',
  'CalledVia' => '',
  'RecordingUrl' => 'http://api.twilio.com/2008-08-01/Accounts/BLAH/Recordings/00000000000000',
  'Called' => '4155992671',
  'Digits' => 'hangup',
  'CallerCountry' => 'US',
  'CalledZip' => '94949',
  'CallerCity' => 'LEAGUE CITY',
  'Caller' => '7138933361',
  'CalledCity' => 'NOVATO',
  'CallSegmentGuid' => '',
  'CalledCountry' => 'US',
  'Duration' => '16',
  'CallerState' => 'TX',
  'RecordingFileSize' => '120640',
  'CallSid' => 'CA0000000000',
  'CallGuid' => 'CA0000000000',
  'AccountGuid' => 'AC000000000',
  'CalledState' => 'CA',
  'CallerZip' => '77040',
);

echo "
<html><head>
	<title>twilio test form</title>
</head><body>
<h1>twilio test form</h1> 
<form action='twilio.php/test/index' method='POST'>
";

foreach($demo_array as $key => $value){
	echo "<input type='text' value='$value' name='$key'> <br>";
}


echo "<input type='submit'></form></body></html>";

?>
