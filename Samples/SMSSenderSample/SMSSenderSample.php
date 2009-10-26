<?php
include 'SMSSenderAPI.php';

$SMS = New SMSSender;
$SMS->SendMessage('666777888', 'passwd', '666777999', 'Testing SMS');
?>