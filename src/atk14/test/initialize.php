<?php
// please, see file config/settings.php for some more options
define("TEST",true);

define("PATH_ATK14_APPLICATION",dirname(__FILE__)."/app/");
define("ATK14_DOCUMENT_ROOT",dirname(__FILE__)."/");

define("ATK14_USE_SMARTY3",true); // also try all the tests with Smarty2

define("ATK14_HTTP_HOST","www.testing.cz");
define("ATK14_HTTP_HOST_SSL","secure.testing.cz");

$GLOBALS["_SERVER"]["HTTP_HOST"] = "www.testing.cz";
$_GET = array();

require("../../../load.php");
