<?php

/*
 * This is the SmartSieve configuration file.
 * You should edit the global settings in this file to suit
 * your installation.
 *
 * Mail server settings can be found in conf/servers.php.
 *
 * $Id$
 */

// Set following as default language.
$default->language = "en_GB";

// Set following as default character set.
$default->charset = "ISO-8859-15";

// Cyrus allows certain users to authorize as other users. They can authenticate 
// as themselves, but login as if they were another user. This is called proxy 
// authorization. Usually, only admins and sieve_admins can do this. The 
// following array should contain the list of users for whom you want to make 
// the authz box visible on the login page. Set this to array('all') to make the 
// authz user box visible to all users.
$default->proxy_authz_users = array();

// Should we allow the user to select which language they wish to view 
// SmartSieve in? If false $default->language will always be used.
$default->user_select_lang = true;

// Should we allow the user to choose from a list of servers? The list 
// itself is in servers.php. If this is false, the first entry in 
// servers.php will be used.
$default->user_select_server = true;

// Will we allow the user to access multiple scripts?
// If true, the user will be able to create and modify multiple scripts 
// on the server. If false, the user will only be able to access the 
// script $default->scriptfile.
$default->allow_multi_scripts = true;

// If SmartSieve does not recognise the encoding on a Sieve script, it 
// will allow the user to edit it's content in a direct edit mode. If, 
// however, you do not want users to be able to modify scripts which were 
// not created using SmartSieve or Websieve, set this to false.
$default->allow_write_unrecognised_scripts = true;

// Should we allow users to switch from the GUI mode into the direct edit 
// mode? Note they will not be able to switch the script back to GUI mode.
$default->allow_change_mode = true;

// Websieve 0.61 includes a feature which will automatically use the ':matches' 
// comparator where the match string contains the special wildcard characters 
// ? or *. Setting websieve_auto_matches to true will keep compatibility with 
// this behaviour. Warning: unless you want full backwards compatibility with 
// Websieve you should set this to false, as users may get unexpected results.
$default->websieve_auto_matches = false;

// Default script to use on the server. This is only used if the user has no 
// existing scripts, or if $default->allow_multi_scripts is set to false. Note 
// that timsieved will add a '.script' extension to the file name on the server.
$default->scriptfile = 'smartsieve';

// Should we provide a box on the login page for users to specify which 
// script to edit? This is ignored if allow_multi_scripts = false.
$default->user_supply_scriptfile = false;

// The base url for SmartSieve. Must have a trailing slash.
$default->baseurl = '/smartsieve/';

// Location of include files.
$default->include_dir = './include';

// Location of config files.
$default->config_dir = './conf';

// Location of language files.
$default->lang_dir = $default->config_dir.'/locale';

// Location of library files.
$default->lib_dir = './lib';

// Location of images.
$default->image_dir = './images';

// What name should we use for the php session?
$default->session_name = 'SmartSieve';

// Cookie domain. This should be the name of the server SmartSieve is running 
// on. If the URL you are using is different from your web-server's server name 
// you will probably need to set this to an empty string.
$default->cookie_domain = $_SERVER['SERVER_NAME'];

// Cookie path. This should be the location of SmartSieve under your web root.
// If you leave this empty, all scripts on the server will have access to the 
// cookie data.
$default->cookie_path = '/smartsieve';

// Title of each page
$default->page_title = 'SmartSieve';

// Welcome message on the login page.
$default->login_page_heading = '&nbsp; Welcome to SmartSieve';

/* Vacation settings. */

// Should we always include the vacation days argument when setting a vacation rule?
// If this is set to true, and the user does not supply a value for this field, we 
// will use $default->vacation_days if set, or demand the user supply one. If false, 
// we will not include the days argument if the user does not supply a value and the 
// server will use its default value.
$default->require_vacation_days = true;;

// The default number to use in the :days argument of a vacation rule. If 
// $default->require_vacation_days is set, this will be used if the user does 
// not supply a value. If empty and require_vacation_days is set, we will demand 
// the user supply a value.
$default->vacation_days = '7';

// What is the maximum number of vacation days to have the user choose from?
$default->max_vacation_days = '30';

// Should we always include the :addresses argument when setting a vacation rule?
// If set to true, and the user does not supply any addresses, we will include 
// user@maildomain in this field. if maildomain is not set for this server in 
// conf/servers.php we will demand the user supply at least one address. If false, 
// we will not include the :addresses argument at all if the user does not supply 
// any extra vacation addresses.
$default->require_vacation_addresses = true;

// If this is anything other than empty this will be used as the default text 
// to send in vacation auto-responses when the user doesn't supply any. If empty, 
// we will always demand the user supply this.
$default->vacation_text = '';

// What is the maximum number of characters an input field should accept?
$default->max_field_chars = 50;

// What is the maximum number of characters a text box should accept?
// e.g. the reject message on the rule page.
$default->max_textbox_chars = 500;

// Should we set the working script as the active script when saving? Note that 
// the working script will always be set as the active script if there are no 
// other scripts, or if allow_multi_scripts is false.
$default->update_activate_script = false;

// Should we allow regular expression matching in sieve rules?
$default->allow_regex = true;

// Should we allow users to create custom sieve rules?
// Note: existing custom rules will always be handled.
$default->allow_custom = true;

// Should we enable the "Forward Mail" interface?
$default->use_forward_mail_interface = true;

// Should we return to the View All Rules page following rule changes?
$default->return_after_update = false;

// What format should we use for the date on the script head?
// See http://www.php.net/manual/en/function.date.php
$default->script_date_format = 'Y/m/d H:i:s';

// The timeout (in seconds) to use when reading from the socket. Increase 
// this if you are experiencing empty bad response errors.
$default->socket_timeout = 5;

// SmartSieve will select a cryptography library to use for encryption. You 
// can override the one it chooses by setting this option. Choices are 
// 'MCRYPT', 'RC4', 'HCEMD5', and ''.
$default->crypt_lib = '';

// An array containing any values needed by the Crypt object.
$default->crypt_args = array();

// SmartSieve will auto negotiate which SASL mechanism to use to authenticate.
// If you want to specify a mechanism instead set this to something other 
// than an empty string. Currently, 'plain' and 'digest-md5' are supported.
$default->sasl_mech = '';

// Logging options.

// Should we log messages?
$default->logging = false;

// At what level should we log? Can be LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, 
// LOG_WARNING, LOG_NOTICE, LOG_INFO, and LOG_DEBUG.
$default->logging_level = LOG_INFO;

// Logging method. Can be 'file', 'syslog'
$default->logging_method = 'file';

// This should either be a filename if logging_method = 'file', or 
// a syslog facility (eg. LOG_LOCAL4) if logging_method = 'syslog'
//$default->logging_facility = LOG_LOCAL4;
$default->logging_facility = "/var/log/smartsieve.log";

// What identifier should we use to identify log messages in the log?
$default->logging_ident = 'smartsieve';

/* Help links. */

// If this is anything other than empty, a help menu link will be 
// displayed on the main page linked to this URL.
// $default->main_help_url = 'http://example.co.uk/help.html';
$default->main_help_url = '';

// If this is anything other than empty, a help menu link will be
// displayed on the rule page linked to this URL.
// $default->rule_help_url = 'http://example.co.uk/help.html';
$default->rule_help_url = '';

// If this is anything other than empty, a help menu link will be
// displayed on the vacation page linked to this URL.
// $default->vacation_help_url = 'http://example.co.uk/help.html';
$default->vacation_help_url = '';

// If this is anything other than empty, a help menu link will be
// displayed on the manage scripts page linked to this URL.
// $default->scripts_help_url = 'http://example.co.uk/help.html';
$default->scripts_help_url = '';


?>
