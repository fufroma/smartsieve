<?php
/**
 * This is the SmartSieve configuration file.
 * You should edit the global settings in this file to suit
 * your installation.
 *
 * Mail server settings can be found in conf/servers.php.
 *
 * $Id$
 */

/**
 * Language options.
 */

// Default language to use if we don't allow the users to choose.
// This must be one of the languages in conf/locales.php.
//$default->language = "en_GB";

// Default character set to use. This will only be used if there is no
// charset set for the selected language in conf/locales.php.
//$default->charset = "ISO-8859-1";

/**
 * Login options.
 */

// Cyrus allows certain users to authenticate as themselves, but authorize to
// act as other users. This is called proxy authorization. Usually, only admins
// and sieve_admins can do this. The following array should contain a list of
// users who you want to see the authz box visible on the login page. Set this
// to array('all') to make it visible to all users.
//$default->proxy_authz_users = array();

// Should we allow users to select which language they wish to view 
// SmartSieve in? If false $default->language will always be used.
//$default->user_select_lang = true;

// Should we allow the user to choose from a list of servers? The list 
// itself is in servers.php. If this is false, the first entry in 
// servers.php will be used.
//$default->user_select_server = true;

// Should we provide a box on the login page for users to specify which 
// script to edit? This is ignored if allow_multi_scripts is false.
//$default->user_supply_scriptfile = false;

/**
 * Usability options.
 */

// Will we allow the user to access multiple scripts?
// If true, the user will be able to create and modify multiple scripts 
// on the server. If false, the user will only be able to access the 
// script $default->scriptfile.
//$default->allow_multi_scripts = true;

// Default script to use on the server. This is only used if the user has no 
// existing scripts, or if $default->allow_multi_scripts is set to false. Note 
// that timsieved will add a '.script' extension to the file name on the server.
//$default->scriptfile = 'smartsieve';

// If SmartSieve does not recognise the encoding on a Sieve script, it 
// will allow the user to edit it's content in a direct edit mode. If, 
// however, you do not want users to be able to modify scripts which were 
// not created using SmartSieve or Websieve, set this to false.
//$default->allow_write_unrecognised_scripts = true;

// Should we allow users to switch between GUI mode into the direct edit mode? 
// Note, this is generally a bad idea because any changes made in direct edit 
// mode will be lost if the user reverts to GUI mode.
//$default->allow_change_mode = false;

// Should we allow regular expression matching in sieve rules?
// FIXME: note, this currently doesn't work.
//$default->allow_regex = true;

// Should we allow users to create custom sieve rules?
// Note: existing custom rules will always be handled.
//$default->allow_custom = true;

// Notification methods to allow. This should be an array containing valid notify 
// methods, of which only 'mailto' and 'sms' are supported. Note, the server must 
// support the "notify" extension, and have notifyd configured to send notifications.
// The notify action is disabled by default.
//$default->notify_methods = array('mailto', 'sms');

// What IMAP flags should we allow users to set via the "addflag" action? The default 
// list appears below. Setting this to an empty array will disable the addflag action.
//$default->imap_flags = array('\\\\Seen', '\\\\Deleted', '\\\\Answered', '\\\\Flagged', 'Junk', 'NotJunk', '$Label1', '$Label2', '$Label3', '$Label4', '$Label5');

// Should we enable the "Forward Mail" interface? This is enabled by default.
//$default->use_forward_mail_interface = true;

// Should we enable the vacation interface? This is enabled by default.
//$default->use_vacation_interface = true;

// Should we enable the "Whitelist" interface? This is enabled by default.
//$default->use_whitelist = true;

// The following array provides a mechanism for specifying a site-specific 
// spam filtering policy. If your mail domain adds a particular message 
// header to mail either indicating that the message is spam, or holding 
// some sort of spam score (like those added by SpamAssissin for example) 
// you can specify those here. The user will then see a "Filter spam" menu 
// item which will link to a simple GUI asking them what they want to do 
// with such messages.
// $default->spam_filter = array('header'=>'X-Spam-Score',
//                               'matchStr'=>'^[0-9]',
//                               'matchType'=>':regex',
//                               'not'=>false);

/**
 * Compatibility options.
 */

// Websieve 0.61 included a feature which automatically used the ':matches' 
// comparator where the match string contains the special wildcard characters 
// ? or *. Versions of SmartSieve up to 1.0-RC1 maintained this feature if the 
// following option was enabled. The rule format has changed and this option
// now only affects the conversion of legacy scripts, but if you have scripts
// created by either of the above cases you can enable the following option to
// maintain the match type of legacy rules.
//$default->websieve_auto_matches = false;

/**
 * Site-specific options.
 */

// The base url for SmartSieve. If you make SmartSieve the web root,
// set this (and cookie_path) to '/'.
//$default->baseurl = '/smartsieve/';

// Location of include files.
//$default->include_dir = './include';

// Location of config files.
//$default->config_dir = './conf';

// Location of language files.
//$default->lang_dir = './conf/locale';

// Location of library files. Warning: don't change this.
//$default->lib_dir = './lib';

// Location of images.
//$default->image_dir = './images';

// What name should we use for the PHP session?
//$default->session_name = 'SmartSieve';

// Cookie domain. This should be the name of the server SmartSieve is running 
// on. If the domain of your site is different to the web servcer's server name
// you should set your site's domain here. If all else fails, set this to an 
// empty string, but beware that doing so is a security risk as cookies will
// be sent to other websites as well.
//$default->cookie_domain = $_SERVER['SERVER_NAME'];

// Cookie path. This should be the location of SmartSieve under your web root.
// If you leave this empty, all scripts on the server will have access to the 
// cookie data. This should match the value of baseurl above.
//$default->cookie_path = '/smartsieve';

// Title of each page
//$default->page_title = 'SmartSieve';

// Welcome message on the login page.
//$default->login_page_heading = 'Welcome to SmartSieve';

// Which page should users see following login?
//$default->initial_page = 'main.php';

// The default number of vacation days for a new vacation action.
//$default->vacation_days = '7';

// The maximum number of vacation days the user can choose from.
//$default->max_vacation_days = '30';

// What is the maximum number of characters an input field should accept?
//$default->max_field_chars = 500;

// What is the maximum number of characters a text box should accept?
// e.g. the reject message on the rule page.
//$default->max_textbox_chars = 50000;

// Should we set the working script as the active script when saving? Note that 
// the working script will always be set as the active script if there are no 
// other scripts, or if allow_multi_scripts is false.
//$default->update_activate_script = false;

// Following logout, users will be redirected to the login page. If you 
// prefer to have them redirected elsewhere you can specify this here.
// Note, this should be a complete URI including the scheme and hostname.
// $default->logout_redirect = 'http://my.logout.message.com';

// Should we return to the View Rules page following rule changes?
//$default->return_after_update = false;

// What format should we use for the date on the script head?
// See http://www.php.net/manual/en/function.date.php
//$default->script_date_format = 'Y/m/d H:i:s';

// The timeout (in seconds) to use when reading from the socket. Increase 
// this if you are experiencing empty bad response errors.
//$default->socket_timeout = 2;

// SmartSieve will select a cryptography library to use for encryption. You 
// can override the one it chooses by setting this option. Choices are 
// 'MCRYPT', 'RC4', 'HCEMD5', and ''.
//$default->crypt_lib = null;

// An array containing any values needed by the Crypt object.
//$default->crypt_args = array();

// SmartSieve will auto negotiate which SASL mechanism to use to authenticate.
// If you want to specify a mechanism instead set this to something other 
// than an empty string. Currently, 'plain' and 'digest-md5' are supported.
//$default->sasl_mech = null;

// If you are using Cyrus imapd 2.3.12 or you have the sieve_utf8fileinto 
// option enabled (defaults to off in 2.3.13 and newer) you should set the 
// following to true to make sure fileinto actions work for mailbox names 
// containing non-ascii characters.
// $default->utf8_fileinto = false;

/**
 * Logging options.
 */

// Should we log messages?
//$default->logging = false;

// At what level should we log? Can be LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, 
// LOG_WARNING, LOG_NOTICE, LOG_INFO, and LOG_DEBUG.
//$default->logging_level = LOG_INFO;

// Logging method. Can be 'file', 'syslog'
//$default->logging_method = 'syslog';

// This should either be a filename if logging_method = 'file', or 
// a syslog facility (eg. LOG_LOCAL4) if logging_method = 'syslog'
//$default->logging_facility = LOG_LOCAL4;

// What identifier should we use to identify log messages in the log?
//$default->logging_ident = 'smartsieve';

// An associative array contaning additional configuration information
// needed by the PEAR Log class.
//$default->logging_args = array();

/**
 * Menu items.
 */

// If any of the following are set, a 'Help' menu item will be displayed
// on the corresponding pages linked to the values set here. These should
// be full URLs.
// $default->main_help_url = 'http://example.co.uk/help.html';
//$default->main_help_url = '';
//$default->spam_help_url = '';
//$default->forward_help_url = '';
//$default->custom_help_url = '';
//$default->vacation_help_url = '';
//$default->whitelist_help_url = '';
//$default->rule_help_url = '';
//$default->scripts_help_url = '';

// The following should be an array containing extra items you want 
// to include in SmartSieve's menu. Each element should be an 
// associative array containing the keys 'uri' and 'label'. The anchor's 
// "target" attribute can be set via the optional 'target' value, and
// the icon can be set via the optional 'img' value.
// $default->menu_items = array(
//     array('uri'=>'http://mydomain.com/mypage.html',
//           'label'=>'Menu Item',
//           'img'=>'./images/item.gif',
//           'target'=>'_blank'));

/**
 * Custom function hooks.
 */

// If this is set to a function name, that function will be called to 
// retrieve login details for the user. Note, the details supplied when 
// the login page is submitted take precedence over this. The function 
// must return an array of the form expected by the SmartSieve::getLoginDetails
// function.
//$default->get_login_details_hook = null;

// If you have an external source of email addresses you want your users 
// to see on the vacation settings page, set the following to the name 
// of a function that will retrieve these. SmartSieve will then include 
// these in the list of addresses the user might include in their vacation 
// addresses. The function should return an array of addresses.
//$default->get_email_addresses_hook = null;

// If you want to extend the sanity checking done prior to the user saving a 
// rule you can define a function and set the function name here. The function
// must take a rule array as a parameter, and will be expected to return boolean 
// true to allow the rule to be saved, or false to disallow it. There is an 
// example isSaneHook() function below.
//$default->is_sane_hook = null;

/**
 * Example get_login_details_hook function.
 *
 * This example looks for credentials set by a single-sign-on 
 * system. If set, the user will not need to log in again.
 *
 * @return array Login details
 */
//function getSSODetails()
//{
//    $details = array();
//    if (isset($_SERVER['REMOTE_USER']) && isset($_SERVER['AUTH_TYPE']) &&
//        $_SERVER['AUTH_TYPE'] == 'sso' && isset($_COOKIE['sso'])) {
//        $details['auth'] = $_SERVER['REMOTE_USER'];
//        $details['passwd'] = $_COOKIE['sso'];
//        $details['authz'] = '';
//        $details['server'] = 'example';
//    }
//    return $details;
//}

/**
 * Example get_email_addresses_hook function.
 *
 * @return array The list of email addresses
 */
//function getEmailAddresses()
//{
//    $addresses = array();
//    if (extension_loaded('ldap')) {
//        $ds = ldap_connect('ldap.example.com');
//        if ($ds) {
//            // Anonymous bind.
//            $r = ldap_bind($ds);
//            $sr = ldap_search($ds, "ou=people,dc=example,dc=com", 
//                              "uid=".$_SESSION['smartsieve']['authz']);
//            $entries = ldap_get_entries($ds, $sr);
//            for ($i=0; $i<$entries['count']; $i++) {
//                $addresses[] = $entries[$i]['mail'][0];
//            }
//        }
//        ldap_close($ds);
//    }
//    return $addresses;
//}

/**
 * Example is_sane_hook function.
 *
 * This function will be called (if set via is_sane_hook above) prior to a user
 * saving a rule, and will be passed a rule array as a parameter. This allows
 * you to add custom sanity checks to those of isSane(). You must return boolean
 * true to allow the user to save the rule, or false to disallow it.
 *
 * @param array $rule The rule values
 * @return boolean True if rule values are acceptable, false if not
 */
//function isSaneHook($rule)
//{
//    foreach ($rule['actions'] as $action) {
//        if ($action['type'] == ACTION_REDIRECT &&
//            !preg_match("/\@example.com\$/", $action['address'])) {
//            SmartSieve::setError(sprintf("Not allowed to forward mail to %s", $action['address']));
//            return false;
//        }
//    }
//    return true;
//}

?>
