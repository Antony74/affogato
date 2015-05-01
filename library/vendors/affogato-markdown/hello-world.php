<?php

require('affogato-markdown.php');

$in  = "This *is* a test     \r\n\r\n";
$in .= "```Processing            \r\n";
$in .= "rect(20,30,40,50);       \r\n";
$in .= "```                      \r\n";

$out = markdown($in);

echo($out);

?>

