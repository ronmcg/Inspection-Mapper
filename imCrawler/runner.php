<?php
include_once(dirname(__FILE__) . '/IMCrawler.php');
include_once(dirname(__FILE__) . '/tools/Utils.php');

//$im = new IMCrawler();
//
//$im->partialCrawl(7112);

$u = new Utils();
$u->runLatLngRange(6184,8509);

?>
