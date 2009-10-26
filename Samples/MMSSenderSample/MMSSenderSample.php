<?php

include 'MMSSenderAPI.php';

//set_time_limit(600);

$mms = new MMSSender();

$log = "666777888";	// user's telephone number
$passw = "passwd";	// user's password

$user = $mms->Login($log, $passw);
if($user == "")
{
    print "Login error";
}
else
{
    $imgPath = "c:\\sounds\\image.jpg";
    $mms->InsertImage($imgPath);

    //$sndPath="c:\\sounds\\tono.mid";
    //$mms->InsertAudio($sndPath);

    //$vidPath="c:\\videos\\vid.avi";
    //$mms->InsertVideo($pathVid);

    $dest = "666777888"; // destination of message
    $subject = "subject"; // subject of message
    $msg = "message text"; // text of message
    $mms->SendMessage($subject, $dest, $msg);

    $mms->Logout();
}
?>