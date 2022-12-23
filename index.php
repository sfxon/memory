<?php

///////////////////////////////////////////////////////////////////////////////
// @title		Blitz - Business Logic.
// @author	Steve Kraemer
// @info		Base file. This is the starting point of the application.
// @license	All rights reserved.
//////////////////////////////////////////////////////////////////////////////

date_default_timezone_set('Europe/Berlin');

$measure_begin = microtime(true);


error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('core/cCore.class.php');

$cCore = new cCore();
$cCore->run();

$measure_end = microtime(true);
$difference = $measure_end - $measure_begin;
//var_dump($difference);


?>