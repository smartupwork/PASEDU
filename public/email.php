<?php
$to = 'To: Developer <rajneeshgautam24@gmail.com>';
//send_php_email($to, 'PAS CRON ERROR :: enrollmentUpdate:hook', 'TEST CRON EMAIL');

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

// Additional headers
//$headers[] = 'To: Mary <mary@example.com>, Kelly <kelly@example.com>';
$headers[] = $to;
$headers[] = 'From: WE Education DEV <info@partner-worldeducation.net>';
//$headers[] = 'Cc: birthdayarchive@example.com';
//$headers[] = 'Bcc: birthdaycheck@example.com';

if(mail($to, 'PAS CRON ERROR :: enrollmentUpdate:hook', 'TEST CRON EMAIL', implode("\r\n", $headers))){
    dump('Email Sent');
}else{
    dump('Email Failed');
}