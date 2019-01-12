<?php
require_once("ganon.php");

$html = "<html><body><table><!--<tr><td></td><td></td></tr>--></table></body></html>";

$dom = str_get_dom($html);

var_dump($dom("table tr"));
