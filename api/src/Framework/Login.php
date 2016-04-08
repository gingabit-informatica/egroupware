<?php
/**
 * EGroupware API - Render (deny-)login screen
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> rewrite in 12/2006
 * @author Pim Snel <pim@lingewoud.nl> author of the idots template set
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage framework
 * @access public
 * @version $Id$
 */

namespace EGroupware\Api\Framework;

use EGroupware\Api;

/**
 * Render (deny-)login screen
 */
class Login
{
	/**
	 * Framework object
	 *
	 * @var Api\Framework
	 */
	protected $framework;

	/**
	 * Constructor
	 *
	 * @param Api\Framework $framework
	 */
	function __construct(Api\Framework $framework)
	{
		$this->framework = $framework;
	}

	/**
	 * Displays the login screen
	 *
	 * @param string $extra_vars for login url
	 * @param string $change_passwd =null string with message to render input fields for password change
	*/
	function screen($extra_vars, $change_passwd=null)
	{
		Api\Header\ContentSecurityPolicy::add('frame-src', array());	// array() no external frame-sources

		//error_log(__METHOD__."() this->template=$this->framework->template, this->template_dir=$this->framework->template_dir, get_class(this)=".get_class($this));
		$tmpl = new Template(EGW_SERVER_ROOT.$this->framework->template_dir);

		$tmpl->set_file(array('login_form' => Api\Header\UserAgent::mobile()?'login_mobile.tpl':'login.tpl'));

		$tmpl->set_var('lang_message',$GLOBALS['loginscreenmessage']);

		// hide change-password fields, if not requested
		if (!$change_passwd)
		{
			$tmpl->set_block('login_form','change_password');
			$tmpl->set_var('change_password', '');
			$tmpl->set_var('lang_password',lang('password'));
			$tmpl->set_var('cd',check_logoutcode($_GET['cd']));
			$tmpl->set_var('cd_class', isset($_GET['cd']) && $_GET['cd'] != 1 ? 'error' : '');
			$last_loginid = $_COOKIE['last_loginid'];
			$last_domain  = $_COOKIE['last_domain'];
			$tmpl->set_var('passwd', '');
			$tmpl->set_var('autofocus_login', 'autofocus');
		}
		else
		{
			$tmpl->set_var('lang_password',lang('Old password'));
			$tmpl->set_var('lang_new_password',lang('New password'));
			$tmpl->set_var('lang_repeat_password',lang('Repeat password'));
			$tmpl->set_var('cd', $change_passwd);
			$tmpl->set_var('cd_class', 'error');
			$last_loginid = $_POST['login'];
			$last_domain  = $_POST['domain'];
			$tmpl->set_var('passwd', $_POST['passwd']);
			$tmpl->set_var('autofocus_login', '');
			$tmpl->set_var('autofocus_new_passwd', 'autofocus');
		}
		if($GLOBALS['egw_info']['server']['show_domain_selectbox'])
		{
			foreach(array_keys($GLOBALS['egw_domain']) as $domain)
			{
				$domains[$domain] = $domain;
			}
			$tmpl->set_var(array(
				'lang_domain'   => lang('domain'),
				'select_domain' => Api\Html::select('logindomain',$last_domain,$domains,true,'tabindex="2"',0,false),
			));
		}
		else
		{
			/* trick to make domain section disapear */
			$tmpl->set_block('login_form','domain_selection');
			$tmpl->set_var('domain_selection',$GLOBALS['egw_info']['user']['domain'] ?
			Api\Html::input_hidden('logindomain',$GLOBALS['egw_info']['user']['domain']) : '');

			if($last_loginid !== '')
			{
				reset($GLOBALS['egw_domain']);
				list($default_domain) = each($GLOBALS['egw_domain']);

				if(!empty ($last_domain) && $last_domain != $default_domain)
				{
					$last_loginid .= '@' . $last_domain;
				}
			}
		}

		$config_reg = Api\Config::read('registration');

		if($config_reg['enable_registration'])
		{
			if ($config_reg['register_link'])
			{
				$reg_link='&nbsp;<a href="'. $this->framework->link('/registration/index.php','lang_code='.$_GET['lang']). '">'.lang('Not a user yet? Register now').'</a><br/>';
			}
			if ($config_reg['lostpassword_link'])
			{
				$lostpw_link='&nbsp;<a href="'. $this->framework->link('/registration/index.php','menuaction=registration.registration_ui.lost_password&lang_code='.$_GET['lang']). '">'.lang('Lost password').'</a><br/>';
			}
			if ($config_reg['lostid_link'])
			{
				$lostid_link='&nbsp;<a href="'. $this->framework->link('/registration/index.php','menuaction=registration.registration_ui.lost_username&lang_code='.$_GET['lang']). '">'.lang('Lost Login Id').'</a><br/>';
			}

			/* if at least one option of "registration" is activated display the registration section */
			if($config_reg['register_link'] || $config_reg['lostpassword_link'] || $config_reg['lostid_link'] )
			{
				$tmpl->set_var(array(
				'register_link'     => $reg_link,
				'lostpassword_link' => $lostpw_link,
				'lostid_link'       => $lostid_link,
				));
			}
			else
			{
				/* trick to make registration section disapear */
				$tmpl->set_block('login_form','registration');
				$tmpl->set_var('registration','');
			}
		}

		$tmpl->set_var('login_url', $GLOBALS['egw_info']['server']['webserver_url'] . '/login.php' . $extra_vars);
		$tmpl->set_var('version', $GLOBALS['egw_info']['server']['versions']['phpgwapi']);
		$tmpl->set_var('login', $last_loginid);

		$tmpl->set_var('lang_username',lang('username'));
		$tmpl->set_var('lang_login',lang('login'));

		$tmpl->set_var('website_title', $GLOBALS['egw_info']['server']['site_title']);
		$tmpl->set_var('template_set',$this->framework->template);

		if (substr($GLOBALS['egw_info']['server']['login_logo_file'], 0, 4) == 'http' ||
			$GLOBALS['egw_info']['server']['login_logo_file'][0] == '/')
		{
			$var['logo_file'] = $GLOBALS['egw_info']['server']['login_logo_file'];
		}
		else
		{
			$var['logo_file'] = Api\Image::find('phpgwapi',$GLOBALS['egw_info']['server']['login_logo_file']?$GLOBALS['egw_info']['server']['login_logo_file']:'logo', '', null);	// null=explicit allow svg
		}
		$var['logo_url'] = $GLOBALS['egw_info']['server']['login_logo_url']?$GLOBALS['egw_info']['server']['login_logo_url']:'http://www.egroupware.org';
		if (substr($var['logo_url'],0,4) != 'http')
		{
			$var['logo_url'] = 'http://'.$var['logo_url'];
		}
		$var['logo_title'] = $GLOBALS['egw_info']['server']['login_logo_title']?$GLOBALS['egw_info']['server']['login_logo_title']:'www.eGroupWare.org';
		$tmpl->set_var($var);

		/* language section if activated in site Config */
		if (@$GLOBALS['egw_info']['server']['login_show_language_selection'])
		{
			$tmpl->set_var(array(
				'lang_language' => lang('Language'),
				'select_language' => Api\Html::select('lang',$GLOBALS['egw_info']['user']['preferences']['common']['lang'],
				Api\Translation::get_installed_langs(),true,'tabindex="1"',0,false),
			));
		}
		else
		{
			$tmpl->set_block('login_form','language_select');
			$tmpl->set_var('language_select','');
		}

		/********************************************************\
		* Check if authentification via cookies is allowed       *
		* and place a time selectbox, how long cookie is valid   *
		\********************************************************/

		if($GLOBALS['egw_info']['server']['allow_cookie_auth'])
		{
			$tmpl->set_block('login_form','remember_me_selection');
			$tmpl->set_var('lang_remember_me',lang('Remember me'));
			$tmpl->set_var('select_remember_me',Api\Html::select('remember_me', '', array(
				'' => lang('not'),
				'1hour' => lang('1 Hour'),
				'1day' => lang('1 Day'),
				'1week'=> lang('1 Week'),
				'1month' => lang('1 Month'),
				'forever' => lang('Forever'),
			),true,'tabindex="3"',0,false));
		}
		else
		{
			/* trick to make remember_me section disapear */
			$tmpl->set_block('login_form','remember_me_selection');
			$tmpl->set_var('remember_me_selection','');
		}
		$tmpl->set_var('autocomplete', ($GLOBALS['egw_info']['server']['autocomplete_login'] ? 'autocomplete="off"' : ''));

		// load jquery for login screen too
		Api\Framework::includeJS('jquery', 'jquery');

		$this->framework->render($tmpl->fp('loginout','login_form'),false,false);
	}

	/**
	* displays a login denied message
	*/
	function denylogin_screen()
	{
		$tmpl = new Template(EGW_SERVER_ROOT.$this->framework->template_dir);

		$tmpl->set_file(array(
			'login_form' => 'login_denylogin.tpl'
		));

		$tmpl->set_var(array(
			'template_set' => 'default',
			'deny_msg'     => lang('Oops! You caught us in the middle of system maintainance.').
			'<br />'.lang('Please, check back with us shortly.'),
		));

		// load jquery for deny-login screen too
		Api\Framework::includeJS('jquery', 'jquery');

		$this->framework->render($tmpl->fp('loginout','login_form'),false,false);
	}
 }
