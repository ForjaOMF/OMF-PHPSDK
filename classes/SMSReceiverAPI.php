<?php
class SMSReceiver
{
  function GetSMSList($url, $login, $pwd)
  {

		// Open the Imap session
		$mbox = imap_open($url, $login, $pwd);

		// Search api mails
		echo "Listing mails from sms\n";
		$new_messages = imap_search($mbox,'TEXT "Texto:"');

		if ($new_messages == false) {
   		echo "Call failed\n";
		} else {
   		foreach ($new_messages as $val) {
     	  echo "\n";
				$message = imap_body($mbox,$val,'FT_UID');
				$processing = explode("\n", $message);
				$msisdn = explode(":", $processing[0]);
				$text = explode(":", $processing[1]);
	
				// Print the result
				echo $msisdn[1] ."\n";
				echo $text[1];
   		}
		}
		imap_close($mbox);
	}
}
?>