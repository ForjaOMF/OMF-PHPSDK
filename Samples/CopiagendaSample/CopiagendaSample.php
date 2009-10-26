<?php
	include 'CopiagendaAPI.php';

	$copiagenda = New Copiagenda; // Creates the copiagenda object

	$contacts = $copiagenda->RetrieveContacts("666777888", "passwd"); // Receive the contacts list

	print_r($contacts); // Print the array
?>
