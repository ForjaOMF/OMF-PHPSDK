<?php
class SMSSender
{
	// it sends sms through MensajeriaWeb
    	// Input: login=string with number of telephone,
    //		pwd=string with password of access to the Web
    //		dest=string with number to send sms (if it is more than one they can separate by ";")
    //		msg=string with the sms text
    // Return: The result of HTTP transaction
    function SendMessage($login, $pwd, $dest, $msg)
    {
        $ch = curl_init();
        $url = "https://opensms.movistar.es/aplicacionpost/loginEnvio.jsp";
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);

        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0; .NET CLR 2.0.50727)";
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        # We initiated SMS POST with HTTPS
        $res= curl_setopt ($ch, CURLOPT_URL,$url);
        $postdata = "TM_ACTION=AUTHENTICATE&TM_LOGIN=$login&TM_PASSWORD=$pwd&to=$dest&message=$msg";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_POST, true);
        # HTTP headers
         $header = array("Content-Type: application/x-www-form-urlencoded",
    		         "Content-Length: ".strlen($postdata),
    		         "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*",
    		         "Connection: Keep-Alive");
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec ($ch);

        return $result;
    }
}
?>
