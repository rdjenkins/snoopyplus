# snoopyplus
An extension of the Snoopy PHP class. Snoopy is a PHP class that simulates a web browser. It automates the task of retrieving web page content and posting forms, for example.

Snoopy PHP class is available from SourceForge https://sourceforge.net/projects/snoopy/

It is no longer maintained and this wrapper class seeks to extend it.

Added features

<ul>
<li>Better handling of gzip encoding</li>
<li>Local storage of downloaded content ... store() and is_stored() methods</li>
<li>Better relative to absolute URL conversion</li>
</ul>

Example code:

<pre>
&lt;?php
include_once &quot;snoopy/Snoopy.class.php&quot;;
include_once &quot;class_Snoopyplus.php&quot;;

$snoopyplus  = new Snoopyplus;
if($snoopyplus-&gt;fetch2(&quot;http://t.co/X8sVIJg3zP&quot;)){
		if ($snoopyplus-&gt;lastredirectaddr) {
			print &quot;&lt;h1&gt;$snoopyplus-&gt;lastredirectaddr&lt;/h1&gt;&quot;;
		}
		print &quot;&lt;pre&gt;&quot;.htmlspecialchars($snoopyplus-&gt;results).&quot;&lt;/pre&gt;&quot;;
?&gt;
</pre>
