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

// Get the list of mailboxes for this user.
// Set it in the session so we only do this once per login.
if (!isset($_SESSION['smartsieve']['mailboxes'])) {
    $_SESSION['smartsieve']['mailboxes'] = array();
    $mboxes = SmartSieve::getMailboxList();
    if (is_array($mboxes)) {
        $_SESSION['smartsieve']['mailboxes'] = $mboxes;
    } else {
        SmartSieve::setError(SmartSieve::text('ERROR: ') . $mboxes);
        SmartSieve::writeToLog(sprintf('failed getting mailbox list for %s from %s: %s', 
            $_SESSION['smartsieve']['auth'], $_SESSION['smartsieve']['server'], $mboxes), LOG_ERR);
    }
}

$ruleID = null;   /* rule number. */
$rule = null;     /* sieve rule $script->rules[$ruleID]. */

/* if one of the save, enable etc options was selected from rule.php, 
 * then get the rule values from POST data. if rule selected from main.php 
 * $ruleID will be set in GET data. if $ruleID not set in POST or GET, or 
 * if $script->rules[$ruleID] does not exist, this will be a new rule page.
 */
if (isset($_POST['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    $rule = getRulePOSTValues($ruleID);
}
elseif (isset($_GET['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    if (isset($script->rules[$ruleID])) {
        $rule = $script->rules[$ruleID];
    }
}

/* save rule changes if requested. */

$action = SmartSieve::getFormValue('thisAction');

if ($action == 'enable') 
{
    if (isset($script->rules[$ruleID])){
        $script->rules[$ruleID]['status'] = 'ENABLED';
	// write and save the new script.
	if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
	} else {
            SmartSieve::setNotice(SmartSieve::text('rule successfully enabled.'));
            if (SmartSieve::getConf('return_after_update') === true) {
                header('Location: ' . SmartSieve::setUrl('main.php'),true);
                exit;
            }
            $rule['status'] = 'ENABLED';
	}
    } else {
        SmartSieve::setError(SmartSieve::text('ERROR: rule does not exist.'));
    }
}
if ($action == 'disable') 
{
    if (isset($script->rules[$ruleID])){
        $script->rules[$ruleID]['status'] = 'DISABLED';
	// write and save the new script.
	if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
	} else {
            SmartSieve::setNotice(SmartSieve::text('rule successfully disabled.'));
            if (SmartSieve::getConf('return_after_update') === true) {
                header('Location: ' . SmartSieve::setUrl('main.php'),true);
                exit;
            }
            $rule['status'] = 'DISABLED';
	}
    } else {
        SmartSieve::setError(SmartSieve::text('ERROR: rule does not exist.'));
    }
}
if ($action == 'delete') 
{
    if (isset($script->rules[$ruleID])){
        $script->rules[$ruleID]['status'] = 'DELETED';
	// write and save the new script.
	if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
	} else {
            SmartSieve::setNotice(SmartSieve::text('Rule successfully deleted.'));
	    header('Location: ' . SmartSieve::setUrl('main.php'),true);
	    exit;
	}
    } else {
        SmartSieve::setError(SmartSieve::text('ERROR: rule does not exist.'));
    }
}
if ($action == 'save') 
{
    $ret = checkRule($rule);
    if ($ret === true){    /* rule passed sanity checks */

        // if existing rule, update. add new if not.
	if (isset($script->rules[$ruleID])){
	    $script->rules[$ruleID] = $rule;
	} else {
	    $ruleID = array_push($script->rules, $rule) - 1;
        }
	// write and save the new script.
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


$jsfile = 'rule.js';
$jsonload = '';
if (!empty($default->rule_help_url)){
    $help_url = $default->rule_help_url;
} else {
    $help_url = '';
}
$wrap_width = (SmartSieve::getConf('wrap_width')) ? SmartSieve::getConf('wrap_width') : 80;

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/menu.inc';
include $default->include_dir . '/common_status.inc';
include $default->include_dir . '/rule.inc';

SmartSieve::close();


/* if rule values supplied from form on rule.php, get rule values 
 * from POST data.
 */
function getRulePOSTValues ($ruleID)
{
    $rule = array();
    $rule['priority'] = SmartSieve::getFormValue('priority');
    $rule['status'] = SmartSieve::getFormValue('status');
    $rule['from'] = SmartSieve::getFormValue('from');
    $rule['to'] = SmartSieve::getFormValue('to');
    $rule['subject'] = SmartSieve::getFormValue('subject');
    $rule['action'] = SmartSieve::getFormValue('action');
    $rule['action_arg'] = SmartSieve::getFormValue($rule['action']);
    $rule['field'] = SmartSieve::getFormValue('field');
    $rule['field_val'] = SmartSieve::getFormValue('field_val');
    $rule['size'] = SmartSieve::getFormValue('size');
    $rule['continue'] = 0;
    if (SmartSieve::getFormValue('continue')) $rule['continue'] = 1;
    $rule['gthan'] = 0;
    if (SmartSieve::getFormValue('gthan')) $rule['gthan'] = 2;
    $rule['anyof'] = 0;
    if (SmartSieve::getFormValue('anyof')) $rule['anyof'] = 4;
    $rule['keep'] = 0;
    if (SmartSieve::getFormValue('keep')) $rule['keep'] = 8;
    $rule['regexp'] = 0;
    if (SmartSieve::getFormValue('regexp')) $rule['regexp'] = 128;
    $rule['unconditional'] = 0;
    if (!$rule['from'] && !$rule['to'] && !$rule['subject'] &&
       !$rule['field'] && !$rule['size'] && $rule['action'] &&
       !($rule['action'] == 'custom' && preg_match("/^ *(els)?if/i", $rule['action_arg']))) {
       $rule['unconditional'] = 1;
    }
    $rule['flg'] = $rule['continue'] | $rule['gthan'] | $rule['anyof'] | $rule['keep'] | $rule['regexp'];

    return $rule;
}


function checkRule(&$rule)
{
    /* check values do not exceed acceptible sizes. */
    $conds = array('from','to','subject','field','field_val');
    foreach ($conds as $cond) {
        if (strlen($rule[$cond]) > SmartSieve::getConf('max_field_chars', 50)) {
	    return SmartSieve::text('the condition value you supplied is too long. it should not exceed %d characters.', array(SmartSieve::getConf('max_field_chars', 50)));
        }
    }
    if ($rule['action'] == 'address' &&
        strlen($rule['action_arg']) > SmartSieve::getConf('max_field_chars', 50)) {
	return SmartSieve::text('the forward address you supplied is too long. it should not exceed %d characters.', array(SmartSieve::getConf('max_field_chars', 50)));
    }
    if ($rule['action'] == 'reject' &&
        strlen($rule['action_arg']) > SmartSieve::getConf('max_textbox_chars', 500)) {
        return SmartSieve::text('your reject message is too long. it should not exceed %d characters.', array(SmartSieve::getConf('max_textbox_chars', 500)));
    }

    if ($rule['field'] && !$rule['field_val'])
        return SmartSieve::text("you must supply a value for the field \"%s\".", array($rule['field']));
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

    return true;
}


?>
