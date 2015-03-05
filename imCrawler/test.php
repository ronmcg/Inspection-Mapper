<?php
include ('IMCrawler.php');
include('data/Data.php');

$im = new IMCrawler();
$result = Data::selectAHSIDs();
var_dump($result);