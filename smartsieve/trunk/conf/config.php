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

$default->scriptfile = "default";

// should we allow the user to choose a script filename other than
// the one above?
$default->user_supply_scriptfile = true;

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

// this should be the same as $default->baseurl.
$default->cookie_domain = $GLOBALS['HTTP_SERVER_VARS']['SERVER_NAME'];

$default->cookie_path = '/';

// title of each page
$default->page_title = 'SmartSieve';

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
$default->update_activate_script = true;

// should we allow regular expression matching in sieve rules?
$default->allow_regex = true;

/* should we return to view all rules page following updates? */
$default->return_after_update = false;

// logging

// should we log messages?
$default->logging = true;

// at what level should we log? Can be LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, 
// LOG_WARNING, LOG_NOTICE, LOG_INFO, and LOG_DEBUG.
$default->logging_level = LOG_INFO;

// logging method. can be 'file', 'syslog'
$default->logging_method = 'file';

// this should either be a filename if logging_method = 'file', or 
// a syslog facility (eg. LOCAL4) if logging_method = 'syslog'
$default->logging_facility = "/home/httpd/smartsieve.log";

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


?>
