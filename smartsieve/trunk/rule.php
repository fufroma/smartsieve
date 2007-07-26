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
define("SMARTSIEVE_RULE_MODE_WHITELIST", 'whitelist');
define("SMARTSIEVE_RULE_MODE_VACATION", 'vacation');

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
        case (SMARTSIEVE_RULE_MODE_WHITELIST):
            $mode = SMARTSIEVE_RULE_MODE_WHITELIST;
            break;
        case (SMARTSIEVE_RULE_MODE_VACATION):
            $mode = SMARTSIEVE_RULE_MODE_VACATION;
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
$rule = array('status' => 'ENABLED',
              'control' => CONTROL_ELSEIF,
              'matchAny' => 0,
              'conditions' => array(),
              'actions' => array(),
              );
// Get form values from POST data.
if (isset($_POST['ruleID'])) {
    $ruleID = SmartSieve::getFormValue('ruleID');
    $rule = getPOSTValues();
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
    $ruleID = 'spam';
    if (!empty($script->spamRule)) {
        $rule = $script->spamRule;
    }
}
// If using forward mode, look for an existing forward rule.
elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
    $ruleID = 'forward';
    if (!empty($script->forwardRule)) {
        $rule = $script->forwardRule;
    }
} elseif ($mode == SMARTSIEVE_RULE_MODE_VACATION) {
    $ruleID = 'vacation';
    if (!empty($script->vacation)) {
        $rule = $script->vacation;
    }
}
// Check if this is a custom rule.
foreach ($rule['actions'] as $action) {
    if ($action['type'] == ACTION_CUSTOM) {
        $mode = SMARTSIEVE_RULE_MODE_CUSTOM;
    }
}

// Perform actions.

$action = SmartSieve::getFormValue('thisAction');

switch ($action) {

    case ('enable'):
        if (isSane($rule)) {
            $rule['status'] = 'ENABLED';
            if ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
                $script->spamRule = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
                $script->forwardRule = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_CUSTOM) {
            } elseif ($mode == SMARTSIEVE_RULE_MODE_WHITELIST) {
                $script->whitelist = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_VACATION) {
                $script->vacation = $rule;
            } else {
                if (isset($script->rules[$ruleID])) {
                    $script->saveRule($rule, $ruleID);
                } else {
                    $ruleID = $script->addRule($rule, (int)SmartSieve::getPOST('position'));
                }
            }
            // write and save the new script.
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
            } else {
                SmartSieve::setNotice(SmartSieve::text('rule successfully enabled.'));
                if (SmartSieve::getConf('return_after_update') === true) {
                    header('Location: ' . SmartSieve::setUrl('main.php'),true);
                    exit;
                }
            }
        }
        break;

    case ('disable'):
        if (isSane($rule) === true) {
            $rule['status'] = 'DISABLED';
            if ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
                $script->spamRule = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
                $script->forwardRule = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_CUSTOM) {
            } elseif ($mode == SMARTSIEVE_RULE_MODE_WHITELIST) {
                $script->whitelist = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_VACATION) {
                $script->vacation = $rule;
            } else {
                if (isset($script->rules[$ruleID])) {
                    $script->saveRule($rule, $ruleID);
                } else {
                    $ruleID = $script->addRule($rule, (int)SmartSieve::getPOST('position'));
                }
            }
            // write and save the new script.
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
            } else {
                SmartSieve::setNotice(SmartSieve::text('rule successfully disabled.'));
                if (SmartSieve::getConf('return_after_update') === true) {
                    header('Location: ' . SmartSieve::setUrl('main.php'),true);
                    exit;
                }
            }
        }
        break;

    case ('delete'):
        $deleted = false;
        if ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
            $script->spamRule = array();
            $deleted = true;
        } elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
            $script->forwardRule = array();
            $deleted = true;
        } elseif ($mode == SMARTSIEVE_RULE_MODE_CUSTOM) {
        } elseif ($mode == SMARTSIEVE_RULE_MODE_WHITELIST) {
            $script->whitelist = array();
            $deleted = true;
        } elseif ($mode == SMARTSIEVE_RULE_MODE_VACATION) {
            $script->vacation = array();
            $deleted = true;
        } else {
            if (isset($script->rules[$ruleID])){
                $deleted = $script->deleteRule($ruleID);
            }
        }
        if ($deleted == true) {
            // write and save the new script.
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
            } else {
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
            if ($mode == SMARTSIEVE_RULE_MODE_SPAM) {
                $script->spamRule = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_FORWARD) {
                $script->forwardRule = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_CUSTOM) {
            } elseif ($mode == SMARTSIEVE_RULE_MODE_WHITELIST) {
                $script->whitelist = $rule;
            } elseif ($mode == SMARTSIEVE_RULE_MODE_VACATION) {
                $script->vacation = $rule;
            } else {
                if (isset($script->rules[$ruleID])) {
                    $script->saveRule($rule, $ruleID);
                } else {
                    $ruleID = $script->addRule($rule, (int)SmartSieve::getPOST('position'));
                }
            }

            // write and save the new script.
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                SmartSieve::log(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
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
$sizeUsed = false;
foreach ($rule['conditions'] as $condition) {
    if ($condition['type'] == TEST_SIZE) {
        $sizeUsed = true;
    }
}
// Add dummy condition and action for "Add action" widgits.
$rule['conditions'][] = array('type' => 'new');
$rule['actions'][] = array('type' => 'new');

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
    case (SMARTSIEVE_RULE_MODE_VACATION):
        $help_url = SmartSieve::getConf('vacation_help_url', '');
        $template = '/vacation.inc';
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
 * @return array Rule values
 */
function getPOSTValues()
{
    $rule = array();
    $rule['status'] = SmartSieve::getPOST('status', 'ENABLED');
    $rule['control'] = SmartSieve::getPOST('control');
    $rule['matchAny'] = SmartSieve::getPOST('anyof');
    $rule['conditions'] = array();
    $i = 0;
    while (($type = SmartSieve::getPOST('condition' . $i)) !== null) {
        $condition = array();
        switch ($type) {
            case ('new'):
                break;
            case ('from'):
                $condition['type'] = TEST_ADDRESS;
                $condition['header'] = 'from';
                $condition['matchStr'] = SmartSieve::utf8Encode(SmartSieve::getPOST('from'.$i));
                break;
            case ('to'):
                $condition['type'] = TEST_ADDRESS;
                $condition['header'] = 'to';
                $condition['matchStr'] = SmartSieve::utf8Encode(SmartSieve::getPOST('to'.$i));
                break;
            case ('subject'):
                $condition['type'] = TEST_HEADER;
                $condition['header'] = 'subject';
                $condition['matchStr'] = SmartSieve::utf8Encode(SmartSieve::getPOST('subject'.$i));
                break;
            case ('size'):
                $condition['type'] = TEST_SIZE;
                $condition['gthan'] = SmartSieve::getPOST('gthan');
                $condition['kbytes'] = SmartSieve::getPOST('size');
                break;
            case ('header'):
                $condition['type'] = TEST_HEADER;
                $condition['header'] = SmartSieve::utf8Encode(SmartSieve::getPOST('header'.$i));
                $condition['matchStr'] = SmartSieve::utf8Encode(SmartSieve::getPOST('headerMatchStr'.$i));
                break;
        }
        if ($type != 'new' && ($condition['type'] == TEST_ADDRESS || $condition['type'] == TEST_HEADER)) {
            $matchType = SmartSieve::getPOST('matchType'.$i);
            switch ($matchType) {
                case ('is'):
                    $condition['matchType'] = MATCH_IS;
                    break;
                case ('notis'):
                    $condition['matchType'] = MATCH_IS;
                    $condition['not'] = true;
                    break;
                case ('matches'):
                    $condition['matchType'] = MATCH_MATCHES;
                    break;
                case ('notmatches'):
                    $condition['matchType'] = MATCH_MATCHES;
                    $condition['not'] = true;
                    break;
                case ('regex'):
                    $condition['matchType'] = MATCH_REGEX;
                    break;
                case ('notregex'):
                    $condition['matchType'] = MATCH_REGEX;
                    $condition['not'] = true;
                    break;
                case ('notcontains'):
                    $condition['matchType'] = MATCH_CONTAINS;
                    $condition['not'] = true;
                    break;
                case ('contains'):
                default:
                    $condition['matchType'] = MATCH_CONTAINS;
                    break;
            }
        }
        // If delete value set, ignore this condition.
        if (SmartSieve::getPOST('delete' . $i++) == '1' || $type == 'new') {
            continue;
        }
        $rule['conditions'][] = $condition;
    }
    $rule['actions'] = array();
    $i = 0;
    while (($type = SmartSieve::getPOST('action' . $i)) !== null) {
        $action = array();
        switch ($type) {
            case ('new'):
                break;
            case (ACTION_FILEINTO):
                $action['type'] = ACTION_FILEINTO;
                $action['folder'] = SmartSieve::getPOST(ACTION_FILEINTO . $i);
                break;
            case (ACTION_REDIRECT):
                $action['type'] = ACTION_REDIRECT;
                $action['address'] = SmartSieve::utf8Encode(SmartSieve::getPOST(ACTION_REDIRECT . $i));
                break;
            case (ACTION_REJECT):
                $action['type'] = ACTION_REJECT;
                $action['message'] = SmartSieve::utf8Encode(SmartSieve::getPOST(ACTION_REJECT . $i));
                break;
            case (ACTION_DISCARD):
                $action['type'] = ACTION_DISCARD;
                break;
            case (ACTION_KEEP):
                $action['type'] = ACTION_KEEP;
                break;
            case (ACTION_STOP):
                $action['type'] = ACTION_STOP;
                break;
            case (ACTION_CUSTOM):
                $action['type'] = ACTION_CUSTOM;
                $action['sieve'] = SmartSieve::utf8Encode(SmartSieve::getPOST('sieve'));
                break;
            case (ACTION_VACATION):
                $action['type'] = ACTION_VACATION;
                $action['message'] = SmartSieve::utf8Encode(SmartSieve::getPOST('message'.$i));
                $action['days'] = SmartSieve::getPOST('days'.$i);
                $addrs = SmartSieve::getFormValue('address'.$i);
                $addresses = array();
                if (is_array($addrs)) {
                    foreach ($addrs as $addr) {
                        $addresses[] = SmartSieve::utf8Encode($addr);
                    }
                }
                $newAddrs = SmartSieve::utf8Encode(SmartSieve::getFormValue('newaddresses'.$i));
                $newAddrs = preg_replace("/\"|\\\/", "", $newAddrs);
                $addrs = preg_split("/\s*,\s*|\s+/", $newAddrs);
                foreach ($addrs as $addr) {
                    if (!empty($addr)) {
                        $addresses[] = $addr;
                    }
                }
                $action['addresses'] = array_unique($addresses);
                break;
        }
        // If delete value set, ignore this condition.
        if (SmartSieve::getPOST('deleteAction' . $i++) == '1' || $type == 'new') {
            continue;
        }
        $rule['actions'][] = $action;
    }
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

    // Condition checks.

    foreach ($rule['conditions'] as $condition) {
        // Check values do not exceed acceptable sizes.
        if ($condition['type'] == TEST_ADDRESS || $condition['type'] == TEST_HEADER) {
            if (($condition['type'] == TEST_HEADER && strlen($condition['header']) > $max_field_chars) ||
                strlen($condition['matchStr']) > $max_field_chars) {
                SmartSieve::setError(SmartSieve::text('the condition value you supplied is too long. it should not exceed %d characters.', array($max_field_chars)));
                return false;
            }
        }
        if ($condition['type'] == TEST_SIZE) {
            // Message size must not contain non-digits.
            if (preg_match("/\D/", $condition['kbytes'])) {
                SmartSieve::setError(SmartSieve::text("message size value must be a positive integer"));
                return false;
            }
        }
        if ($condition['type'] == TEST_HEADER) {
            if (empty($condition['header'])) {
                SmartSieve::setError(SmartSieve::text('Please supply a message header to match on'));
                return false;
            }
            if (empty($condition['matchStr'])) {
                SmartSieve::setError(SmartSieve::text("you must supply a value for the field \"%s\".",
                    array($rule['field'][$i])));
                return false;
            }
        }
    }

    // Action checks.

    // Rule must have an action.
    if (empty($rule['actions'])) {
        SmartSieve::setError(SmartSieve::text("please supply an action"));
        return false;
    }
    foreach ($rule['actions'] as $action) {
        // Actions that require a value.
        if (($action['type'] == ACTION_FILEINTO && empty($action['folder'])) ||
            ($action['type'] == ACTION_REDIRECT && empty($action['address'])) ||
            ($action['type'] == ACTION_REJECT && empty($action['message'])) ||
            ($action['type'] == ACTION_VACATION && empty($action['message'])) ||
            ($action['type'] == ACTION_CUSTOM && empty($action['sieve']))) {
            SmartSieve::setError(SmartSieve::text("you must supply an argument for this action"));
            return false;
        }
        if ($action['type'] == ACTION_REDIRECT) {
            if (strlen($action['address']) > $max_field_chars) {
                SmartSieve::setError(SmartSieve::text('the forward address you supplied is too long. it should not exceed %d characters.', array($max_field_chars)));
                return false;
            }
            if (!preg_match("/^[\x21-\x7E]+@([0-9a-zA-Z-]+\.)+[0-9a-zA-Z]{2,}$/i", $action['address'])) {
            SmartSieve::setError(SmartSieve::text('"%s" is not a valid email address',
                array(htmlspecialchars($action['address']))));
                return false;
            }
        }
        if ($action['type'] == ACTION_REJECT) {
            if (strlen($action['message']) > $max_textbox_chars) {
                SmartSieve::setError(SmartSieve::text('your reject message is too long. it should not exceed %d characters.', array($max_textbox_chars)));
                return false;
            }
        }
        if ($action['type'] == ACTION_VACATION) {
            if (strlen($action['message']) > $max_textbox_chars) {
                SmartSieve::setError(SmartSieve::text('Your vacation message must not exceed %d characters',
                    array($max_textbox_chars)));
                return false;
            }
            foreach ($action['addresses'] as $addr) {
                if (!preg_match("/^[\x21-\x7E]+@([0-9a-zA-Z-]+\.)+[0-9a-zA-Z]{2,}$/i", $addr)) {
                    SmartSieve::setError(SmartSieve::text('"%s" is not a valid email address',
                        array(htmlspecialchars($addr))));
                    return false;
                }
            }
            if (!is_numeric($action['days'])) {
                SmartSieve::setError(SmartSieve::text('vacation days must be a positive integer'));
                return false;
            }
        }
    }

    // All values sane.
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
        $conditions = $GLOBALS['script']->rules[$i]['conditions'];
        $actions = $GLOBALS['script']->rules[$i]['actions'];
        if (count($actions) === 1 && $actions[0]['type'] == ACTION_REDIRECT && empty($conditions)) {
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
        $conditions = $GLOBALS['script']->rules[$i]['conditions'];
        $actions = $GLOBALS['script']->rules[$i]['actions'];
        if (count($conditions) == 1 && $conditions[0]['type'] == TEST_HEADER &&
            $conditions[0]['header'] == $config['header'] && $conditions[0]['headerMatchStr'] == $config['value']) {
            return $i;
        }
    }
    return null;
}

/**
 * Format an array of vacation addresses for the vacation widget.
 *
 * The full list of addresses will include any set in an existing vacation
 * action, plus those returned by get_email_addresses_hook, if set.
 *
 * @param array $addresses List of addresses set in existing vacation object
 * @return array Formatted array of all addresses
 */
function getAllAddresses($addresses)
{
    $all = array();
    if (is_array($addresses)) {
        foreach ($addresses as $address) {
            $all[SmartSieve::utf8Decode($address)] = true;
        }
    }
    static $extra_addresses;
    if (!isset($extra_addresses)) {
        $extra_addresses = array();
        if (($func = SmartSieve::getConf('get_email_addresses_hook')) !== null &&
            function_exists($func)) {
            $extra_addresses = call_user_func($func);
        }
    }
    foreach ($extra_addresses as $addr) {
        if (!in_array($addr, $addresses)) {
            $all[SmartSieve::utf8Decode($addr)] = false;
        }
    }
    // Try some other options if have no addresses.
    if (empty($all)) {
        // If username is fully qualified, suggest that.
        if (strpos($_SESSION['smartsieve']['authz'],'@') !== false) {
            $all[$_SESSION['smartsieve']['authz']] = false;
        }
        if (!empty($_SESSION['smartsieve']['server']['maildomain'])) {
            $all[$_SESSION['smartsieve']['authz'] . '@' . $_SESSION['smartsieve']['server']['maildomain']] = false;
        }
    }
    return $all;
}

?>
