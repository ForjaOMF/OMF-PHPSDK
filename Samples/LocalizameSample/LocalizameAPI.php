<?php
class Localizame
{
    var $cookie;

    // It makes login to the Web of copiagenda
    // Input: login=string with number of telephone,
    //		passw=string with password of access to the Web
    // Return: Necessary identifier of session for all the later operations
    function Login($login, $pwd)
    {
    	print "Login<br>\r\n";
    	$this->cookie = "";
    
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        // Iniciamos login con HTTP
        $url = "http://www.localizame.movistar.es/login.do";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        $postdata = "usuario=$login&clave=$pwd&submit.x=36&submit.y=6";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_POST, true);
        // cabeceras HTTP
        $header = array("Content-Type: application/x-www-form-urlencoded",
    		        "Content-Length: ".strlen($postdata),
    		        "Accept-Encoding: identity",
    		        "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
    		        "Connection: Keep-Alive");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        list($header, $result) = explode("\n\n", $result, 2);
            
	$matches = array();
	preg_match('/Set-Cookie: JSESSIONID=(.*?)\n/', $header, $matches);
	list($cookie_value, $rest) = explode("; ", $matches[1], 2);
	$this->cookie = $cookie_value;
	print "Cookie: ".$this->cookie."<br>";
	    	    
        // Access to this page is needed.
        $url = "http://www.localizame.movistar.es/nuevousuario.do";
        $referer = "http://www.localizame.movistar.es/login.do";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, false);
        // HTTP headers
        $header = array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
    		        "Accept-Encoding: identity",
    		        "Referer: ".$referer,
    		        "Cookie: JSESSIONID=$this->cookie",
	    		"Connection: Keep-Alive");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);
	curl_close($ch);
    }

    // This gives back localizacion of a person by its number of telephone
    // Input: msisdn=string with number of telephone
    // Return: The location of the person
    function Locate($msisdn)
    {	
		print "Locating...<br>\r\n";
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        // We initiated the search
        $url = "http://www.localizame.movistar.es/buscar.do";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        $postdata = "telefono=$msisdn";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_CONNECTIONTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);

        // HTTP headers
        $header = array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
    		        "Accept-Encoding: identity",
    		        "Cookie: JSESSIONID=$this->cookie",
	    		"Connection: Keep-Alive");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);
        
	$parts = explode($msisdn,$result);
	
	$parts2 = explode("metros.",$parts[1]);
	$localization = $msisdn;
	$localization .= $parts2[0];
	curl_close($ch);
	
	return $localization;
    }

    // This authorizes a person to that it can locate to you
    // Input: msisdn=string with number of telephone
    // Return: without return
    function Authorize($msisdn)
    {
    	print "Authorizing...<br>\r\n";
    	
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        // We initiated authorization
        $url = "http://www.localizame.movistar.es/insertalocalizador.do?telefono=$msisdn&submit.x=40&submit.y=5";
        $referer = "http://www.localizame.movistar.es/buscalocalizadorespermisos.do";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, false);

        // HTTP headers
        $header = array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
    		        "Accept-Encoding: identity",
    		        "Referer: ".$referer,
    		        "Cookie: JSESSIONID=$this->cookie",
	    		"Connection: Keep-Alive");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);
	curl_close($ch);
    }

    // This eliminates the authorization a person for locate to you
    // Input: msisdn=string with number of telephone
    // Return: without return
    function Unauthorize($msisdn)
    {
    	print "Deny authorization...<br>\r\n";
    	
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        // We initiated to deny authorization
        $url = "http://www.localizame.movistar.es/borralocalizador.do?telefono=$msisdn&submit.x=44&submit.y=8";
        $referer = "http://www.localizame.movistar.es/login.do";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, false);
        // HTTP headers
        $header = array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
    		        "Accept-Encoding: identity",
    		        "Referer: ".$referer,
    		        "Cookie: JSESSIONID=$this->cookie",
	    		"Connection: Keep-Alive");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);
	curl_close($ch);
    }

    // This does logout of the Web of copiagenda
    // Return: without return
    function Logout()
    {
    	print "Logout<br>\r\n";
    	
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        // We initiated logout
        $url = "http://www.localizame.movistar.es/logout.do";
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, false);
        // HTTP headers
        $header = array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
    		        "Accept-Encoding: identity",
    		        "Cookie: JSESSIONID=$this->cookie",
	    		"Connection: Keep-Alive");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);
	curl_close($ch);
    	$this->cookie = "";
    }
}
?>
