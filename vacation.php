<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->lib_dir/sieve.lib";
require "$default->lib_dir/SmartSieve.lib";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$errors = array();
$msgs = array();

$sieve = &$GLOBALS['HTTP_SESSION_VARS']['sieve'];
$scripts = &$GLOBALS['HTTP_SESSION_VARS']['scripts'];
$script = &$scripts[$sieve->workingscript];

// if a session does not exist, go to login page
if (!is_object($sieve) || !$sieve->authenticate()) {
	header('Location: ' . AppSession::setUrl('login.php'),true);
	exit;
}

// should have a valid session at this point

// open sieve connection
if (!$sieve->openSieveSession()) {
    print "ERROR: " . $sieve->errstr . "<BR>\n";
    $sieve->writeToLog("ERROR: openSieveSession failed for " . $sieve->user .
        ': ' . $sieve->errstr, LOG_ERR);
    exit;
}


$vacation = array();   /* $script->vacation. */

/* if save, enable or disable was selected from vacation.php, then get 
 * the vacation values from POST data. if not, use $script->vacation.
 */
if (isset($GLOBALS['HTTP_POST_VARS']['submitted'])) {
    $address = AppSession::getFormValue('addresses');
    $address = preg_replace("/\"|\\\/","",$address);
    $addresses = array();
    $addresses = preg_split("/\s*,\s*|\s+/",$address);
    $vacation['text'] = AppSession::getFormValue('text');
    $vacation['days'] = AppSession::getFormValue('days');
    $vacation['addresses'] = $addresses;
    $vacation['status'] = AppSession::getFormValue('status');
}
elseif ($script->vacation) {
    $vacation = $script->vacation;
}
else {
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
        if (!$script->updateScript($sieve->connection)) {
            array_push($errors, 'ERROR: ' . $script->errstr);
            $sieve->writeToLog("ERROR: vacation.php: can't update script: "
                . $script->errstr, LOG_ERR);
        }
        else {
            array_push($msgs, 'vacation settings successfully enabled.');
            if ($default->return_after_update){
                header('Location: ' . AppSession::setUrl('main.php'),true);
                exit;
            }
            $vacation['status'] = 'on';
        }
    }
    else {
        array_push($errors, 'ERROR: vacation setting not yet saved.');
        $sieve->writeToLog('ERROR: vacation setting not yet saved.', LOG_ERR);
    }
}
if ($action == 'disable') {
    if ($script->vacation){
        $script->vacation['status'] = 'off';
        /* write and save the new script. */
        if (!$script->updateScript($sieve->connection)) {
            array_push($errors, 'ERROR: ' . $script->errstr);
            $sieve->writeToLog("ERROR: vacation.php: can't update script: " 
                    . $script->errstr, LOG_ERR);
        }
        else {
            array_push($msgs, 'vacation settings successfully disabled.');
            if ($default->return_after_update){
                header('Location: ' . AppSession::setUrl('main.php'),true);
                exit;
            }
            $vacation['status'] = 'off';
        }
    }
    else {
        array_push($errors, 'ERROR: vacation setting not yet saved.');
	$sieve->writeToLog('ERROR: vacation setting not yet saved.', LOG_ERR);
    }
}
if ($action == 'save') 
{
    /* if checkRule() doesn't return an error, write the modified script. */
    if (!$ret = checkRule($vacation)){
        $script->vacation = $vacation;
        if (!$script->updateScript($sieve->connection)) {
            array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog("ERROR: vacation.php: can't update script: "
		. $script->errstr, LOG_ERR);
        }
        else {
            array_push($msgs, 'your changes have been successfully saved.');
            if ($default->return_after_update){
	        header('Location: ' . AppSession::setUrl('main.php'),true);
	        exit;
            }
        }
    }
    else
	array_push($errors, 'ERROR: ' . $ret);
}


$jsfile = 'vacation.js';
$jsonload = '';
if (!empty($default->vacation_help_url)){
    $help_url = $default->vacation_help_url;
} else {
    $help_url = '';
}

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/menu.inc';
include $default->include_dir . '/common_status.inc';
include $default->include_dir . '/vacation.inc';

$sieve->closeSieveSession();


/* basic sanity checks on vacation rule.
 * any value returned will be an error msg.
 * note: we will only demand a value from user if no default is set in config.
 */
function checkRule($vacation)
{
    global $default,$sieve;

    if (!$vacation['text'] && !$default->vacation_text){
	return "please supply the message to send with auto-responses";
    }
    if (!$vacation['days'] && $default->require_vacation_days && !$default->vacation_days){
	return "please select the number of days to wait between responses";
    }
    // does $vacation['addresses'] contain any valid addresses?
    $a = false;
    foreach ($vacation['addresses'] as $addr){
        $tokens = explode('@',$addr);
        if (count($tokens) == 2 && $tokens[0] != '' && strpos($tokens[1],'.') !== false){
            $a = true;
        }
    }
    if ($a == false && $default->require_vacation_addresses && !$sieve->maildomain){
        return "please supply at least one valid vacation address";
    }

    /* check values don't exceed acceptible sizes. */
    foreach ($vacation['addresses'] as $addr){
        if (strlen($addr) > $default->max_field_chars)
            return 'vacation address should not exceed ' . 
		$default->max_field_chars . ' characters.';
    }
    if (strlen($vacation['text']) > $default->max_textbox_chars)
	return 'vacation message should not exceed ' . 
	    $default->max_textbox_chars . ' characters.';

    /* complain if vacation days contains non-digits. */
    if (preg_match("/\D/",$vacation['days']))
	return 'vacation days must be a positive integer.';

    return 0;
}


?>
