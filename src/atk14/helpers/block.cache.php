<?php
/**
 * Provides caching for the given template block
 *
 * There are 2 main parameters: key and duration.
 * Other paramaters just salt the key.
 * 
 * Basic usage:
 *	{cache}
 *		{render partial="some_template"}
 *	{/cache}
 *
 *	{cache key="top_menu"}
 *		<ul>...</ul>
 *	{/cache}
 *
 * Cached content should be valid for 10 minutes
 *	{cache key="top_menu" duration=600}
 *		<ul>...</ul>
 *	{/cache}
 *
 * Cached content differs for admin users
 *	{cache key="top_menu" duration=600 is_admin=($logged_user && $logged_user->isAdmin())}
 *		<ul>...</ul>
 *	{/cache}
 */
function smarty_block_cache($params,$content,$template,&$repeat){
	$smarty = atk14_get_smarty_from_template($template);

	$params += array(
		"key" => null,
		"duration" => 60, // 60 sec
	);

	$duration = $params["duration"]; unset($params["duration"]);
	$key = $params["key"]; unset($params["key"]);

	if(!strlen($key)){
		$current_template = $smarty->_current_file; // "/home/bob/devel/project_x/app/views/shared/_menu.tpl"
		assert(strlen($current_template)>0);

		$lang = $smarty->getTemplateVars("lang");
		$namespace = $smarty->getTemplateVars("namespace");
		$controller = $smarty->getTemplateVars("controller");
		$action = $smarty->getTemplateVars("action");

		$tpl_nice_name = preg_replace('/.*\/([^\/]+)$/','\1',$current_template); // "/home/bob/devel/project_x/app/views/shared/_menu.tpl" -> _menu.tpl
		$key = "$namespace.$lang.$controller.$action.$tpl_nice_name.".md5($current_template);
	}

	// $key sanitization
	$key = preg_replace('/[^a-zA-Z0-9._]/','_',$key);
	$key = preg_replace('/^\./','_.',$key);

	$salt = sizeof($params) ? "_".md5(serialize($params)) : ""; // other parameters make a salt

	$cache_dir = TEMP.'/content_caches';
	!file_exists($cache_dir) && Files::Mkdir($cache_dir);
	$cache_file = TEMP."/content_caches/$key$salt";

	if($repeat){
		if(file_exists($cache_file) && time()-filemtime($cache_file)<$duration){
			// reading cache
			$repeat = false;
			return Files::GetFileContent($cache_file);
		}

		return;
	}

	$tmp_file = Files::WriteToTemp($content);
	Files::MoveFile($tmp_file,$cache_file);
	return $content;
}