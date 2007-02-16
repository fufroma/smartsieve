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

SmartSieve::checkAuthentication();

$smartsieve = &$_SESSION['smartsieve'];
$script = &$_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']];

// If not in GUI mode, redirect.
if ($script->mode == 'advanced' || $script->so == false) {
    header('Location: ' . SmartSieve::setUrl('main.php'));
    exit;
}

$vacation = array();   /* $script->vacation. */

/* if save, enable or disable was selected from vacation.php, then get 
 * the vacation values from POST data. if not, use $script->vacation.
 */
if (isset($_POST['submitted'])) {
    $address = SmartSieve::utf8Encode(SmartSieve::getFormValue('addresses'));
    $address = preg_replace("/\"|\\\/","",$address);
    $addresses = array();
    $addresses = preg_split("/\s*,\s*|\s+/",$address);
    $vacation['text'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('text'));
    $vacation['days'] = SmartSieve::getFormValue('days');
    $vacation['addresses'] = $addresses;
    $vacation['status'] = SmartSieve::getFormValue('status');
} elseif (!empty($script->vacation)) {
    $vacation = $script->vacation;
} else {
    $vacation = array();
    $vacation['status'] = 'on';
    $vacation['text'] = SmartSieve::getConf('vacation_text', '');
    $vacation['days'] = SmartSieve::getConf('vacation_days', 0);
    $vacation['addresses'] = array();
}

/* save vacation settings if requested. */

$action = SmartSieve::getFormValue('thisAction');

switch ($action) {

    case ('enable'):
        if (($ret = checkRule($vacation)) === true){
            if ($script->vacation) {
                $oldvacation = $script->vacation;
                $script->vacation = $vacation;
                $script->vacation['status'] = 'on';
                if (!$script->updateScript()) {
                    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                    SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                        $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                        $script->vacation = $oldvacation;
                } else {
                    SmartSieve::setNotice(SmartSieve::text('vacation settings successfully enabled.'));
                    $vacation['status'] = 'on';
                    if (SmartSieve::getConf('return_after_update') === true) {
                        header('Location: ' . SmartSieve::setUrl('main.php'),true);
                        exit;
                    }
                }
            } else {
                SmartSieve::setError(SmartSieve::text('ERROR: vacation settings not yet saved.'));
            }
        } else {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $ret);
        }
        break;

    case ('disable'):
        if (($ret = checkRule($vacation)) === true){
            if ($script->vacation) {
                $oldvacation = $script->vacation;
                $script->vacation = $vacation;
                $script->vacation['status'] = 'off';
                if (!$script->updateScript()) {
                    SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                    SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                        $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                        $script->vacation = $oldvacation;
                } else {
                    SmartSieve::setNotice(SmartSieve::text('vacation settings successfully disabled.'));
                    $vacation['status'] = 'off';
                    if (SmartSieve::getConf('return_after_update') === true) {
                        header('Location: ' . SmartSieve::setUrl('main.php'),true);
                        exit;
                    }
                }
            } else {
                SmartSieve::setError(SmartSieve::text('ERROR: vacation settings not yet saved.'));
            }
        } else {
            SmartSieve::setError(SmartSieve::text('ERROR: ') . $ret);
        }
        break;

    case ('save'):
        if (($ret = checkRule($vacation)) === true){
            if (isset($script->vacation)) {
                $oldvacation = $script->vacation;
            }
            $script->vacation = $vacation;
            if (!$script->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $script->errstr);
                SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                    $script->name, $_SESSION['smartsieve']['authz'], $script->errstr), LOG_ERR);
                if (isset($oldvacation)) {
                    $script->vacation = $oldvacation;
                } else {
                    unset($script->vacation);
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


$jsfile = 'vacation.js';
$jsonload = '';
$help_url = SmartSieve::getConf('vacation_help_url', '');
$wrap_width = (SmartSieve::getConf('wrap_width')) ? SmartSieve::getConf('wrap_width') : 80;
$max_days = SmartSieve::getConf('max_vacation_days', 30);
$addrs_display = '';
if (is_array($vacation['addresses'])) {
    foreach ($vacation['addresses'] as $address) {
        $addrs_display .= sprintf("%s%s", (!empty($addrs_display)) ? ', ' : '', SmartSieve::utf8Decode($address));
    }
}

include SmartSieve::getConf('include_dir', 'include') . '/common-head.inc';
include SmartSieve::getConf('include_dir', 'include') . '/menu.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common_status.inc';
include SmartSieve::getConf('include_dir', 'include') . '/vacation.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();


/* basic sanity checks on vacation rule.
 * any value returned will be an error msg.
 * note: we will only demand a value from user if no default is set in config.
 */
function checkRule($vacation)
{
    if (!$vacation['text'] && !SmartSieve::getConf('vacation_text')) {
	return SmartSieve::text("please supply the message to send with auto-responses");
    }
    if (!$vacation['days'] && SmartSieve::getConf('require_vacation_days') && !SmartSieve::getConf('vacation_days')) {
        return SmartSieve::text("please select the number of days to wait between responses");
    }
    // does $vacation['addresses'] contain any valid addresses?
    $a = false;
    foreach ($vacation['addresses'] as $addr){
        $tokens = explode('@',$addr);
        if (count($tokens) == 2 && $tokens[0] != '' && strpos($tokens[1],'.') !== false){
            $a = true;
        }
    }
    if ($a == false && SmartSieve::getConf('require_vacation_addresses') && !$_SESSION['smartsieve']['maildomain']) {
        return SmartSieve::text("please supply at least one valid vacation address");
    }

    /* check values don't exceed acceptible sizes. */
    foreach ($vacation['addresses'] as $addr){
        if (strlen($addr) > SmartSieve::getConf('max_field_chars', 50)) {
            return SmartSieve::text('vacation address should not exceed %d characters.', array(SmartSieve::getConf('max_field_chars', 50)));
        }
    }
    if (strlen($vacation['text']) > SmartSieve::getConf('max_textbox_chars', 500)) {
	return SmartSieve::text('vacation message should not exceed %d characters.', array(SmartSieve::getConf('max_textbox_chars', 500)));
    }

    /* complain if vacation days contains non-digits. */
    if (preg_match("/\D/",$vacation['days']))
	return SmartSieve::text('vacation days must be a positive integer');

    return true;
}


?>
