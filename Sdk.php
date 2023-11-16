<?php

/**
 * SDK Workspace
 */
if (!defined("SDK_WORK_DIR"))
{
	define("SDK_WORK_DIR", "/tmp/");
}


if (!defined("AUTOLOADER_PATH"))
{
	define("AUTOLOADER_PATH", dirname(__FILE__));
}

/**
 * autoLoader
 **/
require("Autoloader.php");