<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
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

// change working script if requested.
if (isset($GLOBALS['HTTP_POST_VARS']['script'])) {
    $sieve->workingscript = AppSession::getFormValue('script');
    header('Location:' . AppSession::setUrl('main.php'),true);
    exit;
}


// open sieve connection
if (!$sieve->openSieveSession()) {
    echo SmartSieve::text("ERROR: ") . $sieve->errstr . "<BR>\n";
    $sieve->writeToLog('ERROR: openSieveSession failed for ' . $sieve->user . 
        ': ' . $sieve->errstr, LOG_ERR);
    exit;
}


$ruleID = null;   /* rule number. */
$rule = null;     /* sieve rule $script->rules[$ruleID]. */

/* if one of the save, enable etc options was selected, get the rule values 
 * from POST data. if not we need to look for an unconditional forward 
 * rule from $script->rules. If one doesn't exist this will be a create 
 * forward rule page.
 */
if (isset($GLOBALS['HTTP_POST_VARS']['ruleID'])) {
    $ruleID = AppSession::getFormValue('ruleID');
    $rule = getRulePOSTValues($ruleID);
}
elseif (getForwardRule($script) !== false) {
    $ruleID = getForwardRule($script);
    if (isset($script->rules[$ruleID]))
        $rule = $script->rules[$ruleID];
}

/* save rule changes if requested. */

$action = AppSession::getFormValue('thisAction');

if ($action == 'enable') 
{
    if (isset($script->rules[$ruleID])){
        $script->rules[$ruleID]['status'] = 'ENABLED';
	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, SmartSieve::text('ERROR: ') . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERR);
	}
	else {
	    array_push($msgs, SmartSieve::text('rule successfully enabled.'));
            if ($default->return_after_update){
                header('Location: ' . AppSession::setUrl('main.php'),true);
                exit;
            }
            $rule['status'] = 'ENABLED';
	}
    }
    else {
        array_push($errors, SmartSieve::text('ERROR: rule does not exist.'));
        $sieve->writeToLog('ERROR: rule does not exist.', LOG_ERR);
    }
}
if ($action == 'disable') 
{
    if (isset($script->rules[$ruleID])){
        $script->rules[$ruleID]['status'] = 'DISABLED';
	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, SmartSieve::text('ERROR: ') . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERR);
	}
	else {
	    array_push($msgs, SmartSieve::text('rule successfully disabled.'));
            if ($default->return_after_update){
                header('Location: ' . AppSession::setUrl('main.php'),true);
                exit;
            }
            $rule['status'] = 'DISABLED';
	}
    }
    else {
        array_push($errors, SmartSieve::text('ERROR: rule does not exist.'));
        $sieve->writeToLog('ERROR: rule does not exist.', LOG_ERR);
    }
}
if ($action == 'delete') 
{
    if (isset($script->rules[$ruleID])){
        $script->rules[$ruleID]['status'] = 'DELETED';
	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, SmartSieve::text('ERROR: ') . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERR);
	}
	else {
	    header('Location: ' . AppSession::setUrl('main.php'),true);
	    exit;
	}
    }
    array_push($errors, SmartSieve::text('ERROR: rule does not exist.'));
    $sieve->writeToLog('ERROR: rule does not exist.', LOG_ERR);
}
if ($action == 'save') 
{
    $ret = checkRule($rule);
    if ($ret == 'OK'){    /* rule passed sanity checks */

        // if existing rule, update. add new if not.
	if (isset($script->rules[$ruleID])){
	    $script->rules[$ruleID] = $rule;
	}
	else $ruleID = array_push($script->rules, $rule) - 1;

	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, SmartSieve::text('ERROR: ') . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERR);
	}
	else {
            array_push($msgs, SmartSieve::text('your changes have been successfully saved.'));
            if ($default->return_after_update){
	        header('Location: ' . AppSession::setUrl('main.php'),true);
	        exit;
            }
	}

    } # if checkRule()
    else
        array_push($errors, SmartSieve::text('ERROR: ') . $ret);
}


$jsfile = 'forward.js';
$jsonload = '';
if (!empty($default->forward_help_url)){
    $help_url = $default->forward_help_url;
} else {
    $help_url = '';
}

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/menu.inc';
include $default->include_dir . '/common_status.inc';
include $default->include_dir . '/forward.inc';

$sieve->closeSieveSession();


/* if rule values supplied from form on forward.php, get rule values 
 * from POST data.
 */
function getRulePOSTValues ($ruleID)
{
    $rule = array();
    $rule['priority'] = AppSession::getFormValue('priority');
    $rule['status'] = AppSession::getFormValue('status');
    $rule['from'] = AppSession::getFormValue('from');
    $rule['to'] = AppSession::getFormValue('to');
    $rule['subject'] = AppSession::getFormValue('subject');
    $rule['action'] = AppSession::getFormValue('action');
    $rule['action_arg'] = AppSession::getFormValue($rule['action']);
    $rule['field'] = AppSession::getFormValue('field');
    $rule['field_val'] = AppSession::getFormValue('field_val');
    $rule['size'] = AppSession::getFormValue('size');
    $rule['continue'] = 0;
    if (AppSession::getFormValue('continue')) $rule['continue'] = 1;
    $rule['gthan'] = 0;
    if (AppSession::getFormValue('gthan')) $rule['gthan'] = 2;
    $rule['anyof'] = 0;
    if (AppSession::getFormValue('anyof')) $rule['anyof'] = 4;
    $rule['keep'] = 0;
    if (AppSession::getFormValue('keep')) $rule['keep'] = 8;
    $rule['regexp'] = 0;
    if (AppSession::getFormValue('regexp')) $rule['regexp'] = 128;
    $rule['unconditional'] = 0;
    if (!$rule['from'] && !$rule['to'] && !$rule['subject'] &&
       !$rule['field'] && !$rule['size'] && $rule['action'])
       $rule['unconditional'] = 1;
    if ($rule['action'] == 'custom' && !preg_match("/^ *(els)?if/i", $rule['action_arg']))
        $rule['unconditional'] = 1;
    $rule['flg'] = $rule['continue'] | $rule['gthan'] | $rule['anyof'] | $rule['keep'] | $rule['regexp'];

    return $rule;
}


/* basic sanity checks on rule.
 * any value returned will be an error msg.
 */
function checkRule(&$rule) {
    global $default;

    /* check values do not exceed acceptible sizes. */
    $conds = array('from','to','subject','field','field_val');
    foreach ($conds as $cond) {
        if (strlen($rule[$cond]) > $default->max_field_chars)
	    return SmartSieve::text('the condition value you supplied is too long. it should not exceed %d characters.', array($default->max_field_chars));
    }
    if ($rule['action'] == 'address' &&
        strlen($rule['action_arg']) > $default->max_field_chars)
	    return SmartSieve::text('the forward address you supplied is too long. it should not exceed %d characters.', array($default->max_field_chars));
    if ($rule['action'] == 'reject' &&
        strlen($rule['action_arg']) > $default->max_textbox_chars)
	    return SmartSieve::text('your reject message is too long. it should not exceed %d characters.', array($default->max_textbox_chars));

    if ($rule['field'] && !$rule['field_val'])
        return SmartSieve::text('you must supply a value for the field "%s".', array($rule['field']));
    /* remove colon from end of header field. */
    if ($rule['field'] && preg_match("/:$/",$rule['field']))
        $rule['field'] = rtrim($rule['field'], ":");
    if (!$rule['action'])
        return SmartSieve::text("please supply an action");
    if ($rule['action'] != 'discard' && !$rule['action_arg'])
        return SmartSieve::text("you must supply an argument for this action");
    /* if this is a forward rule, forward address must be a valid email. */
    if ($rule['action'] == 'address' && !preg_match("/\@/",$rule['action_arg']))
        return SmartSieve::text("'%s' is not a valid email address", array($rule['action_arg']));
    /* complain if msg size contains non-digits. */
    if (preg_match("/\D/",$rule['size']))
        return SmartSieve::text("message size value must be a positive integer");

    /* apply alternative namespacing to mailbox if necessary. */
    if ($rule['action'] == 'folder')
        $rule['action_arg'] = SmartSieve::getMailboxName($rule['action_arg']);

    return 'OK';
}


/*
 * Search for existing forward rule.
 * Return $script->rules index or false if no forward rule exists.
 */
function getForwardRule($script)
{
    $i = 0;
    foreach ($script->rules as $rule){
        if ($rule['action'] == 'address' && !$rule['from'] && !$rule['to'] && 
            !$rule['subject'] && !$rule['field'] && !$rule['size'])
            return $i;
        $i++;
    }
    return false;
}


?>