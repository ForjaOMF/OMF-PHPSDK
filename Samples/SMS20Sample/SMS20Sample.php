<?php
    include "SMS20API.php";

    set_time_limit(600);

    $login = "666777888";
    $passw = "passwd";

    $contact = "666777999"; // telephone number to add contact

    $sms20 = new SMS20();
    $session = $sms20->Login($login, $passw);
    $sms20->Connect($login, "", $session);

    $transId = 15;

    $ListContacts = array();
    $ListContacts = $sms20->AddContact($session, $login, $contact, $transId);
    $transId = $transId + 3;

    $message1 = "";
    $list = array();

    // We make the sounding until receiving the message "good bye"
    $cuenta = 0;
    while ($message1 != " bye") {
        $response=$sms20->Polling($session);

        $xml = simplexml_load_string($response);
        if($xml) {
	    $PresenceAuthRequest = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/PresenceAuth-Request');
	    if($PresenceAuthRequest) {
    		$data = $sms20->RequestAuthorization($xml);

                $user = $data[0];
                $transaction = $data[1];
                if (($user != '') && ($transaction != '')){
                    print "<br>\r\n<br>\r\nAuthorizing...<br>\r\n";
                    // ToDo: To present/display the option to authorize or not the user
                    $sms20->AuthorizeContact($session, $transId, $user, $transaction);
                    $transId = $transId + 2;
                }
            }

            $PresenceNotificationRequest = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/PresenceNotification-Request');
            if($PresenceNotificationRequest) {
            	$list = $sms20->ListContacts($xml, $list);
                print_r($list);
	    }

	    $NewMessage = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/NewMessage/ContentData');  
	if ($NewMessage) {
	    	$data = $sms20->ReceivedMessage($xml);
	    	$sender = $data["sender"];
			$message1 = utf8_decode($data["message"]);
		
	    	print $sender." -> ".$message1."<br>\r\n";

	    	$message = utf8_encode($message1." to you also");
	    	$sms20->SendMessage($session,$transId,$login,$sender[0],$message);
	    	$transId = $transId +1;
	    }
        }
	sleep(3);
    }

    $sms20->DeleteContact($session, $transId, $log, $contact);
    $transId = $transId + 5;

    $sms20->Disconnect($session,$transId);
?>
