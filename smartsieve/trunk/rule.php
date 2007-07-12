<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
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
define("SMARTSIEVE_RULE_MODE_CUSTOM", 'custom');

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
        case (SMARTSIEVE_RULE_MODE_CUSTOM):
            $mode = SMARTSIEVE_RULE_MODE_CUSTOM;
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
        SmartSieve::log(sprintf('failed getting mailbox list for %s from %s: %s', 
            $_SESSION['smartsieve']['auth'], $_SESSION['smartsieve']['server']['host'], $mboxes), LOG_ERR);
    }
}

// Get values for this rule.
$ruleID = null;
$display = array('priority' => $script->pcount+1,
                 'status' => 'ENABLED',
                 'startNewBlock' => false,
                 'useRegex' => false,
                 'matchAny' => false,
                 'keep' => false,
                 'conditions' => array(array('type' => 'new')),
                 'usedConditions' => array(),
                 'action' => array('type' => 'folder',
                                   'folder' => '',
                                   'address' => '',
                                   'message' => '',
                                   'sieve' => '',
                                   'custom' => ''
                                  ),
                 'keep' => false,
                 'stop' => false,
                 'flg' => 0
                );
// Get form values from POST data.
if (isset($_POST['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    $display = getPOSTValues();
}
// Use values from an existing rule.
elseif (isset($_GET['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    if (isset($script->rules[$ruleID])) {
        $display = getDisplayValues($script->rules[$ruleID]);
    }
}
// If using spam mode, look for an existing spam rule.
elseif ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
    $ruleID = getSpamRule();
var_dump($ruleID);
    if ($ruleID !== null) {
        $display = getDisplayValues($script->rules[$ruleID]);
    }
}
// If using forward mode, look for an existing forward rule.
elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
    $ruleID = getForwardRule();
    if ($ruleID !== null) {
        $display = getDisplayValues($script->rules[$ruleID]);
    }
}

// Perform actions.

$action = SmartSieve::getFormValue('thisAction');

switch ($action) {

    case ('enable'):
        $rule = buildRule($display);
        if (isSane($rule)) {
            if (isset($script->rules[$ruleID])){
                $oldrule = $script->rules[$ruleID];
                $script->rules[$ruleID] = $rule;
                $script->rules[$ruleID]['status'] = 'ENABLED';
                // write and save the new script.
                if (!$script->updateScript()) {
                    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                    SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
                        $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                    $script->rules[$ruleID] = $oldrule;
                } else {
                    SmartSieve::setNotice(SmartSieve::text('rule successfully enabled.'));
                    $display['status'] = 'ENABLED';
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
        $rule = buildRule($display);
        if (isSane($rule) === true) {
            if (isset($script->rules[$ruleID])){
                $oldrule = $script->rules[$ruleID];
                $script->rules[$ruleID] = $rule;
                $script->rules[$ruleID]['status'] = 'DISABLED';
                // write and save the new script.
                if (!$script->updateScript()) {
                    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                    SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
                        $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                    $script->rules[$ruleID] = $oldrule;
                } else {
                    SmartSieve::setNotice(SmartSieve::text('rule successfully disabled.'));
                    $display['status'] = 'DISABLED';
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
                SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
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
        $rule = buildRule($display);
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
                SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
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
    case (SMARTSIEVE_RULE_MODE_CUSTOM):
        $help_url = SmartSieve::getConf('custom_help_url', '');
        $template = '/custom.inc';
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
 * Get values from the rule edit form.
 *
 * @return
 */
function getPOSTValues()
{
    $display = array();
    $display['priority'] = SmartSieve::getPOST('priority', '0');
    $display['status'] = SmartSieve::getPOST('status', 'ENABLED');
    $display['startNewBlock'] = (SmartSieve::getPOST('continue')) ? true : false;
    $display['useRegex'] = (SmartSieve::getPOST('regexp')) ? true : false;
    $display['matchAny'] = (SmartSieve::getPOST('anyof')) ? true : false;
    $conditions = array();
    $usedConditions = array();
    $i = 0;
    while (($type = SmartSieve::getPOST('condition' . $i)) !== null) {
        $values = array();
        switch ($type) {
            case ('new'):
                break;
            case ('header'):
                $values['header'] = SmartSieve::getPOST('field'.$i);
                $values['matchStr'] = SmartSieve::getPOST('field_val'.$i);
                break;
            case ('size'):
                $values['gthan'] = (SmartSieve::getPOST('gthan')) ? true : false;
                $values['size'] = SmartSieve::getPOST('size');
                break;
            case ('from');
            case ('to');
            case ('subject');
            default:
                $values['matchStr'] = SmartSieve::getPOST($type.$i);
                break;
        }
        // If delete value set, ignore this condition.
        if (SmartSieve::getPOST('delete' . $i++) == '1' || $type == 'new') {
            continue;
        }
        $values['type'] = $type;
        $conditions[] = $values;
        $usedConditions[] = $type;
    }
    $conditions[] = array('type' => 'new');
    $display['conditions'] = $conditions;
    $display['usedConditions'] = $usedConditions;
    $action = array();
    $action['type'] = SmartSieve::getPOST('action');
    foreach (array('folder', 'address', 'message', 'sieve') as $a) {
        $action[$a] = '';
    }
    $action['folder'] = SmartSieve::getPOST('folder', '');
    $action['address'] = SmartSieve::getPOST('address');
    $action['message'] = SmartSieve::getPOST('reject');
    $action['sieve'] = SmartSieve::getPOST('custom');
    $display['action'] = $action;
    $display['keep'] = (SmartSieve::getPOST('keep')) ? true : false;
    $display['stop'] = (SmartSieve::getPOST('stop')) ? true : false;
    $display['flg'] = SmartSieve::getPOST('flg', '0');
    return $display;
}

/**
 * Get dislay values for an existing rule.
 *
 * @param array $rule The rule to display
 * @return Array containing values to display
 */
function getDisplayValues($rule)
{
    $display = array();
    $display['priority'] = (isset($rule['priority'])) ? $rule['priority'] : $script->pcount+1;
    $display['status'] = (isset($rule['status']) && $rule['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';
    $display['startNewBlock'] = (empty($rule['continue'])) ? false : true;
    $display['useRegex'] = (empty($rule['regexp'])) ? false : true;
    $display['matchAny'] = (empty($rule['anyof'])) ? false : true;
    $conditions = array();
    $usedConditions = array();
    if (!empty($rule['from'])) {
        foreach ($rule['from'] as $from) {
            $conditions[] = array('type' => 'from', 'matchStr' => SmartSieve::utf8Decode($from));
            $usedConditions[] = 'from';
        }
    } if (!empty($rule['to'])) {
        foreach ($rule['to'] as $to) {
            $conditions[] = array('type' => 'to', 'matchStr' => SmartSieve::utf8Decode($to));
            $usedConditions[] = 'to';
        }
    } if (!empty($rule['subject'])) {
        foreach ($rule['subject'] as $subject) {
            $conditions[] = array('type' => 'subject', 'matchStr' => SmartSieve::utf8Decode($subject));
            $usedConditions[] = 'subject';
        }
    } if (!empty($rule['size'])) {
        $conditions[] = array('type' => 'size', 'size' => $rule['size'], 'gthan' => (empty($rule['gthan'])) ? false : true);
        $usedConditions[] = 'size';
    } if (!empty($rule['field'])) {
        for ($i=0; $i<count($rule['field']); $i++) {
            $conditions[] = array('type' => 'header', 'header' => SmartSieve::utf8Decode($rule['field'][$i]),
                                  'matchStr' => (isset($rule['field_val'][$i])) ? SmartSieve::utf8Decode($rule['field_val'][$i]) : '');
            $usedConditions[] = 'header';
        }
    }
    $conditions[] = array('type' => 'new');
    $display['conditions'] = $conditions;
    $display['usedConditions'] = $usedConditions;
    $action = array();
    $type = (isset($rule['action'])) ? $rule['action'] : '';
    switch ($type) {
        case ('folder'):
            $action['type'] = 'folder';
            $action['folder'] = $rule['action_arg'];
            break;
        case ('address'):
            $action['type'] = 'address';
            $action['address'] = SmartSieve::utf8Decode($rule['action_arg']);
            break;
        case ('reject'):
            $action['type'] = 'reject';
            $action['message'] = SmartSieve::utf8Decode($rule['action_arg']);
            break;
        case ('discard'):
            $action['type'] = 'discard';
            break;
        case ('custom'):
            $action['type'] = 'custom';
            $action['sieve'] = SmartSieve::utf8Decode($rule['action_arg']);
            $GLOBALS['mode'] = SMARTSIEVE_RULE_MODE_CUSTOM;
            break;
    }
    $display['action'] = $action;
    $display['keep'] = (empty($rule['keep'])) ? false : true;
    $display['stop'] = (empty($rule['stop'])) ? false : true;
    $display['flg'] = (isset($rule['flg'])) ? $rule['flg'] : '';
    return $display;
}

/**
 * Get POST data supplied from the rule edit form.
 *
 * @return array The filter rule
 */
function buildRule($display)
{
    $rule = array();
    $rule['priority'] = $display['priority'];
    $rule['status'] = $display['status'];
    foreach (array('from', 'to', 'subject', 'field', 'field_val') as $cond) {
        $rule[$cond] = array();
    }
    $rule['size'] = '';
    foreach ($display['conditions'] as $cond) {
        switch ($cond['type']) {
            case ('new'):
                break;
            case ('header'):
                $field = SmartSieve::utf8Encode($cond['header']);
                if (substr($field, -1) == ':') {
                    $field = rtrim($field, ':');
                }
                $rule['field'][] = SmartSieve::utf8Encode($field);
                $rule['field_val'][] = SmartSieve::utf8Encode($cond['matchStr']);
                break;
            case ('size'):
                $rule['gthan'] = ($cond['gthan'] == true) ? 2 : 0;
                $rule['size'] = $cond['size'];
                break;
            case ('from');
            case ('to');
            case ('subject');
            default:
                $rule[$cond['type']][] = SmartSieve::utf8Encode($cond['matchStr']);
                break;
        }
    }
    $action = $display['action'];
    $rule['action'] = $action['type'];
    $rule['action_arg'] = '';
    switch ($action['type']) {
        case ('folder'):
            $rule['action_arg'] = $action['folder'];
            break;
        case ('address'):
            $rule['action_arg'] = SmartSieve::utf8Encode($action['address']);
            break;
        case ('discard'):
            break;
        case ('reject'):
            $rule['action_arg'] = SmartSieve::utf8Encode($action['message']);
            break;
        case ('custom'):
            $rule['action_arg'] = SmartSieve::utf8Encode($action['sieve']);
            break;
    }
    $rule['continue'] = ($display['startNewBlock'] == true) ? 1 : 0;
    $rule['gthan'] = (isset($rule['gthan'])) ?  $rule['gthan'] : 0;
    $rule['anyof'] = ($display['matchAny'] == true) ? 4 : 0;
    $rule['keep'] = ($display['keep'] == true) ? 8 : 0;
    $rule['stop'] = ($display['stop'] == true) ? 16 : 0;
    $rule['regexp'] = ($display['useRegex'] == true) ? 128 : 0;
    $rule['unconditional'] = 0;
    if ((empty($rule['from']) && empty($rule['to']) && empty($rule['subject']) &&
       empty($rule['field']) && $rule['size'] === '' &&
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
        for ($i=0; $i<count($rule[$cond]); $i++) {
            if (strlen($rule[$cond][$i]) > $max_field_chars) {
                SmartSieve::setError(SmartSieve::text('the condition value you supplied is too long. it should not exceed %d characters.', array($max_field_chars)));
                return false;
            }
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
    for ($i=0; $i<count($rule['field']); $i++) {
        if (!isset($rule['field_val'][$i]) || !$rule['field_val'][$i]) {
            SmartSieve::setError(SmartSieve::text("you must supply a value for the field \"%s\".",
                array($rule['field'][$i])));
            return false;
        }
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
        if ($rule['action'] == 'address' && empty($rule['from']) && empty($rule['to']) &&
            empty($rule['subject']) && empty($rule['field']) && !$rule['size']) {
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
        if (count($rule['field']) == 1 && $rule['field'][0] == $config['header'] &&
            $rule['field_val'][0] == $config['value'] &&
            empty($rule['from']) && empty($rule['to']) &&
            empty($rule['subject']) && !$rule['size']) {
            return $i;
        }
    }
    return null;
}

?>
