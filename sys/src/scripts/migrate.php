#!/usr/bin/env php
<?
/**
* Do pending migrations.
* Load and process *.sql or *.inc files from the migration folder (db/migrations/).
* 
* Usage:
* 	$ php migrate.php
* 	$ ATK14_ENV=production php migrate.php
*
* Note: table schema_magrations needs to be created in the database.
*		CREATE TABLE schema_magrations(
*			version VARCHAR(255) PRIMARY KEY
*		);
*/

require_once(dirname(__FILE__)."/load.inc");

$logger = &Atk14Migration::GetLogger();

// getting list of migration files
$migrations = array();
$dir = opendir($ATK14_GLOBAL->getMigrationsPath());
while($item = readdir($dir)){
	if(preg_match("/(.+)\\.(sql|inc)$/",$item,$matches)){
		$migrations[] = $item;
	}
}
closedir($dir);
asort($migrations);

// getting list of done migrations
$already_done_migrations = $dbmole->selectIntoArray("SELECT version FROM schema_magrations ORDER BY version");

$counter = 0;
foreach($migrations as $m){
	if(in_array($m,$already_done_migrations)){ continue; }

	$logger->info("about to start migration $m"); $logger->flush();
	
	if(preg_match("/^[0-9]+_(.*)\\.inc$/",$m,$matches)){
		require_once($ATK14_GLOBAL->getMigrationsPath().$m);
		$class_name = preg_replace("/_/","",$matches[1]);
		$migr = new $class_name($m);
	}else{
		// an *.sql file
		$migr = new Atk14MigrationBySqlFile($m);
	}

	if(!$migr->migrateUp()){
		break; // an error occured
	}

	$logger->info("migration $m has been successfully finished"); $logger->flush();

	$counter++;
}

if($counter==0){ $logger->info("there is nothing to migrate"); }

$logger->flush_all();