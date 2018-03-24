<?php
if(!isset($_POST['host']) || $_POST['host']=="") {
	die("You did not specify any host.</br><a href=\"index.html\">Back</a>");
     }
if(!checkdnsrr(idn_to_ascii($_POST['host']), 'A')) {
	die("Host is not resolvable. Maybe you've misspelled it?</br><a href=\"index.html\">Back</a>");
     }

$host = $_POST['host'];

if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
echo "<a href=http://$host>http://$host</a> is <b>online</b> and reachable!";
fclose($socket);
} else {
echo "<a href=http://$host>http://$host</a> is <b>offline</b>.";
}

if($socket =@ fsockopen($host, 443, $errno, $errstr, 30)) {
echo "<br><a href=https://$host>https://$host</a> is <b>online</b> and reachable!</br></br><a href=\"index.html\">Back</a>";
fclose($socket);
} else {
echo "<br><a href=https://$host>https://$host</a> is <b>offline</b>.</br></br><a href=\"index.html\">Back</a>";
}
?>