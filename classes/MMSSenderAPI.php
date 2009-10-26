<?php

class MMSSender

{
	var $login;
	var $user;
	var $cookie;
	var $server;

	# Logs into the MMS service
	function Login($loginini, $pwd)
	{
		echo "\n\n\nLogging in...<br>";
   		$this->server = "multimedia.movistar.es";
    	
		$ch = curl_init();
		$url = "http://$this->server/";
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

		$useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

		// Request multimedia.movistar.es
		$res= curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_GET, true);
		// HTTP headers
		$header = array("Accept-Encoding: identity",
	    							"Connection: Keep-Alive");
		curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
		$result = curl_exec ($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   	if ($code == 302)
   	{
	    // You are redirected and receive a cookie JSESSIONID
     	list($header, $result) = explode("\n\n", $result, 2);

	   	$matches = array();
			preg_match('/JSESSIONID=(.*?)\n/', $header, $matches);
	   	list($sessionCookie, $rest) = explode("; ", $matches[1], 2);
	   	echo "\n\n\n - SessionCookie: ".$sessionCookie."<br>";
			$this->user = $sessionCookie;
	   	$this->login = $loginini;
	    
	    $url = "http://$this->server/do/dologin;jsessionid=$sessionCookie";
	    echo $url;
		$res= curl_setopt ($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_GET, false);
	    $postdata = "TM_ACTION=LOGIN&variant=mensajeria&locale=sp-SP&client=html-msie-7-winxp&directMessageView=&uid=&uidl=&folder=&remoteAccountUID=&login=1&TM_LOGIN=$this->login&TM_PASSWORD=$pwd";
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	    # cabeceras HTTP
	    $header = array("Content-type: application/x-www-form-urlencoded",
	    		    "Content-Length: ".strlen($postdata),
	    		    "Accept-Encoding: identity",
	    		    "Cookie: JSESSIONID=".$sessionCookie,
	    		    "Connection: Keep-Alive");
	    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
	    $result = curl_exec ($ch);
	    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     	if ($code == 302)
     	{
				// You are redirected and receive 2 more cookies ("activeLogin" and "skf")
	     	list($header, $result) = explode("\n\n", $result, 2);

				$matches = array();
				preg_match('/Set-Cookie:skf=(.*?)\n/', $header, $matches);
				$matches1 = explode(";", $matches[1]);		
				
			$skfCookie = $matches1[0];
				
	    	echo "\n\n\n - skfCookie: ".$skfCookie."<br>";

				$matches = array();
				preg_match('/activeLogin=(.*?)\n/', $header, $matches);
				$matches1 = explode(";", $matches[1]);
				
			$loginCookie = $matches1[0];
				
	    	echo "\n\n\n - loginCookie: ".$loginCookie."<br>";
	    	
	    	$this->cookie = "JSESSIONID=".$sessionCookie."; skf=".$skfCookie."; activeLogin=".$loginCookie;
	    	echo "\n\n\nCookie: ".$this->cookie."<br>";
	

				// Request Create URL
	    	$url = "http://$this->server/do/multimedia/create?l=sp-SP&v=mensajeria";
				$res= curl_setopt ($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_GET, true);
				// HTTP headers
				$header = array("Accept-Encoding: identity",
												"Cookie: ".$this->cookie,
			    							"Connection: Keep-Alive");
				curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
				$result = curl_exec ($ch);
    	}
		}
		curl_close($ch);
		return $this->user;
	}

	// Inserts an image in the MMS message
 	function InsertImage($objPath)
 	{
  	$contentTypes["gif"] = "image/gif";
   	$contentTypes["jpg"] = "image/pjpeg";
   	$contentTypes["jpeg"] = "image/pjpeg";
   	$contentTypes["png"] = "image/x-png";
   	$contentTypes["bmp"] = "image/bmp";
        
   	$path = split("\.", $objPath);
   	$count = count($path);
   	$extension = $path[$count-1];
   	$contentType = $contentTypes[$extension];

		echo "\n\n\nInserting image...<br>";
        
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

		$useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

 		$separator = "---------------------------7d811c60180";

   	// generating object data
   	$filePart = "--$separator\r\nContent-Disposition: form-data; name=\"file\"; filename=\"$objPath\"\r\nContent-Type: $contentType\r\n\r\n";
   	$final = "\r\n--$separator--\r\n";
            
  	$contents = file_get_contents($objPath);

   	$data = $filePart.$contents.$final;

	 	$url = "http://$this->server/do/multimedia/uploadEnd ";
	 	$res= curl_setopt ($ch, CURLOPT_URL,$url);
	 	curl_setopt($ch, CURLOPT_POST, true);
	 	curl_setopt($ch, CURLOPT_GET, false);
	 	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
   	$referer = "http://$this->server/do/multimedia/upload?l=sp-SP&v=mensajeria";
	  // HTTP headers
   	$contentType2 = "multipart/form-data; boundary=$separator";
	 	$header = array("Content-Type: $contentType2",
	    		    			"Accept-Language: es",
	    		    			"Accept: */*",
			    					"Accept-Encoding: gzip, deflate",
			    					"Cookie: $this->cookie",
			    					"Cache-Control: no-cache",
			    					"Referer: $referer",
	    		    			"Connection: Keep-Alive");
	 	curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
	 	$result = curl_exec ($ch);
	 	
   	curl_close($ch);
  }

 	// Inserts an audio file in an MMS message
 	function InsertAudio($objPath)
 	{
   	$contentTypes["mid"] = "audio/mid";
   	$contentTypes["wav"] = "audio/wav";
   	$contentTypes["mp3"] = "audio/mpeg";
        
	$path = split("\.", $objPath);
   	$count = count($path);
   	$extension = $path[$count-1];
   	$contentType = $contentTypes[$extension];

		echo "\n\n\nInserting audio...<br>";
        
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

		$useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

 		$separator = "---------------------------7d811c60180";

   	// generating object data
   	$filePart = "--$separator\r\nContent-Disposition: form-data; name=\"file\"; filename=\"$objPath\"\r\nContent-Type: $contentType\r\n\r\n";
   	$final = "\r\n--$separator--\r\n";
            
  	$contents = file_get_contents($objPath);

   	$data = $filePart.$contents.$final;

	 	$url = "http://$this->server/do/multimedia/uploadEnd ";
	 	$res= curl_setopt ($ch, CURLOPT_URL,$url);
	 	curl_setopt($ch, CURLOPT_POST, true);
	 	curl_setopt($ch, CURLOPT_GET, false);
	 	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
   	$referer = "http://$this->server/do/multimedia/upload?l=sp-SP&v=mensajeria";
	  // HTTP headers
   	$contentType2 = "multipart/form-data; boundary=$separator";
	 	$header = array("Content-Type: $contentType2",
	    		    			"Accept-Language: es",
	    		    			"Accept: */*",
			    					"Accept-Encoding: gzip, deflate",
			    					"Cookie: $this->cookie",
			    					"Cache-Control: no-cache",
			    					"Referer: $referer",
	    		    			"Connection: Keep-Alive");
	 	curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
	 	$result = curl_exec ($ch);
	 	
   	curl_close($ch);
	}

 	// Inserts a video file into an MMS message
 	function InsertVideo($objPath)
 	{
   	$contentTypes["avi"] = "video/avi";
   	$contentTypes["asf"] = "video/x-ms-asf";
   	$contentTypes["mpg"] = "video/mpeg";
   	$contentTypes["mpeg"] = "video/mpeg";
   	$contentTypes["wmv"] = "video/x-ms-wmv";
        
	$path = split("\.", $objPath);
   	$count = count($path);
   	$extension = $path[$count-1];
   	$contentType = $contentTypes[$extension];

		echo "\n\n\nInserting video...<br>";
        
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

		$useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

 		$separator = "---------------------------7d811c60180";

   	// generating object data
   	$filePart = "--$separator\r\nContent-Disposition: form-data; name=\"file\"; filename=\"$objPath\"\r\nContent-Type: $contentType\r\n\r\n";
   	$final = "\r\n--$separator--\r\n";
            
  	$contents = file_get_contents($objPath);

   	$data = $filePart.$contents.$final;

	 	$url = "http://$this->server/do/multimedia/uploadEnd ";
	 	$res= curl_setopt ($ch, CURLOPT_URL,$url);
	 	curl_setopt($ch, CURLOPT_POST, true);
	 	curl_setopt($ch, CURLOPT_GET, false);
	 	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
   	$referer = "http://$this->server/do/multimedia/upload?l=sp-SP&v=mensajeria";
	  // HTTP headers
   	$contentType2 = "multipart/form-data; boundary=$separator";
	 	$header = array("Content-Type: $contentType2",
	    		    			"Accept-Language: es",
	    		    			"Accept: */*",
			    					"Accept-Encoding: gzip, deflate",
			    					"Cookie: $this->cookie",
			    					"Cache-Control: no-cache",
			    					"Referer: $referer",
	    		    			"Connection: Keep-Alive");
	 	curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
	 	$result = curl_exec ($ch);
	 	
   	curl_close($ch);
	}

 	// Send MMS message
 	function SendMessage($subject, $dest, $msg)
 	{
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

		$useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

   	echo "\n\n\nSending message...<br>";

 	$separator = "---------------------------7d811c60180";

   	// generating object data
   	$basefolderPart = "--$separator\r\nContent-Disposition: form-data; name=\"basefolder\"\r\n\r\n\r\n";
   	$folderPart = "--$separator\r\nContent-Disposition: form-data; name=\"folder\"\r\n\r\n\r\n";
   	$idPart = "--$separator\r\nContent-Disposition: form-data; name=\"id\"\r\n\r\n\r\n";
   	$publicPart = "--$separator\r\nContent-Disposition: form-data; name=\"public\"\r\n\r\n\r\n";
   	$namePart = "--$separator\r\nContent-Disposition: form-data; name=\"name\"\r\n\r\n\r\n";
   	$ownerPart = "--$separator\r\nContent-Disposition: form-data; name=\"owner\"\r\n\r\n\r\n";
   	$deferreddatePart = "--$separator\r\nContent-Disposition: form-data; name=\"deferredDate\"\r\n\r\n\r\n";
   	$requestreturnreceiptPart = "--$separator\r\nContent-Disposition: form-data; name=\"requestReturnReceipt\"\r\n\r\n\r\n";

   	$toPart = "--$separator\r\nContent-Disposition: form-data; name=\"to\"\r\n\r\n$dest\r\n";
   	$subjectPart = "--$separator\r\nContent-Disposition: form-data; name=\"subject\"\r\n\r\n$subject\r\n";
   	$textPart = "--$separator\r\nContent-Disposition: form-data; name=\"text\"\r\n\r\n$msg\r\n";

   	$final = "\r\n--$separator--\r\n";
            
   	$data = $basefolderPart.$folderPart.$idPart.$publicPart.$namePart.$ownerPart.$deferreddatePart.$requestreturnreceiptPart.$toPart.$subjectPart.$textPart.$final;
   	
		$url = "http://$this->server/do/multimedia/send?l=sp-SP&v=mensajeria";
		$res= curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_GET, false);
		$contentType = "multipart/form-data; boundary=$separator";
		$referer = "http://$this->server/do/multimedia/show";
		// HTTP headers
		$header = array("Content-Type: $contentType",
	   								"Referer: $referer",
										"Accept-Language: es",
										"Accept-Encoding: gzip, deflate",
										"Cache-Control: no-cache",
	   								"Content-Length: ".strlen($data),
	    							"Accept: */*",
	    							"Cookie: $this->cookie",
	    							"Connection: Keep-Alive");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
		$result = curl_exec ($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

   	if(ereg("Tu mensaje ha sido enviado",$result))
   	{
   		echo "\n\n\n - Message sent<br>";
   	}
   	else
   	{
   		echo "\n\n\n - Message not sent<br>";
   	}
		curl_close($ch);	
 	}

 	// Logs out from the service
 	function Logout()
 	{
		echo "\n\n\nLogging out...<br>";
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

		$useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	
		$url = "http://$this->server/do/logout?l=sp-SP&v=mensajeria";
		$res= curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_GET, false);
		$postdata = "TM_ACTION=LOGOUT";
		$referer = "http://$this->server/do/messages/inbox";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		$header = array("Content-type: application/x-www-form-urlencoded",
	   								"Referer: ".$referer,
	   								"Content-Length: ".strlen($postdata),
	    							"Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
	    							"Connection: Keep-Alive");
		curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
		$result = curl_exec ($ch);

		curl_close($ch);	
 	}
}
?>
