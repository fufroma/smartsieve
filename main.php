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
require "$default->lib_dir/Script.php";
require "$default->config_dir/style.php";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$smartsieve = &$_SESSION['smartsieve'];

// If a session does not exist, redirect to login page.
if (SmartSieve::authenticate() !== true) {
    header('Location: ' . SmartSieve::setUrl('login.php'),true);
    exit;
}

// Change working script if requested.
if (isset($_POST['script'])) {
    SmartSieve::setWorkingScript(SmartSieve::getFormValue('script'));
}

$script = &$_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']];

if (!$script->retrieveRules()) {
    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
    SmartSieve::writeToLog(sprintf('failed reading rules from script "%s" for %s: %s', 
        $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
}

/* do rule status change if requested. */

$action = SmartSieve::getFormValue('action');

if ($action) {

    if ($action == 'enable') {
        $rules = SmartSieve::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'ENABLED';
        }
    }

    if ($action == 'disable') {
        $rules = SmartSieve::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'DISABLED';
        }
    }

    if ($action == 'delete') {
        $rules = SmartSieve::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'DELETED';
        }
    }

    if ($action == 'save') {
        $script->script = SmartSieve::getFormValue('text');
        $script->script .= "\n\n";
    }

    /* increase rule priority. */
    if ($action == 'increase') {
        $rindex = SmartSieve::getFormValue('rindex');
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
        $rindex = SmartSieve::getFormValue('rindex');
        /* if this rule and one after it exists, switch them. */
        if ($script->rules[$rindex] &&
            $script->rules[$rindex+1]) {
	    $tmp = $script->rules[$rindex+1];
	    $script->rules[$rindex+1] = $script->rules[$rindex];
	    $script->rules[$rindex] = $tmp;
        }
    }
    /* write these changes. */
    if (!$script->updateScript()) {
        SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
        SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s', 
            $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
    }
    /* get the rules from the saved script again. */
    else {
	if (!$script->retrieveRules()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            SmartSieve::writeToLog(sprintf('failed reading rules from script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
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

SmartSieve::close();


function buildRule($rule) {
    $andor = ' ' . SmartSieve::text('AND') . ' ';
    $started = 0;
    if ($rule['anyof']) $andor = ' ' . SmartSieve::text('OR') . ' ';

    if (preg_match("/custom/i",$rule['action'])){
        return '[' . SmartSieve::text('Custom Rule') . '] ' . $rule['action_arg'];
    }

    $complete = SmartSieve::text('IF') . ' ';
    if ($rule['unconditional']) $complete = '[' . SmartSieve::text('Unconditional') . '] ';

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
	$xthan = SmartSieve::text('less than');
	if ($rule['gthan']) $xthan = SmartSieve::text('greater than');
	if ($started) $complete .= $andor;
	$complete .= SmartSieve::text("message %s '%sKB'", array($xthan,$rule['size']));
	$started = 1;
    }
    if (!$rule['unconditional']) $complete .= " ".SmartSieve::text('THEN')." ";
    if (preg_match("/folder/i",$rule['action']))
	$complete .= SmartSieve::text("file into '%s';",array($rule['action_arg']));
    if (preg_match("/reject/i",$rule['action']))
	$complete .= SmartSieve::text("reject '%s';",array($rule['action_arg']));
    if (preg_match("/address/i",$rule['action']))
        $complete .= SmartSieve::text("forward to '%s';",array($rule['action_arg']));
    if (preg_match("/discard/i",$rule['action']))
        $complete .= SmartSieve::text("discard;");
    if ($rule['continue']) $complete .= " [".SmartSieve::text('Continue')."]";
    if ($rule['keep']) $complete .= " [".SmartSieve::text('Keep a copy')."]";
    return htmlspecialchars($complete);
}

function buildVacationString()
{
    global $script;
    $vacation = $script->vacation;
    $vacation_str = '';
    if (!is_array($vacation)){ return htmlspecialchars($vacation_str); }

    $vacation_str .= SmartSieve::text('Respond');
    if (is_array($vacation['addresses']) && $vacation['addresses'][0]){
        $vacation_str .= ' ' . SmartSieve::text('to mail sent to') . ' ';
        $first = true;
        foreach ($vacation['addresses'] as $addr){
            if (!$first) $vacation_str .= ', ';
            $vacation_str .= $addr;
            $first = false;
        }
    }
    if (!empty($vacation['days'])){
        $vacation_str .= ' ' . SmartSieve::text("every %s days",array($vacation['days']));
    }
    $vacation_str .= ' ' . SmartSieve::text('with message "%s"',array($vacation['text']));
    return htmlspecialchars($vacation_str);
}

function setMatchType (&$matchstr, $regex = false)
{
    $match = SmartSieve::text('contains');
    if (preg_match("/\s*!/", $matchstr)) 
        $match = SmartSieve::text('does not contain');
    if (preg_match("/\*|\?/", $matchstr) &&
        !empty($GLOBALS['default']->websieve_auto_matches)){
        $match = SmartSieve::text('matches');
        if (preg_match("/\s*!/", $matchstr))
            $match = SmartSieve::text('does not match');
    }
    if ($regex){
        $match = SmartSieve::text('matches regexp');
        if (preg_match("/\s*!/", $matchstr))
            $match = SmartSieve::text('does not match regexp');
    }
    $matchstr = preg_replace("/^\s*!/","",$matchstr);
    return $match;
}


?>

