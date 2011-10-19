<?
// struktura testovaci databaze je k dispozici v souboru testing_structures.sql
require("../../dbmole/dbmole.inc");
require("../../dbmole/pgmole.inc");
require("../../string/load.inc");

require("../load.inc");
require("./inobj_testtable.inc");
require("./article.inc");
require("./author.inc");

function &dbmole_connection($dbmole){
	static $connection;
	//if($dbmole->getDatabaseType()=="postgresql" && $dbmole->getConfigurationName()=="default"){
		if(!isset($connection)){
			$connection = pg_connect("dbname=test user=test password=test");
		}
		return $connection;
	//}
}