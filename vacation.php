<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->lib_dir/Managesieve.php";
require "$default->lib_dir/SmartSieve.lib";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$smartsieve = &$_SESSION['smartsieve'];
$script = &$_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']];

// If a session does not exist, redirect to login page.
if (SmartSieve::authenticate() !== true) {
    header('Location: ' . SmartSieve::setUrl('login.php'),true);
    exit;
}

$vacation = array();   /* $script->vacation. */

/* if save, enable or disable was selected from vacation.php, then get 
 * the vacation values from POST data. if not, use $script->vacation.
 */
if (isset($_POST['submitted'])) {
    $address = AppSession::getFormValue('addresses');
    $address = preg_replace("/\"|\\\/","",$address);
    $addresses = array();
    $addresses = preg_split("/\s*,\s*|\s+/",$address);
    $vacation['text'] = AppSession::getFormValue('text');
    $vacation['days'] = AppSession::getFormValue('days');
    $vacation['addresses'] = $addresses;
    $vacation['status'] = AppSession::getFormValue('status');
} elseif ($script->vacation) {
    $vacation = $script->vacation;
} else {
    $vacation = array();
    $vacation['status'] = 'on';
    $vacation['text'] = !empty($default->vacation_text) ? $default->vacation_text : '';
    $vacation['days'] = !empty($default->vacation_days) ? $default->vacation_days : 0;
    $vacation['addresses'] = array();
}

/* save vacation settings if requested. */

$action = AppSession::getFormValue('thisAction');

if ($action == 'enable') {
    if ($script->vacation){
        $script->vacation['status'] = 'on';
        /* write and save the new script. */
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('vacation settings successfully enabled.'));
            if (SmartSieve::getConf('return_after_update') === true) {
                header('Location: ' . SmartSieve::setUrl('main.php'),true);
                exit;
            }
            $vacation['status'] = 'on';
        }
    } else {
        SmartSieve::setError(SmartSieve::text('ERROR: vacation settings not yet saved.'));
    }
}
if ($action == 'disable') {
    if ($script->vacation){
        $script->vacation['status'] = 'off';
        /* write and save the new script. */
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('vacation settings successfully disabled.'));
            if (SmartSieve::getConf('return_after_update') === true) {
                header('Location: ' . SmartSieve::setUrl('main.php'),true);
                exit;
            }
            $vacation['status'] = 'off';
        }
    } else {
        SmartSieve::setError(SmartSieve::text('ERROR: vacation settings not yet saved.'));
    }
}
if ($action == 'save') 
{
    if (($ret = checkRule($vacation)) === true){
        $script->vacation = $vacation;
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('your changes have been successfully saved.'));
            if (SmartSieve::getConf('return_after_update') === true) {
	        header('Location: ' . SmartSieve::setUrl('main.php'),true);
	        exit;
            }
        }
    } else {
        SmartSieve::setError(SmartSieve::text('ERROR: ') . $ret);
    }
}


$jsfile = 'vacation.js';
$jsonload = '';
if (!empty($default->vacation_help_url)){
    $help_url = $default->vacation_help_url;
} else {
    $help_url = '';
}
$wrap_width = (SmartSieve::getConf('wrap_width')) ? SmartSieve::getConf('wrap_width') : 80;

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/menu.inc';
include $default->include_dir . '/common_status.inc';
include $default->include_dir . '/vacation.inc';

SmartSieve::close();


/* basic sanity checks on vacation rule.
 * any value returned will be an error msg.
 * note: we will only demand a value from user if no default is set in config.
 */
function checkRule($vacation)
{
    if (!$vacation['text'] && !SmartSieve::getConf('vacation_text')) {
	return SmartSieve::text("please supply the message to send with auto-responses");
    }
    if (!$vacation['days'] && SmartSieve::getConf('require_vacation_days') && !SmartSieve::getConf('vacation_days')) {
        return SmartSieve::text("please select the number of days to wait between responses");
    }
    // does $vacation['addresses'] contain any valid addresses?
    $a = false;
    foreach ($vacation['addresses'] as $addr){
        $tokens = explode('@',$addr);
        if (count($tokens) == 2 && $tokens[0] != '' && strpos($tokens[1],'.') !== false){
            $a = true;
        }
    }
    if ($a == false && SmartSieve::getConf('require_vacation_addresses') && !$_SESSION['smartsieve']['maildomain']) {
        return SmartSieve::text("please supply at least one valid vacation address");
    }

    /* check values don't exceed acceptible sizes. */
    foreach ($vacation['addresses'] as $addr){
        if (strlen($addr) > SmartSieve::getConf('max_field_chars', 50)) {
            return SmartSieve::text('vacation address should not exceed %d characters.', array(SmartSieve::getConf('max_field_chars', 50)));
        }
    }
    if (strlen($vacation['text']) > SmartSieve::getConf('max_textbox_chars', 500)) {
	return SmartSieve::text('vacation message should not exceed %d characters.', array(SmartSieve::getConf('max_textbox_chars', 500)));
    }

    /* complain if vacation days contains non-digits. */
    if (preg_match("/\D/",$vacation['days']))
	return SmartSieve::text('vacation days must be a positive integer');

    return true;
}


?>
