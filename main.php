<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './lib/base.php';

// Form actions.
define("FORM_ACTION_ENABLE", 'enable');
define("FORM_ACTION_DISABLE", 'disable');
define("FORM_ACTION_DELETE", 'delete');
define("FORM_ACTION_SAVE", 'save');
define("FORM_ACTION_CHANGEORDER", 'changeOrder');
define("FORM_ACTION_VIEWSOURCE", 'viewSource');

SmartSieve::checkAuthentication();

$smartsieve = &$_SESSION['smartsieve'];

// Change working script if requested.
if (isset($_POST['script'])) {
	SmartSieve::setWorkingScript(SmartSieve::getFormValue('script'));
}

$script = &$_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']];


/* do rule status change if requested. */

$action = SmartSieve::getFormValue('action');

switch ($action) {

    case (FORM_ACTION_ENABLE):
        $changes = false;
        $ruleIDs = SmartSieve::getPOST('ruleID');
        if (is_array($ruleIDs)) {
            foreach ($ruleIDs as $ruleID) {
                if (($rule = $script->getRule($ruleID)) !== null) {
                    $rule['status'] = 'ENABLED';
                    $script->saveRule($rule, $ruleID);
                    $changes = true;
                }
            }
        }
        if ($changes === true) {
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                $logmsg = sprintf('failed writing script "%s" for %s: %s',
                  $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
                SmartSieve::log($logmsg, LOG_ERR);
            } else {
                SmartSieve::setNotice(SmartSieve::text('Rule successfully enabled'));
            }
        }
        break;

    case (FORM_ACTION_DISABLE):
        $changes = false;
        $ruleIDs = SmartSieve::getPOST('ruleID');
        if (is_array($ruleIDs)) {
            foreach ($ruleIDs as $ruleID) {
                if (($rule = $script->getRule($ruleID)) !== null) {
                    $rule['status'] = 'DISABLED';
                    $script->saveRule($rule, $ruleID);
                    $changes = true;
                }
            }
        }
        if ($changes === true) {
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                $logmsg = sprintf('failed writing script "%s" for %s: %s',
                  $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
                SmartSieve::log($logmsg, LOG_ERR);
            } else {
                SmartSieve::setNotice(SmartSieve::text('Rule successfully disabled'));
            }
        }
        break;

    case (FORM_ACTION_DELETE):
        $changes = false;
        $ruleIDs = SmartSieve::getPOST('ruleID');
        // Sort IDs into reverse order to cope with index renumbering problem.
        rsort($ruleIDs, SORT_NUMERIC);
        if (is_array($ruleIDs)) {
            foreach ($ruleIDs as $ruleID) {
                $changes = $script->deleteRule($ruleID);
            }
        }
        if ($changes === true) {
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                $logmsg = sprintf('failed writing script "%s" for %s: %s',
                  $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
                SmartSieve::log($logmsg, LOG_ERR);
            } else {
                SmartSieve::setNotice(SmartSieve::text('Rule successfully deleted'));
            }
        }
        break;

    case (FORM_ACTION_SAVE):
        $script->content = SmartSieve::utf8Encode(SmartSieve::getFormValue('content'));
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
              $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::log($logmsg, LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('Your changes have been successfully saved'));
        }
        break;

    case (FORM_ACTION_CHANGEORDER):
        $ridx = SmartSieve::getPOST('rindex');
        $newidx = SmartSieve::getPOST('toPosition') - 1;
        $script->changeRuleOrder($ridx, $newidx);
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::log($logmsg, LOG_ERR);
        }
        break;

    case ('direct'):
        $script->mode = 'advanced';
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
              $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::log($logmsg, LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('You are now in direct edit mode'));
        }
        break;

    case ('gui'):
        $script->mode = 'basic';
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
              $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::log($logmsg, LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('You are now in GUI mode'));
        }
        break;

    case (FORM_ACTION_VIEWSOURCE):
        header('Content-Type: text/plain; charset=utf-8');
        echo $script->content;
        exit;
        break;
}


$ret = $script->getContent();
if ($ret === false) {
    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
    SmartSieve::log(sprintf('failed reading rules from script "%s" for %s: %s',
        $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
}

$jsonload = '';
$jsfile = ($script->mode == 'advanced' || $script->so == false) ? 'script-direct.js' : 'main.js';
$help_url = SmartSieve::getConf('main_help_url', '');

include SmartSieve::getConf('include_dir', 'include') . '/common-head.inc';
include SmartSieve::getConf('include_dir', 'include') . '/menu.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common_status.inc';

if ($script->mode == 'advanced' || $script->so == false) {
    if (isset($_POST['content'])) {
        $content = SmartSieve::getPOST('content');
    } else {
        $content = SmartSieve::utf8Decode(Script::removeEncoding());
    }
    include SmartSieve::getConf('include_dir', 'include') . '/script-direct.inc';
} else {
    $rows = array();
    for ($i = 0; $i < count($script->rules); $i++) {
        $tr = array();
        $tr['summary'] = getSummary($script->rules[$i]);
        $tr['class'] = 'inactive';
        $tr['statusImage'] = SmartSieve::getConf('image_dir', 'images') . '/disable.gif';
        $tr['statusChangeAction'] = FORM_ACTION_ENABLE;
        $tr['statusChangeText'] = SmartSieve::text('Enable this rule');
        if ($script->isRuleEnabled($i)) {
            $tr['class'] = 'active';
            $tr['statusImage'] = SmartSieve::getConf('image_dir', 'images') . '/tick.gif';
            $tr['statusChangeAction'] = FORM_ACTION_DISABLE;
            $tr['statusChangeText'] = SmartSieve::text('Disable this rule');
        }
        $tr['id'] = $i;
        $tr['position'] = $i + 1;
        $tr['link'] = SmartSieve::setUrl(sprintf("rule.php?ruleID=%s", $i));
        $tr['tooltip'] = SmartSieve::text('Edit this rule');
        if (!empty($script->rules[$i]['special'])) {
            $tr['link'] = SmartSieve::setUrl(sprintf("rule.php?mode=%s", $script->rules[$i]['special']));
            if ($script->rules[$i]['special'] == RULE_TAG_VACATION) {
                $tr['tooltip'] = SmartSieve::text('Edit vacation settings');
                $tr['img'] = SmartSieve::getConf('image_dir', 'images') . '/vacation.gif';
            } elseif ($script->rules[$i]['special'] == RULE_TAG_FORWARD) {
                $tr['tooltip'] = SmartSieve::text('Edit mail forwarding');
                $tr['img'] = SmartSieve::getConf('image_dir', 'images') . '/forward.gif';
            } elseif ($script->rules[$i]['special'] == RULE_TAG_SPAM) {
                $tr['tooltip'] = SmartSieve::text('Edit spam filtering');
                $tr['img'] = SmartSieve::getConf('image_dir', 'images') . '/spam.gif';
            } elseif ($script->rules[$i]['special'] == RULE_TAG_WHITELIST) {
                $tr['tooltip'] = SmartSieve::text('Edit whitelist');
                $tr['img'] = SmartSieve::getConf('image_dir', 'images') . '/whitelist.gif';
            }
        }
        $rows[] = $tr;
    }
    include SmartSieve::getConf('include_dir', 'include') . '/script-gui.inc';
}
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();


/**
 * Build summary for a filter rule.
 *
 * @param array $rule The rule to build summary for
 * @return string The summary text
 */
function getSummary($rule)
{
    global $script;
    static $useif = true;
    $started = 0;
    $andor = sprintf(" %s ", ($rule['matchAny']) ? SmartSieve::text('OR') : SmartSieve::text('AND'));

    if (Script::hasCondition($rule) == false) {
        $complete = sprintf("[%s] ", SmartSieve::text('Unconditional'));
    } elseif ($useif || $rule['control'] == CONTROL_IF) {
        $complete = sprintf("%s ", SmartSieve::text('IF'));
    } else {
        $complete = sprintf("%s ", SmartSieve::text('ELSE IF'));
    }

    foreach ($rule['conditions'] as $condition) {
        $complete .= ($started) ? $andor : '';
        if ($condition['type'] == TEST_ADDRESS) {
            $match = getMatchType($condition['matchType'], isset($condition['not']) ? $condition['not'] : false);
            if ($condition['header'] == 'from') {
                $complete .= sprintf("'From:' %s '%s'",
                    $match, SmartSieve::utf8Decode($condition['matchStr']));
                $started = 1;
            } elseif ($condition['header'] == 'to') {
                $complete .= sprintf("'To:' %s '%s'",
                    $match, SmartSieve::utf8Decode($condition['matchStr']));
                $started = 1;
            } elseif ($condition['header'] == array('to', 'cc')) {
                $complete .= sprintf("'To:' or 'Cc:' %s '%s'",
                    $match, SmartSieve::utf8Decode($condition['matchStr']));
                $started = 1;
            } else {
                $complete .= sprintf("'%s' %s '%s'", $condition['header'],
                    $match, SmartSieve::utf8Decode($condition['matchStr']));
                $started = 1;
            }
        }
        if ($condition['type'] == TEST_HEADER) {
            $match = getMatchType($condition['matchType'], isset($condition['not']) ? $condition['not'] : false);
            if ($condition['header'] == 'subject') {
                $complete .= sprintf("'Subject:' %s '%s'",
                    $match, SmartSieve::utf8Decode($condition['matchStr']));
            } else {
                $complete .= sprintf("'%s' %s '%s'",
                    SmartSieve::utf8Decode($condition['header']), $match,
                    SmartSieve::utf8Decode($condition['matchStr']));
            }
            $started = 1;
        }
        if ($condition['type'] == TEST_SIZE) {
            $complete .= SmartSieve::text("message %s '%sKB'", array(
                ($condition['gthan']) ? SmartSieve::text('greater than') : SmartSieve::text('less than'),
                $condition['kbytes']));
            $started = 1;
        }
        if ($condition['type'] == TEST_BODY) {
            $match = getMatchType($condition['matchType'], isset($condition['not']) ? $condition['not'] : false);
            $complete .= SmartSieve::text("message body %s '%s'", array(
                $match, SmartSieve::utf8Decode($condition['matchStr'])));
            $started = 1;
        }
    }
    if (Script::hasCondition($rule)) {
        $complete .= sprintf(" %s ", SmartSieve::text('THEN'));
    }
    $started = false;
    foreach ($rule['actions'] as $action) {
        if ($started) {
            $complete .= sprintf(" %s ", SmartSieve::text('AND'));
        }
        switch ($action['type']) {
            case (ACTION_FILEINTO):
                $complete .= sprintf("%s '%s'", SmartSieve::text('file into'),
                                     SmartSieve::mutf7Decode($action['folder']));
                break;
            case (ACTION_REDIRECT):
                $complete .= sprintf("%s '%s'", SmartSieve::text('forward to'),
                                     SmartSieve::utf8Decode($action['address']));
                break;
            case (ACTION_REJECT):
                $complete .= sprintf("%s '%s'", SmartSieve::text('reject'),
                                     SmartSieve::utf8Decode($action['message']));
                break;
            case (ACTION_DISCARD):
                $complete .= SmartSieve::text('discard');
                break;
            case (ACTION_KEEP):
                $complete .= SmartSieve::text('Keep a copy');
                break;
            case (ACTION_STOP):
                $complete .= SmartSieve::text('Stop processing');
                break;
            case (ACTION_VACATION):
                $complete .= SmartSieve::text('Respond');
                if (!empty($action['addresses']) && is_array($action['addresses'])) {
                    $complete .= sprintf(" %s ", SmartSieve::text('to mail sent to'));
                    $first = true;
                    foreach ($action['addresses'] as $addr) {
                        $complete .= ($first) ? '' : ', ';
                        $complete .= SmartSieve::utf8Decode($addr);
                        $first = false;
                    }
                }
                $complete .= (!empty($action['days'])) ? sprintf(" %s ",
                    SmartSieve::text("every %s days", array($action['days']))) : '';
                $complete .= sprintf(" %s",
                    SmartSieve::text('with message "%s"', array(SmartSieve::utf8Decode($action['message']))));
                break;
            case (ACTION_ADDFLAG):
                $complete .= SmartSieve::text('set the "%s" flag', array($action['flag']));
                break;
            case (ACTION_NOTIFY):
                if ($action['method'] == 'mailto') {
                    $complete .= SmartSieve::text('send email notification to %s "%s"',
                        array($action['options'], $action['message']));
                } elseif ($action['method'] == 'sms') {
                    $complete .= SmartSieve::text('send SMS notification to %s "%s"',
                        array($action['options'], $action['message']));
                } else {
                    $complete .= SmartSieve::text('send notification to %s "%s"',
                        array($action['options'], $action['message']));
                }
                break;
            case (ACTION_CUSTOM):
                // Scrap the above and just display the custom text.
                $complete = sprintf("[%s] %s", SmartSieve::text('Custom Rule'),
                                    SmartSieve::utf8Decode($action['sieve']));
                continue 2;
                break;
        }
        $started = true;
    }
    if ($rule['status'] == 'ENABLED') {
        $useif = false;
        if (Script::hasCondition($rule) == false) {
            $useif = true;
        }
    }
    return htmlspecialchars($complete);
}


/**
 * Translate MATCH_* values into human readable text.
 *
 * @param string $matchType The match type value to translate
 * @param boolean $not Invert logic
 * @return string Human readable text
 */
function getMatchType($matchType, $not = false)
{
	$translated = SmartSieve::text('contains');
	switch ($matchType) {
		case (MATCH_IS):
			$translated = ($not) ? SmartSieve::text('is not') : SmartSieve::text('is');
			break;
		case (MATCH_MATCHES):
			$translated = ($not) ? SmartSieve::text('does not match') : SmartSieve::text('matches');
			break;
		case (MATCH_REGEX):
			$translated = ($not) ? SmartSieve::text('does not match regexp') : SmartSieve::text('matches regexp');
			break;
		case (MATCH_CONTAINS):
		default:
			$translated = ($not) ? SmartSieve::text('does not contain') : SmartSieve::text('contains');
			break;
	}
	return $translated;
}
