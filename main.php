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
require SmartSieve::getConf('config_dir', 'conf') . "/style.php";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, SmartSieve::getConf('cookie_path', ''), SmartSieve::getConf('cookie_domain', ''));
session_name(SmartSieve::getConf('session_name', session_name()));
@session_start();

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

    case ('enable'):
        $changes = false;
        $ruleIDs = SmartSieve::getFormValue('ruleID');
        if (is_array($ruleIDs)) {
            foreach ($ruleIDs as $ruleID) {
                if ($ruleID == 'vacation' && !empty($script->vacation)) {
                    $script->vacation['status'] = 'ENABLED';
                    $changes = true;
                } elseif (isset($script->rules[$ruleID])) {
                    $script->rules[$ruleID]['status'] = 'ENABLED';
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
                SmartSieve::setNotice(SmartSieve::text('rule successfully enabled.'));
            }
        }
        break;

    case ('disable'):
        $changes = false;
        $ruleIDs = SmartSieve::getFormValue('ruleID');
        if (is_array($ruleIDs)) {
            foreach ($ruleIDs as $ruleID) {
                if ($ruleID == 'vacation' && !empty($script->vacation)) {
                    $script->vacation['status'] = 'DISABLED';
                    $changes = true;
                } elseif (isset($script->rules[$ruleID])) {
                    $script->rules[$ruleID]['status'] = 'DISABLED';
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
                SmartSieve::setNotice(SmartSieve::text('rule successfully disabled.'));
            }
        }
        break;

    case ('delete'):
        $changes = false;
        $ruleIDs = SmartSieve::getFormValue('ruleID');
        if (is_array($ruleIDs)) {
            foreach ($ruleIDs as $ruleID) {
                if ($ruleID == 'vacation' && !empty($script->vacation)) {
                    $script->vacation = array();
                    $changes = true;
                } elseif (isset($script->rules[$ruleID])) {
                    $script->rules[$ruleID]['status'] = 'DELETED';
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
                SmartSieve::setNotice(SmartSieve::text('Rule successfully deleted.'));
            }
        }
        break;

    case ('save'):
        $script->content = SmartSieve::utf8Encode(SmartSieve::getFormValue('text'));
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
              $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::log($logmsg, LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('your changes have been successfully saved.'));
        }
        break;

    case ('increase'):
        $rindex = SmartSieve::getFormValue('rindex');
        $script->changeRuleOrder($rindex, ($rindex-1));
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
                $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::log($logmsg, LOG_ERR);
        }
        break;

    case ('decrease'):
        $rindex = SmartSieve::getFormValue('rindex');
        $script->changeRuleOrder($rindex, ($rindex+1));
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
            SmartSieve::setNotice(SmartSieve::text('You are now in direct edit mode.'));
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
            SmartSieve::setNotice(SmartSieve::text('You are now in GUI mode.'));
        }
        break;

    case ('view_source'):
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
if (isset($_POST['text'])) {
    $stext = SmartSieve::getFormValue('text');
} else {
    $stext = SmartSieve::utf8Decode(Script::removeEncoding());
}

if ($script->mode == 'advanced'){
    $jsfile = 'script-direct.js';
} else {
    $jsfile = 'main.js';
}
$jsonload = '';
$help_url = SmartSieve::getConf('main_help_url', '');

include SmartSieve::getConf('include_dir', 'include') . '/common-head.inc';
include SmartSieve::getConf('include_dir', 'include') . '/menu.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common_status.inc';

if ($script->mode == 'advanced' || $script->so == false){
    include SmartSieve::getConf('include_dir', 'include') . '/script-direct.inc';
} else {
    $rows = array();
    for ($i=0; $i<count($script->rules); $i++) {
        $tr = array();
        $tr['summary'] = getSummary($script->rules[$i]);
        $tr['class'] = 'disabledrule';
        $tr['eclass'] = 'disabled';
        $tr['onmouseover'] = $css['.disabledrule-over']['background-color'];
        $tr['onmouseout'] = $css['.disabledrule']['background-color'];
        $tr['status'] = SmartSieve::text('DISABLED');
        if ($script->rules[$i]['status'] == 'ENABLED'){
            $tr['class'] = 'enabledrule';
            $tr['eclass'] = 'enabled';
            $tr['onmouseover'] = $css['.enabledrule-over']['background-color'];
            $tr['onmouseout'] = $css['.enabledrule']['background-color'];
            $tr['status'] = SmartSieve::text('ENABLED');
        }
        $tr['id'] = $i;
        $rows[] = $tr;
    }
    $vRow = array();
    $vRow['class'] = 'disabledrule';
    $vRow['eclass'] = 'disabled';
    $vRow['onmouseover'] = $css['.disabledrule-over']['background-color'];
    $vRow['onmouseout'] = $css['.disabledrule']['background-color'];
    $vRow['status'] = SmartSieve::text('DISABLED');
    if (!empty($script->vacation)) {
        $vRow['summary'] = getSummary($script->vacation);
        if ($script->vacation['status'] == 'ENABLED'){
            $vRow['class'] = 'enabledrule';
            $vRow['eclass'] = 'enabled';
            $vRow['onmouseover'] = $css['.enabledrule-over']['background-color'];
            $vRow['onmouseout'] = $css['.enabledrule']['background-color'];
            $vRow['status'] = SmartSieve::text('ENABLED');
        }
    }
    include SmartSieve::getConf('include_dir', 'include') . '/script-gui.inc';
}
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();


/**
 * Build summary for a filter rule.
 *
 * @param array $rule The rule to build summary for
 * @return text The summary text
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
                    SmartSieve::text("every %s days",array($action['days']))) : '';
                $complete .= sprintf(" %s",
                    SmartSieve::text('with message "%s"',array(SmartSieve::utf8Decode($action['message']))));
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
function getMatchType($matchType, $not=false)
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


?>

