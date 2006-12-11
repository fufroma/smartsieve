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
require SmartSieve::getConf('config_dir', 'conf') . "/style.php";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$smartsieve = &$_SESSION['smartsieve'];

SmartSieve::checkAuthentication();

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
                if ($ruleID == 'vacation' && isset($script->vacation)) {
                    $script->vacation['status'] = 'on';
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
                SmartSieve::writeToLog($logmsg, LOG_ERR);
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
                if ($ruleID == 'vacation' && isset($script->vacation)) {
                    $script->vacation['status'] = 'off';
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
                SmartSieve::writeToLog($logmsg, LOG_ERR);
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
                if ($ruleID == 'vacation' && isset($script->vacation)) {
                    unset($script->vacation);
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
                SmartSieve::writeToLog($logmsg, LOG_ERR);
            } else {
                SmartSieve::setNotice(SmartSieve::text('Rule successfully deleted.'));
            }
        }
        break;

    case ('save'):
        $script->script = SmartSieve::utf8Encode(SmartSieve::getFormValue('text'));
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
              $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::writeToLog($logmsg, LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('your changes have been successfully saved.'));
        }
        break;

    case ('increase'):
        $rindex = SmartSieve::getFormValue('rindex');
        /* if this rule and one before it exists, switch them. */
        if (isset($script->rules[$rindex]) &&
                isset($script->rules[$rindex-1])) {
            $tmp = $script->rules[$rindex-1];
            $script->rules[$rindex-1] = $script->rules[$rindex];
            $script->rules[$rindex] = $tmp;
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                $logmsg = sprintf('failed writing script "%s" for %s: %s',
                  $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
                SmartSieve::writeToLog($logmsg, LOG_ERR);
            }
        }
        break;

    case ('decrease'):
        $rindex = SmartSieve::getFormValue('rindex');
        /* if this rule and one after it exists, switch them. */
        if (isset($script->rules[$rindex]) &&
                isset($script->rules[$rindex+1])) {
            $tmp = $script->rules[$rindex+1];
            $script->rules[$rindex+1] = $script->rules[$rindex];
            $script->rules[$rindex] = $tmp;
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                $logmsg = sprintf('failed writing script "%s" for %s: %s',
                  $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
                SmartSieve::writeToLog($logmsg, LOG_ERR);
            }
        }
        break;

    case ('direct'):
        $script->mode = 'advanced';
        if (!$script->updateScript()) {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
            $logmsg = sprintf('failed writing script "%s" for %s: %s',
              $script->name, $_SESSION['smartsieve']['authz'], $script->errstr);
            SmartSieve::writeToLog($logmsg, LOG_ERR);
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
            SmartSieve::writeToLog($logmsg, LOG_ERR);
        } else {
            SmartSieve::setNotice(SmartSieve::text('You are now in GUI mode.'));
        }
        break;

    case ('view_source'):
        header('Content-Type: text/plain; charset=utf-8');
        echo $script->script;
        exit;
        break;
}


$ret = $script->retrieveRules();
if ($ret === false) {
    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
    SmartSieve::writeToLog(sprintf('failed reading rules from script "%s" for %s: %s',
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
} else {
    $summaries = getSummaries();
    $rows = array();
    for ($i=0; $i<count($script->rules); $i++) {
        $tr = array();
        $tr['summary'] = $summaries[$i];
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
    $vRow['summary'] = getVacationSummary();
    $vRow['class'] = 'disabledrule';
    $vRow['eclass'] = 'disabled';
    $vRow['onmouseover'] = $css['.disabledrule-over']['background-color'];
    $vRow['onmouseout'] = $css['.disabledrule']['background-color'];
    $vRow['status'] = SmartSieve::text('DISABLED');
    if ($script->vacation && $script->vacation['status'] == 'on'){
        $vRow['class'] = 'enabledrule';
        $vRow['eclass'] = 'enabled';
        $vRow['onmouseover'] = $css['.enabledrule-over']['background-color'];
        $vRow['onmouseout'] = $css['.enabledrule']['background-color'];
        $vRow['status'] = SmartSieve::text('ENABLED');
    }
    include $default->include_dir . '/script-gui.inc';
}
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();


function getSummaries() {
    global $script;
    $summaries = array();
    $useif = 1;
    foreach ($script->rules as $rule) {
        $andor = ' ' . SmartSieve::text('AND') . ' ';
        $started = 0;
        if ($rule['anyof']) $andor = ' ' . SmartSieve::text('OR') . ' ';

        if ($useif) {
            $complete = SmartSieve::text('IF') . ' ';
        } else {
            $complete = SmartSieve::text('ELSE IF') . ' ';
        }
        if ($rule['unconditional']) $complete = '[' . SmartSieve::text('Unconditional') . '] ';

        if ($rule['from']) {
            $match = setMatchType($rule['from'],$rule['regexp']);
        $complete .= "'From:' " . $match . " '" . SmartSieve::utf8Decode($rule['from']) . "'";
        $started = 1;
        }
        if ($rule['to']) {
        if ($started) $complete .= $andor;
            $match = setMatchType($rule['to'],$rule['regexp']);
        $complete .= "'To:' " . $match . " '" . SmartSieve::utf8Decode($rule['to']) . "'";
        $started = 1;
        }
        if ($rule['subject']) {
        if ($started) $complete .= $andor;
            $match = setMatchType($rule['subject'],$rule['regexp']);
        $complete .= "'Subject:' " . $match . " '" . SmartSieve::utf8Decode($rule['subject']) . "'";
        $started = 1;
        }
        if ($rule['field'] && $rule['field_val']) {
        if ($started) $complete .= $andor;
            $match = setMatchType($rule['field_val'],$rule['regexp']);
        $complete .= "'" . SmartSieve::utf8Decode($rule['field']) . "' " . $match . " '" . SmartSieve::utf8Decode($rule['field_val']) . "'";
        $started = 1;
        }
        if (isset($rule['size']) && $rule['size'] !== '') {
        $xthan = SmartSieve::text('less than');
        if ($rule['gthan']) $xthan = SmartSieve::text('greater than');
        if ($started) $complete .= $andor;
        $complete .= SmartSieve::text("message %s '%sKB'", array($xthan,$rule['size']));
        $started = 1;
        }
        if (!$rule['unconditional']) $complete .= " ".SmartSieve::text('THEN')." ";
        if (preg_match("/folder/i",$rule['action']))
        $complete .= SmartSieve::text("file into '%s';",array(SmartSieve::mutf7Decode($rule['action_arg'])));
        if (preg_match("/reject/i",$rule['action']))
        $complete .= SmartSieve::text("reject '%s';",array(SmartSieve::utf8Decode($rule['action_arg'])));
        if (preg_match("/address/i",$rule['action']))
            $complete .= SmartSieve::text("forward to '%s';",array(SmartSieve::utf8Decode($rule['action_arg'])));
        if (preg_match("/discard/i",$rule['action']))
            $complete .= SmartSieve::text("discard;");
        if ($rule['keep']) $complete .= " [".SmartSieve::text('Keep a copy')."]";
        if (preg_match("/custom/i",$rule['action'])){
            $complete = '[' . SmartSieve::text('Custom Rule') . '] ' . SmartSieve::utf8Decode($rule['action_arg']);
        }
        $summaries[] = htmlspecialchars($complete);
        if ($rule['status'] == 'ENABLED') {
            if ($rule['continue'] == 1 || $rule['unconditional']) {
                $useif = 1;
            } else {
                $useif = 0;
            }
        }
    }
    return $summaries;
}

function getVacationSummary()
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
            $vacation_str .= SmartSieve::utf8Decode($addr);
            $first = false;
        }
    }
    if (!empty($vacation['days'])){
        $vacation_str .= ' ' . SmartSieve::text("every %s days",array($vacation['days']));
    }
    $vacation_str .= ' ' . SmartSieve::text('with message "%s"',array(SmartSieve::utf8Decode($vacation['text'])));
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

