<?php
/*
 * $Id$
 *
 * Copyright 2002-2006 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->lib_dir/SmartSieve.lib";
require SmartSieve::getConf('lib_dir', 'lib') . "/Managesieve.php";
require SmartSieve::getConf('lib_dir', 'lib') . "/Script.php";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

// Rule modes.
define("SMARTSIEVE_RULE_MODE_GENERAL", 'general');
define("SMARTSIEVE_RULE_MODE_FORWARD", 'forward');
define("SMARTSIEVE_RULE_MODE_SPAM", 'spam');

SmartSieve::checkAuthentication();

$smartsieve = &$_SESSION['smartsieve'];
$script = &$_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']];

// If not in GUI mode, redirect.
if ($script->mode == 'advanced' || $script->so == false) {
    header('Location: ' . SmartSieve::setUrl('main.php'));
    exit;
}

// What kind of rule are we creating?
$mode = SMARTSIEVE_RULE_MODE_GENERAL;
if (SmartSieve::getFormValue('mode')) {
    switch (SmartSieve::getFormValue('mode')) {
        case (SMARTSIEVE_RULE_MODE_SPAM):
            $mode = SMARTSIEVE_RULE_MODE_SPAM;
            break;
        case (SMARTSIEVE_RULE_MODE_FORWARD):
            $mode = SMARTSIEVE_RULE_MODE_FORWARD;
            break;
    }
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

/* if one of the save, enable etc options was selected from rule.php, 
 * then get the rule values from POST data. if rule selected from main.php 
 * $ruleID will be set in GET data. if $ruleID not set in POST or GET, or 
 * if $script->rules[$ruleID] does not exist, this will be a new rule page.
 */
$ruleID = null;
$rule = array();
if (isset($_POST['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    $rule = getRulePOSTValues($ruleID);
}
elseif (isset($_GET['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    if (isset($script->rules[$ruleID])) {
        $rule = $script->rules[$ruleID];
    }
} elseif ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
    $ruleID = getSpamRule();
    if ($ruleID !== null) {
        $rule = $script->rules[$ruleID];
    }
} elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
    $ruleID = getForwardRule();
    if ($ruleID !== null) {
        $rule = $script->rules[$ruleID];
    }
}

// Perform actions.

$action = SmartSieve::getFormValue('thisAction');

switch ($action) {

    case ('enable'):
        $ret = checkRule($rule);
        if ($ret === true) {
            if (isset($script->rules[$ruleID])){
                $oldrule = $script->rules[$ruleID];
                $script->rules[$ruleID] = $rule;
                $script->rules[$ruleID]['status'] = 'ENABLED';
                // write and save the new script.
                if (!$script->updateScript()) {
                    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                    SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                    $script->rules[$ruleID] = $oldrule;
                } else {
                    SmartSieve::setNotice(SmartSieve::text('rule successfully enabled.'));
                    $rule['status'] = 'ENABLED';
                    if (SmartSieve::getConf('return_after_update') === true) {
                        header('Location: ' . SmartSieve::setUrl('main.php'),true);
                        exit;
                    }
                }
            } else {
                SmartSieve::setError(SmartSieve::text('ERROR: rule does not exist.'));
            }
        } else {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $ret);
        }
        break;

    case ('disable'):
        $ret = checkRule($rule);
        if ($ret === true) {
            if (isset($script->rules[$ruleID])){
                $oldrule = $script->rules[$ruleID];
                $script->rules[$ruleID] = $rule;
                $script->rules[$ruleID]['status'] = 'DISABLED';
                // write and save the new script.
                if (!$script->updateScript()) {
                    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                    SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                    $script->rules[$ruleID] = $oldrule;
                } else {
                    SmartSieve::setNotice(SmartSieve::text('rule successfully disabled.'));
                    $rule['status'] = 'DISABLED';
                    if (SmartSieve::getConf('return_after_update') === true) {
                        header('Location: ' . SmartSieve::setUrl('main.php'),true);
                        exit;
                    }
                }
            } else {
                SmartSieve::setError(SmartSieve::text('ERROR: rule does not exist.'));
            }
        } else {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $ret);
        }
        break;

    case ('delete'):
        if (isset($script->rules[$ruleID])){
            $status = $script->rules[$ruleID]['status'];
            $script->rules[$ruleID]['status'] = 'DELETED';
            // write and save the new script.
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                $script->rules[$ruleID]['status'] = $status;
            } else {
                unset($script->rules[$ruleID]);
                $script->rules = array_values($script->rules);
                SmartSieve::setNotice(SmartSieve::text('Rule successfully deleted.'));
                header('Location: ' . SmartSieve::setUrl('main.php'),true);
                exit;
            }
        } else {
            SmartSieve::setError(SmartSieve::text('ERROR: rule does not exist.'));
        }
        break;

    case ('save'):
        $ret = checkRule($rule);
        if ($ret === true) {
            // if existing rule, update. add new if not.
            if (isset($script->rules[$ruleID])){
                $oldrule = $script->rules[$ruleID];
                $script->rules[$ruleID] = $rule;
            } else {
                if ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
                    array_unshift($script->rules, $rule);
                    $ruleID = 0;
                } else {
                    $ruleID = array_push($script->rules, $rule) - 1;
                }
            }
            // write and save the new script.
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                if (isset($oldrule)) {
                    $script->rules[$ruleID] = $oldrule;
                } else {
                    unset($script->rules[$ruleID]);
                }
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
        break;
}


$jsfile = 'rule.js';
$jsonload = '';
$wrap_width = (SmartSieve::getConf('wrap_width')) ? SmartSieve::getConf('wrap_width') : 80;

switch ($mode) {
    case (SMARTSIEVE_RULE_MODE_SPAM):
        $help_url = ($url = SmartSieve::getConf('spam_help_url')) ? $url : '';
        $config = SmartSieve::getConf('spam_filter', array());
        $template = '/spam.inc';
        break;
    case (SMARTSIEVE_RULE_MODE_FORWARD):
        $help_url = ($url = SmartSieve::getConf('forward_help_url')) ? $url : '';
        $template = '/forward.inc';
        break;
    default:
        $help_url = ($url = SmartSieve::getConf('rule_help_url')) ? $url : '';
        $template = '/rule.inc';
        break;
}

include SmartSieve::getConf('include_dir', 'include') . '/common-head.inc';
include SmartSieve::getConf('include_dir', 'include') . '/menu.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common_status.inc';
include SmartSieve::getConf('include_dir', 'include') . $template;
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();


/* if rule values supplied from form on rule.php, get rule values 
 * from POST data.
 */
function getRulePOSTValues ($ruleID)
{
    $rule = array();
    $rule['priority'] = SmartSieve::getFormValue('priority');
    $rule['status'] = SmartSieve::getFormValue('status');
    $rule['from'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('from'));
    $rule['to'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('to'));
    $rule['subject'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('subject'));
    $rule['action'] = SmartSieve::getFormValue('action');
    $rule['action_arg'] = SmartSieve::getFormValue($rule['action']);
    if ($rule['action'] != 'folder') {
        $rule['action_arg'] = SmartSieve::utf8Encode($rule['action_arg']);
    }
    $rule['field'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('field'));
    $rule['field_val'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('field_val'));
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
    if ((!$rule['from'] && !$rule['to'] && !$rule['subject'] &&
       !$rule['field'] && $rule['size'] === '' && 
       $rule['action'] != 'custom') OR
       ($rule['action'] == 'custom' && !preg_match("/^ *(els)?if/i", $rule['action_arg']))) {
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
    if (!$rule['action'] && !$rule['keep'])
        return SmartSieve::text("please supply an action");
    if ($rule['action'] != 'discard' && !$rule['keep'] && !$rule['action_arg'])
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

/**
 * Search for existing forward rule.
 *
 * @return mixed Matching rule array index if one exists, or null if not
 */
function getForwardRule()
{
    for ($i=0;$i<count($GLOBALS['script']->rules);$i++) {
        $rule = $GLOBALS['script']->rules[$i];
        if ($rule['action'] == 'address' && !$rule['from'] && !$rule['to'] &&
            !$rule['subject'] && !$rule['field'] && !$rule['size']) {
            return $i;
        }
    }
    return null;
}

/**
 * Search for existing spam filter rule.
 *
 * This will search for a rule matching the settings
 * @return mixed Matching rule array index if one exists, or null if not
 */
function getSpamRule()
{
    $config = SmartSieve::getConf('spam_filter');
    if ($config === false ||
        !is_array($config) ||
        !isset($config['header']) ||
        !isset($config['value'])) {
        return null;
    }
    for ($i=0;$i<count($GLOBALS['script']->rules);$i++) {
        $rule = $GLOBALS['script']->rules[$i];
        if ($rule['field'] == $config['header'] &&
            $rule['field_val'] == $config['value'] &&
            !$rule['from'] && !$rule['to'] &&
            !$rule['subject'] && !$rule['size']) {
            return $i;
        }
    }
    return null;
}

?>
