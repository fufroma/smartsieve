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
require "$default->config_dir/style.php";

session_name($default->session_name);
@session_start();
$errors = array();
$msgs = array();

$sieve = &$GLOBALS['HTTP_SESSION_VARS']['sieve'];
$scripts = &$GLOBALS['HTTP_SESSION_VARS']['scripts'];

// if a session does not exist, go to login page
if (!is_object($sieve) || !$sieve->authenticate()) {
	header('Location: ' . AppSession::setUrl('login.php'),true);
	exit;
}

// should have a valid session at this point

// start sieve session, and get the rules via the script object
if (!$sieve->openSieveSession()) {
    print "ERROR: " . $sieve->errstr . "<BR>\n";
    $sieve->writeToLog("ERROR: openSieveSession failed for " . $sieve->user .
	': ' . $sieve->errstr, LOG_ERR);
    exit;
}

// if user has just logged in select which script to open.
if (!$sieve->workingscript){
    if (!$sieve->initialWorkingScript()){
        $sieve->writeToLog('ERROR: ' . $sieve->errstr);
        array_push($errors, 'ERROR: ' . $sieve->errstr);
    }
}

// change working script if requested.
if (isset($GLOBALS['HTTP_POST_VARS']['script'])) {
    $sieve->workingscript = AppSession::getFormValue('script');
}

// create script object if doesn't already exist.
if (!isset($scripts[$sieve->workingscript]) || 
    !is_object($scripts[$sieve->workingscript])){
    $scripts[$sieve->workingscript] = new Script($sieve->workingscript);
    if (!is_object($scripts[$sieve->workingscript])){
        writeToLog('main.php: failed to create script object ' . $sieve->workingscript);
        array_push($errors, 'failed to create script object ' . $sieve->workingscript);
    }
}

$script = &$scripts[$sieve->workingscript];

if (!$script->retrieveRules($sieve->connection)) {
    array_push($errors, 'ERROR: ' . $script->errstr);
    $sieve->writeToLog("ERROR: retrieveRules failed for " . $sieve->user .
	": " . $script->errstr, LOG_ERR);
}

/* do rule status change if requested. */

$action = AppSession::getFormValue('action');

if ($action) {

    if ($action == 'enable') {
        $rules = AppSession::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'ENABLED';
        }
    }

    if ($action == 'disable') {
        $rules = AppSession::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'DISABLED';
        }
    }

    if ($action == 'delete') {
        $rules = AppSession::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'DELETED';
        }
    }

    if ($action == 'save') {
        $script->script = AppSession::getFormValue('text');
        $script->script .= "\n\n";
    }

    /* increase rule priority. */
    if ($action == 'increase') {
        $rindex = AppSession::getFormValue('rindex');
        /* if this rule and one before it exists, switch them. */
        if ($script->rules[$rindex] &&
		$script->rules[$rindex-1]) {
	    $tmp = $script->rules[$rindex-1];
	    $script->rules[$rindex-1] = $script->rules[$rindex];
	    $script->rules[$rindex] = $tmp;
        }
    }
    /* decrease rule priority. */
    if ($action == 'decrease') {
        $rindex = AppSession::getFormValue('rindex');
        /* if this rule and one after it exists, switch them. */
        if ($script->rules[$rindex] &&
            $script->rules[$rindex+1]) {
	    $tmp = $script->rules[$rindex+1];
	    $script->rules[$rindex+1] = $script->rules[$rindex];
	    $script->rules[$rindex] = $tmp;
        }
    }
    /* write these changes. */
    if (!$script->updateScript($sieve->connection)) {
	array_push($errors, 'ERROR: ' . $script->errstr);
	$sieve->writeToLog('ERROR: updateScript failed for ' . $sieve->user
	    . ': ' . $script->errstr, LOG_ERR);
    }
    /* get the rules from the saved script again. */
    else {
	if (!$script->retrieveRules($sieve->connection)) {
	    array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog('ERROR: retrieveRules failed for ' . $sieve->user
	    	. ': ' . $script->errstr, LOG_ERR);
	}
    }
}


if ($script->mode == 'advanced'){
    $jsfile = 'script-direct.js';
} else {
    $jsfile = 'main.js';
}
$jsonload = '';
if (!empty($default->main_help_url)){
    $help_url = $default->main_help_url;
} else {
    $help_url = '';
}

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/menu.inc';
include $default->include_dir . '/common_status.inc';

if ($script->mode == 'advanced' || $script->so == false){
    include $default->include_dir . '/script-direct.inc';
}
else {
    include $default->include_dir . '/script-gui.inc';
}

$sieve->closeSieveSession();


function buildRule($rule) {
    $andor = " AND ";
    $started = 0;
    if ($rule['anyof']) $andor = " OR ";

    if (preg_match("/custom/i",$rule['action'])){
        return '[Custom Rule] ' . $rule['action_arg'];
    }

    $complete = "IF ";
    if ($rule['unconditional']) $complete = "[Unconditional] ";

    if ($rule['from']) {
        $match = setMatchType($rule['from'],$rule['regexp']);
	$complete .= "'From:' " . $match . " '" . $rule['from'] . "'";
	$started = 1;
    }
    if ($rule['to']) {
	if ($started) $complete .= $andor;
        $match = setMatchType($rule['to'],$rule['regexp']);
	$complete .= "'To:' " . $match . " '" . $rule['to'] . "'";
	$started = 1;
    }
    if ($rule['subject']) {
	if ($started) $complete .= $andor;
        $match = setMatchType($rule['subject'],$rule['regexp']);
	$complete .= "'Subject:' " . $match . " '" . $rule['subject'] . "'";
	$started = 1;
    }
    if ($rule['field'] && $rule['field_val']) {
	if ($started) $complete .= $andor;
        $match = setMatchType($rule['field_val'],$rule['regexp']);
	$complete .= "'" . $rule['field'] . "' " . $match . " '" . $rule['field_val'] . "'";
	$started = 1;
    }
    if ($rule['size']) {
	$xthan = " less than '";
	if ($rule['gthan']) $xthan = " greater than '";
	if ($started) $complete .= $andor;
	$complete .= "message " . $xthan . $rule['size'] . "KB'";
	$started = 1;
    }
    if (!$rule['unconditional']) $complete .= " THEN ";
    if (preg_match("/folder/i",$rule['action']))
	$complete .= "file into '" . $rule['action_arg'] . "';";
    if (preg_match("/reject/i",$rule['action']))
	$complete .= "reject '" . $rule['action_arg'] . "';";
    if (preg_match("/address/i",$rule['action']))
        $complete .= "forward to '" . $rule['action_arg'] . "';";
    if (preg_match("/discard/i",$rule['action']))
        $complete .= "discard;";
    if ($rule['continue']) $complete .= " [Continue]";
    if ($rule['keep']) $complete .= " [Keep a copy]";
    return htmlspecialchars($complete);
}

function setMatchType (&$matchstr, $regex = false)
{
    $match = 'contains';
    if (preg_match("/\s*!/", $matchstr)) 
        $match = 'does not contain';
    if (preg_match("/\*|\?/", $matchstr) &&
        $GLOBALS['default']->websieve_auto_matches == true){
        $match = 'matches';
        if (preg_match("/\s*!/", $matchstr))
            $match = 'does not match';
    }
    if ($regex){
        $match = 'matches regexp';
        if (preg_match("/\s*!/", $matchstr))
            $match = 'does not match regexp';
    }
    $matchstr = preg_replace("/^\s*!/","",$matchstr);
    return $match;
}


?>

