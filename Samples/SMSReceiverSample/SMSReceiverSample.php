<?php
	include 'SMSReceiverAPI.php';

	$SMS = New SMSReceiver;
	$SMS->GetSMSList("{imap.gmail.com:993/imap/ssl}", "gmailuser", "gmailpasswd");
?>