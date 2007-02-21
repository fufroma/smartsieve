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
session_set_cookie_params(0, SmartSieve::getConf('cookie_path', ''), SmartSieve::getConf('cookie_domain', ''));
session_name(SmartSieve::getConf('session_name', session_name()));
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

// Get values for this rule.
$ruleID = null;
$rule = array();
// Get form values from POST data.
if (isset($_POST['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    $rule = getRulePOSTValues();
}
// Use values from an existing rule.
elseif (isset($_GET['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    if (isset($script->rules[$ruleID])) {
        $rule = $script->rules[$ruleID];
    }
}
// If using spam mode, look for an existing spam rule.
elseif ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
    $ruleID = getSpamRule();
    if ($ruleID !== null) {
        $rule = $script->rules[$ruleID];
    }
}
// If using forward mode, look for an existing forward rule.
elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
    $ruleID = getForwardRule();
    if ($ruleID !== null) {
        $rule = $script->rules[$ruleID];
    }
}

// Perform actions.

$action = SmartSieve::getFormValue('thisAction');

switch ($action) {

    case ('enable'):
        if (isSane($rule)) {
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
        }
        break;

    case ('disable'):
        if (isSane($rule) === true) {
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
        if (isSane($rule)) {
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
        }
        break;
}


$jsfile = 'rule.js';
$jsonload = '';
$wrap_width = (SmartSieve::getConf('wrap_width')) ? SmartSieve::getConf('wrap_width') : 80;

switch ($mode) {
    case (SMARTSIEVE_RULE_MODE_SPAM):
        $help_url = SmartSieve::getConf('spam_help_url', '');
        $config = SmartSieve::getConf('spam_filter', array());
        $template = '/spam.inc';
        break;
    case (SMARTSIEVE_RULE_MODE_FORWARD):
        $help_url = SmartSieve::getConf('forward_help_url', '');
        $template = '/forward.inc';
        break;
    default:
        $help_url = SmartSieve::getConf('rule_help_url', '');
        $template = '/rule.inc';
        break;
}

include SmartSieve::getConf('include_dir', 'include') . '/common-head.inc';
include SmartSieve::getConf('include_dir', 'include') . '/menu.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common_status.inc';
include SmartSieve::getConf('include_dir', 'include') . $template;
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();


/**
 * Get POST data supplied from the rule edit form.
 *
 * @return array The filter rule
 */
function getRulePOSTValues()
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
    // Remove trailing colon if present.
    if ($rule['field'] && substr($rule['field'], -1) == ':') {
        $rule['field'] = rtrim($rule['field'], ':');
    }
    $rule['field_val'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('field_val'));
    $rule['size'] = SmartSieve::getFormValue('size');
    $rule['continue'] = (SmartSieve::getFormValue('continue')) ? 1 : 0;
    $rule['gthan'] = (SmartSieve::getFormValue('gthan')) ? 2 : 0;
    $rule['anyof'] = (SmartSieve::getFormValue('anyof')) ? 4 : 0;
    $rule['keep'] = (SmartSieve::getFormValue('keep')) ? 8 : 0;
    $rule['stop'] = (SmartSieve::getFormValue('stop')) ? 16 : 0;
    $rule['regexp'] = (SmartSieve::getFormValue('regexp')) ? 128 : 0;
    $rule['unconditional'] = 0;
    if ((!$rule['from'] && !$rule['to'] && !$rule['subject'] &&
       !$rule['field'] && $rule['size'] === '' && 
       $rule['action'] != 'custom') OR
       ($rule['action'] == 'custom' && !preg_match("/^ *(els)?if/i", $rule['action_arg']))) {
        $rule['unconditional'] = 1;
    }
    $rule['flg'] = $rule['continue'] | $rule['gthan'] | $rule['anyof'] | $rule['keep'] | $rule['stop'] | $rule['regexp'];
    return $rule;
}


/**
 * Is this rule sane.
 *
 * Performs basic sanity/integrity checks.
 *
 * @param array $rule Rule values
 * @return boolean true if sane, false if not
 */
function isSane($rule)
{
    $max_field_chars = SmartSieve::getConf('max_field_chars', 500);
    $max_textbox_chars = SmartSieve::getConf('max_textbox_chars', 50000);

    // Check values do not exceed acceptable sizes.
    $conds = array('from', 'to', 'subject', 'field', 'field_val');
    foreach ($conds as $cond) {
        if (strlen($rule[$cond]) > $max_field_chars) {
            SmartSieve::setError(SmartSieve::text('the condition value you supplied is too long. it should not exceed %d characters.', array($max_field_chars)));
            return false;
        }
    }
    if ($rule['action'] == 'address') {
        if (strlen($rule['action_arg']) > $max_field_chars) {
            SmartSieve::setError(SmartSieve::text('the forward address you supplied is too long. it should not exceed %d characters.', array($max_field_chars)));
            return false;
        }
        if (!preg_match("/^[\x21-\x7E]+@([0-9a-zA-Z-]+\.)+[0-9a-zA-Z]{2,}$/i", $rule['action_arg'])) {
            SmartSieve::setError(SmartSieve::text('"%s" is not a valid email address',
                array(htmlspecialchars($rule['action_arg']))));
            return false;
        }
    }
    if ($rule['action'] == 'reject' &&
        strlen($rule['action_arg']) > $max_textbox_chars) {
        SmartSieve::setError(SmartSieve::text('your reject message is too long. it should not exceed %d characters.', array($max_textbox_chars)));
        return false;
    }
    if ($rule['field'] && !$rule['field_val']) {
        SmartSieve::setError(SmartSieve::text("you must supply a value for the field \"%s\".",
            array($rule['field'])));
        return false;
    }
    // Rule must have an action.
    if (!$rule['action'] && !$rule['keep'] && !$rule['stop']) {
        SmartSieve::setError(SmartSieve::text("please supply an action"));
        return false;
    }
    // Actions other than discard, keep and stop must have a corresponding value.
    if ($rule['action'] != 'discard' && !$rule['keep'] && !$rule['stop'] && !$rule['action_arg']) {
        SmartSieve::setError(SmartSieve::text("you must supply an argument for this action"));
        return false;
    }
    // Message size must not contain non-digits.
    if (preg_match("/\D/", $rule['size'])) {
        SmartSieve::setError(SmartSieve::text("message size value must be a positive integer"));
        return false;
    }
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
