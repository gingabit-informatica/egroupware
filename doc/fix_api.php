#!/usr/bin/env php -qC
<?php
/**
 * EGroupware - check namespace usage in converted code
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @author Ralf Becker <rb@stylite.de>
 * @copyright 2016 by Ralf Becker <rb@stylite.de>
 * @version $Id$
 */

if (php_sapi_name() !== 'cli')	// security precaution: forbit calling as web-page
{
	die('<h1>fix_api.php must NOT be called as web-page --> exiting !!!</h1>');
}

// raw replacements
$replace = array(
	"#use EGroupware\\Api;#" => '',	// remove evtl. use EGroupware\Api, as we add it again below
	"#<\?php\n/\*\*.*\*/#msU" => "\$0\n\nuse EGroupware\\Api;",
	'#egw_framework::csp_connect_src_attrs\(#' => "Api\\Header\\ContentSecurityPolicy::add('connect-src', ",
	'#egw_framework::csp_frame_src_attrs\(#' => "Api\\Header\\ContentSecurityPolicy::add('frame-src', ",
	'#egw_framework::csp_script_src_attrs\(#' => "Api\\Header\\ContentSecurityPolicy::add('script-src', ",
	'#egw_framework::csp_style_src_attrs\(#' => "Api\\Header\\ContentSecurityPolicy::add('style-src', ",
	"#egw_session::appsession\(([^,]+),\s*('[^']+')\)#" => 'Api\\Cache::getSession($2, $1)',
	"#egw_session::appsession\(([^,]+),\s*('[^']+'),\s*#" => 'Api\\Cache::setSession($2, $1, ',
	"#\\\$GLOBALS\['egw'\]->session->appsession\(([^,]+),\s*('[^']+')\)#" => 'Api\\Cache::getSession($2, $1)',
	"#\\\$GLOBALS\['egw'\]->session->appsession\(([^,]+),\s*('[^']+'),\s*#" => 'Api\\Cache::setSession($2, $1, ',
	"#\\\$GLOBALS\['egw'\]->common->#" => 'common::',
	"#\\\$GLOBALS\['egw'\]->hooks->#" => 'Api\\Hooks::',
	'#Api\\Hooks::hook_implemented#' => 'Api\\Hooks::implemented',
	"#\\\$GLOBALS\['egw'\]->translation->#" => 'Api\\Translation::',
	'#egw_framework::csp_script_src_attrs\((.*)\);#' => "Api\\Header\\ContentSecurityPolicy::add('script-src', \$1);",
	'#egw_framework::csp_style_src_attrs\((.*)\);#' => "Api\\Header\\ContentSecurityPolicy::add('style-src', \$1);",
	'#egw_framework::csp_connect_src_attrs\((.*)\);#' => "Api\\Header\\ContentSecurityPolicy::add('connect-src', \$1);",
	'#egw_framework::csp_frame_src_attrs\((.*)\);#' => "Api\\Header\\ContentSecurityPolicy::add('frame-src', \$1);",
);
// enclose class-names and static methods with some syntax check
$class_start = '#([\[\s,;(.!])';
$class_end = '(::|\\(|\\)|;|\?|:|\\s|,|$)#';
foreach(array(
	'accounts' => 'Api\\Accounts',
	'acl' => 'Api\\Acl',
	'EGW_ACL_READ' => 'Api\\Acl::READ',
	'EGW_ACL_ADD' => 'Api\\Acl::ADD',
	'EGW_ACL_EDIT' => 'Api\\Acl::EDIT',
	'EGW_ACL_DELETE' => 'Api\\Acl::DELETE',
	'EGW_ACL_PRIVATE' => 'Api\\Acl::PRIVAT',
	'EGW_ACL_GROUP_MANAGERS' => 'Api\\Acl::GROUPMGRS',
	'EGW_ACL_CUSTOM_1' => 'Api\\Acl::CUSTOM1',
	'EGW_ACL_CUSTOM_2' => 'Api\\Acl::CUSTOM2',
	'EGW_ACL_CUSTOM_3' => 'Api\\Acl::CUSTOM3',
	//'applications' => 'Api\\Applications',
	'asyncservice' => 'Api\\AsyncService',
	'auth' => 'Api\\Auth',
	'categories' => 'Api\\Categories',
	'config::get_customfields' => 'Api\\Storage\\Customfields::get',
	'config' => 'Api\\Config',
	'common::setlocale' => 'Api\\Preferences::setlocale',
	'common::generate_uid' => 'Api\\CalDAV::generate_uid',
	'common::ldap_addslashes' => 'Api\\Ldap::quote',
	'common::ldapConnect' => 'Api\\Ldap::factory',
	'common::egw_exit' => 'exit',
	'common::randomstring' => 'Api\\Auth::randomstring',
	'common::display_fullname' => 'Api\\Accounts::format_username',
	'common::grab_owner_name' => 'Api\\Accounts::username',
	'common::find_image' => 'Api\\Image::find',
	'common::image' => 'Api\\Image::find',
	'common::svg_usable' => 'Api\\Image::svg_usable',
	'common::image_map' => 'Api\\Image::map',
	'common::delete_image_map' => 'Api\\Image::invalidate',
	'common::transliterate' => 'Api\\Translation::to_ascii',
	'common::email_address' => 'Api\\Accounts::email',
	'common::next_id' => 'Api\\Accounts\\Ldap::next_id',
	'common::last_id' => 'Api\\Accounts\\Ldap::last_id',
	'country' => 'Api\\Country',
	//'egw' =>
	'egw_cache' => 'Api\\Cache',
	'egw_ckeditor_config' => 'Api\\Html\\CkEditorConfig',
	'egw_customfields' => 'Api\\Storage\\Customfields',
	'egw_db' => 'Api\\Db',
	'egw_digest_auth' => 'Api\\Header\\Authentication',
	'egw_exception' => 'Api\\Exception',
	'egw_exception_no_permission' => 'Api\\Exception\\NoPermission',
	'egw_exception_no_permission_app' => 'Api\\Exception\\NoPermission\\App',
	'egw_exception_no_permission_admin' => 'Api\\Exception\\NoPermission\\Admin',
	'egw_exception_no_permission_record' => 'Api\\Exception\\NoPermission\\Record',
	'egw_exception_not_found' => 'Api\\Exception\\NotFound',
	'egw_exception_assertion_failed' => 'Api\\Exception\\AssertionFailed',
	'egw_exception_wrong_parameter' => 'Api\\Exception\\WrongParameter',
	'egw_exception_wrong_userinput' => 'Api\\Exception\\WrongUserinput',
	'egw_exception_db' => 'Api\\Db\\Exception',
	'egw_exception_db_invalid_sql' => 'Api\\Db\\Exception\\InvalidSql',
	'egw_exception_redirect' => 'Api\\Exception\\Redirect',
	'egw_favorites' => 'Api\\Framework\\Favorites',
	'egw_framework::validate_file' => 'Api\\Framework::includeJS',
	'egw_framework::favorite_list' => 'Api\\Framework\\Favorites::list_favorites',
	'egw_framework' => 'Api\\Framework',
	'egw_json_request' => 'Api\\Json\\Request',
	'egw_json_response' => 'Api\\Json\\Response',
	'egw_link' => 'Api\\Link',
	'egw_mailer' => 'Api\\Mailer',
	'egw_session' => 'Api\\Session',
	'egw_tail' => 'Api\\Json\\Tail',
	'egw_time' => 'Api\\DateTime',
	'egw_vfs' => 'Api\\Vfs',
	'groupdav' => 'Api\CalDAV',
	'groupdav_principal' => 'Api\\CalDAV\\Principal',
	'groupdav_handler' => 'Api\\CalDAV\\Handler',
	'egw_ical_iterator' => 'Api\\CalDAV\\IcalIterator',
	'historylog' => 'Api\\Storage\\History',
	'html::\$user_agent' => 'Api\\Header\\UserAgent::type()',
	'html::\$ua_version' => 'Api\\Header\\UserAgent::version()',
	'html::\$ua_mobile' => 'Api\\Header\\UserAgent::mobile()',
	'html::safe_content_header' => 'Api\\Header\\Content::safe',
	'html::content_header' => 'Api\\Header\\Content::type',
	'html::content_disposition_header' => 'Api\\Header\\Content::disposition',
	'html' => 'Api\\Html',
	'iface_stream_wrapper' => 'Api\\Vfs\\StreamWrapperIface',
	'mime_magic' => 'Api\\MimeMagic',
	'preferences' => 'Api\\Preferences',
	'solink' => 'Api\\Link\\Storage',
	'sqlfs_stream_wrapper' => 'Api\\Vfs\\Sqlfs\\StreamWrapper',
	'sqlfs_utils' => 'Api\\Vfs\\Sqlfs\\Utils',
	'Template' => 'Api\\Framework\\Template',
	'translation::decodeMailHeader' => 'Api\\Mail\\Html::decodeMailHeader',
	'translation::replaceEmailAdresses' => 'Api\\Mail\\Html::replaceEmailAdresses',
	'translation::replaceTagsCompletley' => 'Api\\Mail\\Html::replaceTagsCompletley',
	'translation::transform_mailto2text' => 'Api\\Mail\\Html::transform_mailto2text',
	'translation::transform_url2text' => 'Api\\Mail\\Html::transform_url2text',
	'translation::convertHTMLToText' => 'Api\\Mail\\Html::convertHTMLToText',
	'translation::splithtmlByPRE' => 'Api\\Mail\\Html::splithtmlByPRE',
	'translation' => 'Api\\Translation',
	// etemplate2
	'etemplate_new' => 'Api\\Etemplate',
	'etemplate_widget' => 'Api\\Etemplate\\Widget',
	'etemplate_widget_entry' => 'Api\\Etemplate\\Widget\\Entry',
	'etemplate_widget_tree' => 'Api\\Etemplate\\Widget\\Tree',
	'etemplate_widget_select' => 'Api\\Etemplate\\Widget\\Select',
	'etemplate_widget_link' => 'Api\\Etemplate\\Widget\\Link',
	'etemplate_widget_nextmatch' => 'Api\\Etemplate\\Widget\\Nextmatch',
	'etemplate_widget_taglist' => 'Api\\Etemplate\\Widget\\Taglist',
	'etemplate_widget_file' => 'Api\\Etemplate\\Widget\\File',
	'etemplate_widget_vfs' => 'Api\\Etemplate\\Widget\\Vfs',
	'etemplate_request' => 'Api\\Etemplate\\Request',
	'nextmatch_widget::category_action' => 'Api\\Etemplate\\Widget\\Nextmatch::category_action',
	// so_sql and friends
	'so_sql' => 'Api\\Storage\\Base',
	'so_sql_cf' => 'Api\\Storage',
	'so_sql2' => 'Api\\Storage\\Base2',
	'bo_tracking' => 'Api\\Storage\\Tracking',
	'bo_merge' => 'Api\\Storage\\Merge',
	// addressbook backend
	'addressbook_bo' => 'Api\\Contacts',
	'addressbook_so' => 'Api\\Contacts\\Storage',
) as $from => $to)
{
	$replace[$class_start.$from.$class_end] = '$1'.$to.'$2';
}
//print_r($replace);

/**
 * Check namespace usage in converted code
 *
 * @param string $file filename
 * @param boolean $dry_run =false true: only echo fixed file, not fix it
 * @return boolean false on error
 */
function fix_api($file, $dry_run=false)
{
	global $prog, $replace;
	if (basename($file) == $prog) return true;	// dont fix ourself ;-)

	if (($content = $content_in = file_get_contents($file)) === false) return false;

	if (!preg_match("|<\?php\n/\*\*.*\*/|msU", $content))
	{
		error_log("No file-level phpDoc block found in $file to add 'use EGroupware\\Api;'\n");
		return;
	}
	$content = 	preg_replace(array_keys($replace), array_values($replace), $content);

	// shorten some classes, if used, with further use declarations
	foreach(array('Api\\Etemplate', 'Api\\Vfs') as $namespace)
	{
		if (strpos($content, $namespace) !== false && strpos($content, 'use EGroupware\\'.$namespace) === false)
		{
			$content = strtr($content, array(
				$namespace => str_replace('Api\\', '', $namespace),
				"use EGroupware\\Api;" => "use EGroupware\\Api;\nuse EGroupware\\$namespace;"
			));
		}
	}

	if ($dry_run)
	{
		echo $content;
	}
	elseif ($content_in != $content)
	{
		file_put_contents($file, $content);
	}

	return true;
}

/**
 * Loop recursive through directory and call fix_api for each php file
 *
 * @param string $dir
 * @param boolean $dry_run =false true: only echo fixed file, not fix it
 * @return boolean false on error
 */
function fix_api_recursive($dir, $dry_run=false)
{
	if (!is_dir($dir)) return false;

	foreach(scandir($dir) as $file)
	{
		if ($file == '.' || $file == '..') continue;

		if (is_dir($dir.'/'.$file))
		{
			fix_api_recursive($dir.'/'.$file, $dry_run);
		}
		elseif(substr($file,-4) == '.php')
		{
			echo "\r".str_repeat(' ',100)."\r".$dir.'/'.$file.': ';
			fix_api($dir.'/'.$file, $dry_run);
		}
	}
	echo "\r".str_repeat(' ',100)."\r";
	return true;
}

/**
 * Give usage
 *
 * @param string $error =null
 */
function usage($error=null)
{
	global $prog;
	echo "Usage: $prog [-h|--help] [-d|--dry-run] file or dir\n\n";
	if ($error) echo $error."\n\n";
	exit($error ? 1 : 0);
}

$args = $_SERVER['argv'];
$prog = basename(array_shift($args));

if (!$args) usage();

$dry_run = false;
while(($arg = array_shift($args)))
{
	switch($arg)
	{
		case '-h':
		case '--help':
			usage();
			break;

		case '-d':
		case '--dry-run':
			$dry_run = true;
			break;

		default:
			if ($args)	// not last argument
			{
				usage("Unknown argument '$arg'!");
			}
			break 2;
	}
}

if (!file_exists($arg)) usage("Error: $arg not found!");

if (!is_dir($arg))
{
	fix_api($arg, $dry_run);
}
else
{
	fix_api_recursive($arg, $dry_run);
}