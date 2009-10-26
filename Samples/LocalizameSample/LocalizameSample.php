<?php
include "LocalizameAPI.php";

set_time_limit(600);

$localizer = "666777888"; // MSISDN
$passw = "xxxxx"; // Password of access to Localízame. He is always the same one but it is necessary to request it every time for a maximum of half an hour

// Telephone number that we want to locate (we must be authorized)
$locatable = "666777999";
$passw2 = "xxxxx"; // Password of access to Localízame. He is always the same one but it is necessary to request it every time for a maximum of half an hour

// The locatable one authorizes to the localizer
$loc1 = new Localizame();
$loc1->Login($locatable, $passw2);
$loc1->Authorize($localizer);
$loc1->Logout();

// We initiated location
$loc2 = new Localizame();
$loc2->Login($localizer, $passw);
$location = $loc2->Locate($locatable);
print $location;
$loc2->Logout();

// The locatable one deprives of authority to the localizer
$loc3 = new Localizame();
$loc3->Login($locatable, $passw2);
$loc3->Unauthorize($localizer);
$loc3->Logout();

?>
