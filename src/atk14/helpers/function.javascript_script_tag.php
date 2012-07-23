<?php
/**
 * Smarty tag for inserting html <script /> tag.
 *
 * Includes HTML tag <script /> to output.
 * Generated file paths are relative to ./public/javascripts
 *
 * Example:
 * <code>
 * {javascript_script_tag file="scripts.js"}
 * </code>
 *
 * <ul>
 * 	<li><b>file</b> - name of file with javascript</li>
 * </ul>
 *
 * @package Atk14
 * @subpackage Helpers
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Smarty function that outputs HTML <script /> tag.
 */
function smarty_function_javascript_script_tag($params,&$smarty){
	global $ATK14_GLOBAL;
	$smarty = atk14_get_smarty_from_template($smarty);

	$src = $ATK14_GLOBAL->getPublicBaseHref()."javascripts/$params[file]";
	$filename = $ATK14_GLOBAL->getPublicRoot()."javascripts/$params[file]";


	if(file_exists($filename)){
		$src = $src."?".filemtime($filename);
	}else{
		return "<!-- javascript file not found: $filename -->";
	}

	unset($params["file"]);

	$attribs = array();
	reset($params);
	while(list($key,$value) = each($params)){
		$attribs[] = htmlspecialchars($key)."=\"".htmlspecialchars($value)."\"";
	}
	if(sizeof($attribs)>0){
		array_unshift($attribs,"");
	}
	
	return "<script src=\"$src\" type=\"text/javascript\"".join(" ",$attribs)."></script>";
}
?>
