<?php
class Copiagenda
{	
	// It obtains the list of contacts in the Web of copiagenda
    // Input: login=string with number of telephone,
    //		pwd=string with password of access to the Web
    // Return: An Array with the list of contacts
	function RetrieveContacts($login, $pwd)
	{
	    $contact_list = "";

	    $ch = curl_init();
	    $url = "https://copiagenda.movistar.es/cp/ps/Main/login/Agenda";
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt ($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

	    $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0; .NET CLR 2.0.50727)";
	    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

	    # We initiated login with HTTPS
	    $res= curl_setopt ($ch, CURLOPT_URL,$url);
	    $postdata = "TM_ACTION=LOGIN&TM_LOGIN=$login&TM_PASSWORD=$pwd";
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	    curl_setopt($ch, CURLOPT_POST, true);
	    # HTTP headers
	    $header = array("Content-Type: application/x-www-form-urlencoded",
	    		    "Content-Length: ".strlen($postdata),
	    		    "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
	    		    "Connection: Keep-Alive");
	    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
	    $result = curl_exec ($ch);
	    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	    if (curl_errno($ch))
	    {
	        print "Error: " . curl_error($ch);
	    }
	    else
	    {
		if ($code == 301 || $code == 302)
		{
		    # They redirect and give us a cookie
	            list($header, $result) = explode("\n\n", $result, 2);

		    $matches = array();
		    preg_match('/Location:(.*?)\n/', $header, $matches);
		    $url = @parse_url(trim(array_pop($matches)));

		    $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');

		    $matches2 = array();
		    preg_match('/Set-Cookie: s=(.*?)\n/', $header, $matches2);
	    	    list($cookie_value2, $rest) = explode("; ", $matches2[1], 2);

		    $matches = array();
		    preg_match('/Set-Cookie:skf=(.*?)\n/', $header, $matches);
	    	    list($cookie_value1, $rest) = explode("; ", $matches[1], 2);

	            curl_setopt($ch, CURLOPT_URL, $new_url);
		    # HTTP headers
		    $header = array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
		    		    "Cookie: skf=$cookie_value; s=$cookie_value2",
		    		    "Connection: Keep-Alive");
		    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
		    curl_setopt($ch, CURLOPT_GET, true);
		    $result = curl_exec ($ch);

		    $matches = array();
		    preg_match('/password" value=(.*?)>\n/', $result, $matches);
		    $password = $matches[1];

		    # They request to us that re-authenticate with the user data the cookie and they give back to us a session token
	            curl_setopt($ch, CURLOPT_URL, "https://copiagenda.movistar.es/cp/ps/Main/login/Authenticate");
		    $postdata = "password=$password&u=$login&d=movistar.es";
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		    curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_GET, false);
		    # HTTP headers
		    $header = array("Content-Type: application/x-www-form-urlencoded",
		    		    "Content-Length: ".strlen($postdata),
		    		    "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
		    		    "Cookie: skf=$cookie_value1; s=$cookie_value2",
		    		    "Connection: Keep-Alive");
		    $result = curl_exec ($ch);
		    $matches = array();
		    preg_match('/&t=(.*?)"\n/', $result, $matches);
		    $token = $matches[1];

		    # We requested exported of the data in separated file txt by tabulators
		    $urlfinal = "https://copiagenda.movistar.es/cp/ps/PSPab/preferences/ExportContacts?d=movistar.es&c=yes&u=$login&t=$token";
	            curl_setopt($ch, CURLOPT_URL, $urlfinal);
		    $postdata = "fileFormat=TEXT&charset=8859_1&delimiter=TAB";
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		    curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_GET, false);
		    # HTTP headers
		    $header = array("Content-Type: application/x-www-form-urlencoded",
		    		    "Content-Length: ".strlen($postdata),
		    		    "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
		    		    "Cookie: skf=$cookie_value1; s=$cookie_value2",
		    		    "Connection: Keep-Alive");
		    # In this case it interests to us to eliminate the headers
		    curl_setopt ($ch, CURLOPT_HEADER, false);
		    $result = curl_exec ($ch);
		    $contact_list = $result;
		}
		else
		{
		    print "No redirigido";
		}

	        curl_close($ch);
	    }

	    $addressbook = split("\n", $contact_list);
	    return $addressbook;
	}
}
?>