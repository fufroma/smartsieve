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

// should we allow the user to choose from a list of servers?
// the list itself is in servers.php.
// if this is false, the first entry in servers.php will be used.
$default->user_select_server = true;

/* will we allow the user to access multiple scripts?
 * If true, the user will be able to create and modify multiple scripts 
 * on the server. If false, the user will only be able to access the 
 * script $default->scriptfile.
 */
$default->allow_multi_scripts = true;

// If SmartSieve does not recognise the encoding on a Sieve script, it 
// will allow the user to edit it's content in direct edit mode. If, 
// however, you do not want users to be able to modify scripts which were 
// not created using SmartSieve or Websieve, set this to false.
$default->allow_write_unrecognised_scripts = true;

/* Websieve 0.61 includes a feature which will automatically use the ':matches' 
 * comparator where the match string contains the special wildcard characters 
 * ? or *. Setting websieve_auto_matches to true will keep compatibility with 
 * this behaviour. Warning: unless you want full backwards compatibility with 
 * Websieve you should set this to false, as users may get unexpected results.
 */
$default->websieve_auto_matches = false;

/* default script filename. note that timsieved will add a .script 
 * extension when saving on the server. */
$default->scriptfile = 'smartsieve';

/* should we provide a box on the login page for users to specify which 
 * script to edit? This does not apply if allow_multi_scripts = false. */
$default->user_supply_scriptfile = false;

// base url for app. must have trailing slash '/'.
$default->baseurl = '/smartsieve/';

// location of include files
$default->include_dir = './include';

// location of config files
$default->config_dir = './conf';

// location of library files
$default->lib_dir = './lib';

/* location of images. */
$default->image_dir = './images';

// what name should we use for the php session.
$default->session_name = 'SmartSieve';

// this should be the same as $default->baseurl.
$default->cookie_domain = $GLOBALS['HTTP_SERVER_VARS']['SERVER_NAME'];

/* only scripts under this path will be able to access cookie data 
 * set during a SmartSieve session. unless this is set to the SmartSieve 
 * directory under the web root, cookie data will be accessible by all 
 * scripts at $default->cookie_domain. */
//$default->cookie_path = '/smartsieve';
$default->cookie_path = '/';

// title of each page
$default->page_title = 'SmartSieve';

/* welcome message on the login page. */
$default->login_page_heading = '&nbsp; Welcome to SmartSieve';

// if this is anything other than empty, this will be taken as the default 
// number of vacation days, rather than demanding the user supply a value.
$default->vacation_days = '4';

// if this is anything other than empty, this will be taken as the default
// text to send in vacation auto-responses, rather than demanding the user 
// supply something.
$default->vacation_text = '';

// what is the maximum number of vacation days to have the user choose from?
$default->max_vacation_days = '5';

/* what is the maximum number of characters an input field should accept? */
$default->max_field_chars = 50;

/* what is the maximum number of characters a text box should accept?
 * e.g. the reject message on the rule page. */
$default->max_textbox_chars = 500;

// should we set the working script as the active script when saving?
$default->update_activate_script = false;

// should we allow regular expression matching in sieve rules?
$default->allow_regex = true;

// should we allow users to create custom sieve rules?
// Note: existing custom rules will always be handled.
$default->allow_custom = true;

/* should we return to view all rules page following updates? */
$default->return_after_update = false;

/* what date format do we want on the script head? */
/* see http://www.php.net/manual/en/function.date.php */
$default->script_date_format = 'Y/m/d H:i:s';

// increase the timeout on the socket if you are experiencing empty 
// bad response errors.
$default->socket_timeout = 5;

// SmartSieve will select a cryptography library to use for encryption. You 
// can override the one it chooses by setting this option. Choices are 
// 'MCRYPT', 'RC5', 'HCEMD5', and ''.
$default->crypt_lib = '';

// an array containing any values needed by the Crypt object.
$default->crypt_args = array();

// SmartSieve will auto negotiate which SASL mechanism to use to authenticate.
// If you want to specify a mechanism instead set this to something other 
// than an empty string. Currently, only 'plain' is supported.
$default->sasl_mech = '';

// logging

// should we log messages?
$default->logging = false;

// at what level should we log? Can be LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, 
// LOG_WARNING, LOG_NOTICE, LOG_INFO, and LOG_DEBUG.
$default->logging_level = LOG_INFO;

// logging method. can be 'file', 'syslog'
$default->logging_method = 'file';

// this should either be a filename if logging_method = 'file', or 
// a syslog facility (eg. LOG_LOCAL4) if logging_method = 'syslog'
//$default->logging_facility = LOG_LOCAL4;
$default->logging_facility = "/var/log/smartsieve.log";

// what identifier should we use to identify log messages in the log?
$default->logging_ident = 'smartsieve';

/* help links. */

/* if this is anything other than empty, a help menu link will be 
 * displayed on the main page linked to this URL. */
// $default->main_help_url = 'http://example.co.uk/help.html';
$default->main_help_url = '';

/* if this is anything other than empty, a help menu link will be
 * displayed on the rule page linked to this URL. */
// $default->rule_help_url = 'http://example.co.uk/help.html';
$default->rule_help_url = '';

/* if this is anything other than empty, a help menu link will be
 * displayed on the vacation page linked to this URL. */
// $default->vacation_help_url = 'http://example.co.uk/help.html';
$default->vacation_help_url = '';

/* if this is anything other than empty, a help menu link will be
 * displayed on the manage scripts page linked to this URL. */
// $default->scripts_help_url = 'http://example.co.uk/help.html';
$default->scripts_help_url = '';


?>
