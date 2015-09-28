# snoopyplus
An extension of the Snoopy PHP class. Snoopy is a PHP class that simulates a web browser. It automates the task of retrieving web page content and posting forms, for example.

Snoopy PHP class is available from SourceForge https://sourceforge.net/projects/snoopy/

It is no longer maintained and this wrapper class seeks to extend it.

Added features

Better handling of gzip encoding
Local storage of downloaded content ... store() and is_stored() methods
Better relative to absolute URL conversion

Example code:

<?php
include_once "snoopy/Snoopy.class.php";
include_once "class_snoopyplus.php";

$snoopyplus  = new Snoopyplus;
if($snoopyplus->fetch2("http://t.co/X8sVIJg3zP")){
		if ($snoopyplus->lastredirectaddr) {
			print "<h1>$snoopyplus->lastredirectaddr</h1>";
		}
		print "<pre>".htmlspecialchars($snoopyplus->results)."</pre>\n";
}

?>
