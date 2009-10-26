<?php
class SMS20
{
    var $sessionID;
    var $bSessionID;
    var $miAlias;

    // It makes login to the Web of movistar
    // Input: login=string with number of telephone,
    //		passw=string with password of access to the Web
    // Return: Necessary identifier of session for all the later operations
    function Login($log, $passw)
    {
    	print "Login<br>\r\n";
        $this->sessionID = "";
        $this->bSessionID = 0;
        $this->miAlias = "";
    
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        // We initiated login with HTTP
        $url = "http://impw.movistar.es/tmelogin/tmelogin.jsp";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        $postdata = "TM_ACTION=AUTHENTICATE&TM_LOGIN=$log&TM_PASSWORD=$passw&SessionCookie=ColibriaIMPS_367918656&ClientID=WV:InstantMessenger-1.0.2309.16485@COLIBRIA.PC-CLIENT";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_POST, true);
        // HTTP headers
        $header = array("Content-Type: application/x-www-form-urlencoded");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);
	curl_close($ch);
	
        list($header, $string) = explode("\r\n\r\n", $result, 2);
        $string=str_replace("xmlns=","a=",$string);

	$xml = simplexml_load_string($string);
	
	$sessions = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/Login-Response/SessionID');
	while(list( , $node) = each($sessions)) {
	    $this->sessionID = $node;
	}

	print "SessionID = ".$this->sessionID."<br />\r\n";
	return $this->sessionID;
    }

    // It makes the connection to service SMS2.0
    // Input: log=string with number of telephone,
    //		nickname=string with nickname that we want to use (only is necessary the first time)
    //		session=string with the session identifier
    // Return: The list of contacts associated to the telephone number
    function Connect($log, $nickname, $session)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $url = "http://sms20.movistar.es/";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        // HTTP headers
        $header = array("Content-Type: application/vnd.wv.csp.xml",
    		        "Expect: 100-continue");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);

        // We initiated access to sms20.movistar.es
        // We sent <ClientCapability-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>1</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><ClientCapability-Request><ClientID><URL>WV:InstantMessenger-1.0.2309.16485@COLIBRIA.PC-CLIENT</URL></ClientID><CapabilityList><ClientType>COMPUTER</ClientType><InitialDeliveryMethod>P</InitialDeliveryMethod><AcceptedContentType>text/plain</AcceptedContentType><AcceptedContentType>text/html</AcceptedContentType><AcceptedContentType>image/png</AcceptedContentType><AcceptedContentType>image/jpeg</AcceptedContentType><AcceptedContentType>image/gif</AcceptedContentType><AcceptedContentType>audio/x-wav</AcceptedContentType><AcceptedContentType>image/jpg</AcceptedContentType><AcceptedTransferEncoding>BASE64</AcceptedTransferEncoding><AcceptedContentLength>256000</AcceptedContentLength><MultiTrans>1</MultiTrans><ParserSize>300000</ParserSize><SupportedCIRMethod>STCP</SupportedCIRMethod><ColibriaExtensions>T</ColibriaExtensions></CapabilityList></ClientCapability-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <Service-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>2</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><Service-Request><ClientID><URL>WV:InstantMessenger-1.0.2309.16485@COLIBRIA.PC-CLIENT</URL></ClientID><Functions><WVCSPFeat><FundamentalFeat /><PresenceFeat /><IMFeat /><GroupFeat /></WVCSPFeat></Functions><AllFunctionsRequest>T</AllFunctionsRequest></Service-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <UpdatePresence-Request> para avisar de que estamos conectados con un messenger
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>3</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><UpdatePresence-Request><PresenceSubList xmlns=\"http://www.openmobilealliance.org/DTD/WV-PA1.2\"><OnlineStatus><Qualifier>T</Qualifier></OnlineStatus><ClientInfo><Qualifier>T</Qualifier><ClientType>COMPUTER</ClientType><ClientTypeDetail xmlns=\"http://imps.colibria.com/PA-ext-1.2\">PC</ClientTypeDetail><ClientProducer>Colibria As</ClientProducer><Model>TELEFONICA Messenger</Model><ClientVersion>1.0.2309.16485</ClientVersion></ClientInfo><CommCap><Qualifier>T</Qualifier><CommC><Cap>IM</Cap><Status>OPEN</Status></CommC></CommCap><UserAvailability><Qualifier>T</Qualifier><PresenceValue>AVAILABLE</PresenceValue></UserAvailability></PresenceSubList></UpdatePresence-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <GetList-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>4</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><GetList-Request /></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <GetPresence-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>5</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><GetPresence-Request><User><UserID>wv:$log@movistar.es</UserID></User></GetPresence-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);
        
        list($header1, $header2, $string) = explode("\r\n\r\n", $result, 3);
        $string=str_replace("xmlns=","a=",$string);
	$xml = simplexml_load_string($string);
	$presences = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/GetPresence-Response/Presence');
        while(list( , $presence) = each($presences)) {
            if($presence->UserID == "wv:$log@movistar.es") {
	        $this->miAlias = $presence->PresenceSubList->Alias->PresenceValue;
	    }
	}
	print "NickName = ".$this->miAlias."<br />\r\n";

        // We sent <ListManage-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>6</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><ListManage-Request><ContactList>wv:$log/~pep1.0_privatelist@movistar.es</ContactList><ReceiveList>T</ReceiveList></ListManage-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        list($header1, $header2, $string) = explode("\r\n\r\n", $result, 3);
        $string=str_replace("xmlns=","a=",$string);
	$xml = simplexml_load_string($string);
	$contacts = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/ListManage-Response/NickList/NickName');
        $lista = array();
        while(list( , $contact) = each($contacts)) {
            $lista["$contact->UserID"] = $contact->Name;
	}

        // We sent <CreateList-Request>
        $listadenicks = "";
        if($lista) {
        	$listadenicks.="<NickList>";
                while(list($keys,$values) = each($lista)) {
                    $listadenicks.="<NickName><Name>$values</Name><UserID>$keys</UserID></NickName>";
		}
        	$listadenicks.="</NickList>";
        }
        print $listadenicks."<br>\r\n";
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>7</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><CreateList-Request><ContactList>wv:$log/~PEP1.0_subscriptions@movistar.es</ContactList>$listadenicks</CreateList-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <SubscribePresence-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>8</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><SubscribePresence-Request><ContactList>wv:$log/~PEP1.0_subscriptions@movistar.es</ContactList><PresenceSubList xmlns=\"http://www.openmobilealliance.org/DTD/WV-PA1.2\"><OnlineStatus /><ClientInfo /><FreeTextLocation /><CommCap /><UserAvailability /><StatusText /><StatusMood /><Alias /><StatusContent /><ContactInfo /></PresenceSubList><AutoSubscribe>T</AutoSubscribe></SubscribePresence-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <UpdatePresence-Request> in order to send ours nick
        // Only the first time and when we want to change nick
        if ($nickname != '') {
            $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>9</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><UpdatePresence-Request><PresenceSubList xmlns=\"http://www.openmobilealliance.org/DTD/WV-PA1.2\"><Alias><Qualifier>T</Qualifier><PresenceValue>$nickname</PresenceValue></Alias></PresenceSubList></UpdatePresence-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            $result = curl_exec ($ch);

            $this->miAlias = $nickname;
        }

        curl_close($ch);

        return $lista;
    }

    // It makes the sounding in search of notifications of messages, contacts that are connected, etc...
    // Input: session=string with the session identifier
    // Return: The text of the answer to look for the different types from answer
    function Polling($session)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $url = "http://sms20.movistar.es/";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        // HTTP headers
        $header = array("Content-Type: application/vnd.wv.csp.xml",
    		        "Expect: 100-continue");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);

        // We sent <Polling-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID /></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><Polling-Request /></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        curl_close($ch);
        
        list($header1, $header2, $string) = explode("\r\n\r\n", $result, 3);
        $string=str_replace("xmlns=","a=",$string);
        return $string;
    }

    // It adds a contact to the list of the user
    // Input: session=string with the session identifier
    //		log=string with number of telephone
    //		contact=string with the telephone number of the contact that we want to add
    //		transId=transaction identifier (the numerical sequence must be managed from the application)
    // Return: The list of present contacts (only with the state of the added number)
    function AddContact($session, $log, $contact, $transId)
    {
        $lista = array();

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $url = "http://sms20.movistar.es/";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        // HTTP headers
        $header = array("Content-Type: application/vnd.wv.csp.xml",
    		        "Expect: 100-continue");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);

        // We sent <Search-Request> (in theory to obtain userId)
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><Search-Request><SearchPairList><SearchElement>USER_MOBILE_NUMBER</SearchElement><SearchString>$contact</SearchString></SearchPairList><SearchLimit>50</SearchLimit></Search-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <GetPresence-Request> in order to obtain the state of the direct contact
        //	(without this it would be necessary to hope to that the contact changes of state)
        $transId1 = $transId+1;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId1</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><GetPresence-Request><User><UserID>wv:$contact@movistar.es</UserID></User><PresenceSubList xmlns=\"http://www.openmobilealliance.org/DTD/WV-PA1.2\"><OnlineStatus /><ClientInfo /><GeoLocation /><FreeTextLocation /><CommCap /><UserAvailability /><StatusText /><StatusMood /><Alias /><StatusContent /><ContactInfo /></PresenceSubList></GetPresence-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        list($header1, $header2, $string) = explode("\r\n\r\n", $result, 3);
        $string=str_replace("xmlns=","a=",$string);
	$xml = simplexml_load_string($string);
	$presences = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/GetPresence-Response/Presence');
        while(list( , $presence) = each($presences)) {
            if($presence->UserID != "wv:$log@movistar.es") {
            	$par = array($presence->PresenceSubList->Alias->PresenceValue, $presence->PresenceSubList->UserAvailability->PresenceValue);
                $list["$presence->UserID"] = $par;
	    }
	}

        $nickname=$list["wv:".$contact."@movistar.es"][0];
        print $nickname."<br>\r\n";

        // We sent <ListManage-Request>
        $transId2 = $transId+2;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId2</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><ListManage-Request><ContactList>wv:$log/~PEP1.0_subscriptions@movistar.es</ContactList><AddNickList><NickName><Name>$nickname</Name><UserID>wv:$contact@movistar.es</UserID></NickName></AddNickList><ReceiveList>T</ReceiveList></ListManage-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <ListManage-Request> this time for the PrivateList
        $transId3 = $transId+3;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId3</TransactionID></TransactionDescriptor><TransactionContent xmlns=\http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><ListManage-Request><ContactList>wv:$log/~PEP1.0_privatelist@movistar.es</ContactList><AddNickList><NickName><Name>$nickname</Name><UserID>wv:$contact@movistar.es</UserID></NickName></AddNickList><ReceiveList>T</ReceiveList></ListManage-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        curl_close($ch);

        return $list;
    }

    // It authorizes to a contact to know our state presence
    // Input: session=string with the session identifier
    //		transId=transaction identifier (the numerical sequence must be managed from the application)
    //		user=user identifier to whom we authorized (wv:6xxxxxxxx@movistar.es)
    //		transaction=identifier of transaction received in the request of the authorization
    // Return: without return
    function AuthorizeContact($session, $transId, $user, $transaction)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $url = "http://sms20.movistar.es/";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        // cabeceras HTTP
        $header = array("Content-Type: application/vnd.wv.csp.xml",
    		        "Expect: 100-continue");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);

        // We sent <GetPresence-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><GetPresence-Request><User><UserID>$user</UserID></User><PresenceSubList xmlns=\"http://www.openmobilealliance.org/DTD/WV-PA1.2\"><OnlineStatus /><ClientInfo /><GeoLocation /><FreeTextLocation /><CommCap /><UserAvailability /><StatusText /><StatusMood /><Alias /><StatusContent /><ContactInfo /></PresenceSubList></GetPresence-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <Status> para hacer el ack de la petición
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Response</TransactionMode><TransactionID>$transaction</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><Status><Result><Code>200</Code></Result></Status></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <PresenceAuth-User>
        $transId1 = $transId+1;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId1</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><PresenceAuth-User><UserID>$user</UserID><Acceptance>T</Acceptance></PresenceAuth-User></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        curl_close($ch);
    }

    // It eliminate authorization to a contact to know our state presence
    // Input: session=string with the session identifier
    //		transId=transaction identifier (the numerical sequence must be managed from the application)
    //		log=string with number of telephone of the user
    //		contact=identifier of contact to erase (wv:6xxxxxxxx@movistar.es)
    // Retorna: without return
    function DeleteContact($session, $transId, $log, $contact)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $url = "http://sms20.movistar.es/";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        // HTTP headers
        $header = array("Content-Type: application/vnd.wv.csp.xml",
    		        "Expect: 100-continue");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        
        // We sent <ListManage-Request> in order to eliminate contact of the list of subscriptions
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><ListManage-Request><ContactList>wv:$log/~PEP1.0_subscriptions@movistar.es</ContactList><RemoveNickList><UserID>$contacto</UserID></RemoveNickList><ReceiveList>T</ReceiveList></ListManage-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <ListManage-Request> in order to eliminate contact of the private list
        $transId1 = $transId+1;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId1</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><ListManage-Request><ContactList>wv:$log/~PEP1.0_privatelist@movistar.es</ContactList><RemoveNickList><UserID>$contact</UserID></RemoveNickList><ReceiveList>T</ReceiveList></ListManage-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <UnsubscribePresence-Request>
        $transId2 = $transId+2;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId2</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><UnsubscribePresence-Request><User><UserID>$contact</UserID></User></UnsubscribePresence-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <DeleteAttributeList-Request>
        $transId3 = $transId+3;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId3</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><DeleteAttributeList-Request><UserID>$contact</UserID><DefaultList>F</DefaultList></DeleteAttributeList-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        // We sent <CancelAuth-Request>
        $transId4 = $transId+4;
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId4</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><CancelAuth-Request><UserID>$contact</UserID></CancelAuth-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        curl_close($ch);
    }

    // It sends a message to the number identified by adressee
    // Input: session=string with the session identifier
    //		transId=transaction identifier (the numerical sequence must be managed from the application)
    //		log=string with number of telephone of the user
    //		dest=string with the identifier of the adressee of the message (wv:6xxxxxxxx@movistar.es
    //		message=text of the message that we want to send
    // Return: without return
    function SendMessage($session,$transId,$log,$dest,$message)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $url = "http://sms20.movistar.es/";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        // HTTP headers
        $header = array("Content-Type: application/vnd.wv.csp.xml",
    		        "Expect: 100-continue");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);

        // We sent <SendMessage-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><SendMessage-Request><DeliveryReport>F</DeliveryReport><MessageInfo><ContentType>text/html</ContentType><ContentSize>148</ContentSize><Recipient><User><UserID>$dest</UserID></User></Recipient><Sender><User><UserID>$log@movistar.es</UserID></User></Sender></MessageInfo><ContentData>&lt;span style=\"color:#000000;font-family:'Microsoft Sans Serif';font-style:normal;font-weight:normal;font-size:12px;\"&gt;$message&lt;/span&gt;</ContentData></SendMessage-Request></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        curl_close($ch);
    }

    // It sends a message to the number identified by adressee
    // Input: session=string with the session identifier
    //		transId=transaction identifier (the numerical sequence must be managed from the application)
    // Return: without return
    function Disconnect($session,$transId)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $url = "http://sms20.movistar.es/";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        // HTTP headers
        $header = array("Content-Type: application/vnd.wv.csp.xml",
    		        "Expect: 100-continue");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        
        // We sent <Logout-Request>
        $postdata = "<?xml version=\"1.0\" encoding=\"utf-8\"?><WV-CSP-Message xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"http://www.openmobilealliance.org/DTD/WV-CSP1.2\"><Session><SessionDescriptor><SessionType>Inband</SessionType><SessionID>$session</SessionID></SessionDescriptor><Transaction><TransactionDescriptor><TransactionMode>Request</TransactionMode><TransactionID>$transId</TransactionID></TransactionDescriptor><TransactionContent xmlns=\"http://www.openmobilealliance.org/DTD/WV-TRC1.2\"><Logout-Request /></TransactionContent></Transaction></Session></WV-CSP-Message>";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec ($ch);

        curl_close($ch);
    }
    
    function RequestAuthorization($xml)
    {
    	$datos = array();
    	
        // In order to respond to the authorization request to detect presence
        $userIds = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/PresenceAuth-Request/UserID');
        $usuario = $userIds[0];
        $datos[0] = $usuario;

        $transactionIds = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionDescriptor/TransactionID');
        $transaction = $transactionIds[0];

        $datos[1] = $transaction;
        
        return $datos;
    }
    
    function ListContacts($xml, $list)
    {
	$presences = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/PresenceNotification-Request/Presence');
        while(list( , $presence) = each($presences)) {
            $par = array();
            if ($list["$presence->UserID"] == 0)
                $list["$presence->UserID"] = $par;
            if ($presence->PresenceSubList->Alias->PresenceValue != "")
                $list["$presence->UserID"][0] = $presence->PresenceSubList->Alias->PresenceValue;
            if ($presence->PresenceSubList->UserAvailability->PresenceValue != "")
                $list["$presence->UserID"][1] = $presence->PresenceSubList->UserAvailability->PresenceValue;
	}
	
	return $list;
    }
    
    function ReceivedMessage($xml)
    {
    	$datos = array();

	$NewMessage = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/NewMessage/ContentData');
	$sender = $xml->xpath('/WV-CSP-Message/Session/Transaction/TransactionContent/NewMessage/MessageInfo/Sender/User/UserID');
	list( $ini1, $ini2, $message) = split( '[<>]', $NewMessage[0] );
	$message1 = utf8_decode($message);
	
	$data["sender"] = $sender[0];
	$data["message"] = $message1;
	
	return $data;
    }
}
?>
