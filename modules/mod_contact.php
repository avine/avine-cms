<?php

// Congif
$user_id = 1;

// Display the contact details and link to the contact page
$contact = new comContact_();
echo $contact->preview($user_id, true);

?>