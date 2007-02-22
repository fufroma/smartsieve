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

SmartSieve::checkAuthentication();

$smartsieve = &$_SESSION['smartsieve'];
$script = &$_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']];

// If not in GUI mode, redirect.
if ($script->mode == 'advanced' || $script->so == false) {
    header('Location: ' . SmartSieve::setUrl('main.php'));
    exit;
}

$vacation = array();

// Get form values from POST data.
if (isset($_POST['submitted'])) {
    $addrs = SmartSieve::getFormValue('address');
    $addresses = array();
    if (is_array($addrs)) {
        foreach ($addrs as $addr) {
            $addresses[] = SmartSieve::utf8Encode($addr);
        }
    }
    $newAddrs = SmartSieve::utf8Encode(SmartSieve::getFormValue('newaddresses'));
    $newAddrs = preg_replace("/\"|\\\/", "", $newAddrs);
    $addrs = preg_split("/\s*,\s*|\s+/", $newAddrs);
    foreach ($addrs as $addr) {
        if (!empty($addr)) {
            $addresses[] = $addr;
        }
    }
    $addresses = array_unique($addresses);
    $vacation['text'] = SmartSieve::utf8Encode(SmartSieve::getFormValue('text'));
    $vacation['days'] = SmartSieve::getFormValue('days');
    $vacation['addresses'] = $addresses;
    $vacation['status'] = SmartSieve::getFormValue('status');
}
// Use existing vacation values, if any.
elseif (!empty($script->vacation)) {
    $vacation = $script->vacation;
}
// Otherwise, initialize some default values.
else {
    $vacation = array();
    $vacation['status'] = 'on';
    $vacation['text'] = SmartSieve::getConf('vacation_text', '');
    $vacation['days'] = SmartSieve::getConf('vacation_days', 7);
    $vacation['addresses'] = array();
}

// Perform actions.

$action = SmartSieve::getFormValue('thisAction');

switch ($action) {

    case ('enable'):
        if (isSane($vacation) === true) {
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
        }
        break;

    case ('disable'):
        if (isSane($vacation) === true) {
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
        }
        break;

    case ('save'):
        if (isSane($vacation) === true) {
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
        }
        break;
}


$jsfile = 'vacation.js';
$jsonload = '';
$help_url = SmartSieve::getConf('vacation_help_url', '');
$wrap_width = (SmartSieve::getConf('wrap_width')) ? SmartSieve::getConf('wrap_width') : 80;
$max_days = SmartSieve::getConf('max_vacation_days', 30);
$addrs_display = '';
$addresses = array();
if (is_array($vacation['addresses'])) {
    foreach ($vacation['addresses'] as $address) {
        $addresses[SmartSieve::utf8Decode($address)] = true;
    }
}
if (($func = SmartSieve::getConf('get_email_addresses_hook')) !== null &&
    function_exists($func)) {
    $extra_addresses = call_user_func($func);
    foreach ($extra_addresses as $addr) {
        if (!in_array($addr, $vacation['addresses'])) {
            $addresses[SmartSieve::utf8Decode($addr)] = false;
        }
    }
}
if (empty($addresses)) {
    // If username is fully qualified, suggest that.
    if (strpos($_SESSION['smartsieve']['authz'],'@') !== false) {
        $addresses[$_SESSION['smartsieve']['authz']] = false;
    }
    if (!empty($_SESSION['smartsieve']['maildomain'])) {
        $addresses[$_SESSION['smartsieve']['authz'] . '@' . $_SESSION['smartsieve']['maildomain']] = false;
    }
}

include SmartSieve::getConf('include_dir', 'include') . '/common-head.inc';
include SmartSieve::getConf('include_dir', 'include') . '/menu.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common_status.inc';
include SmartSieve::getConf('include_dir', 'include') . '/vacation.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();


/**
 * Is this vacation rule sane.
 *
 * Performs basic sanity/integrity checks.
 *
 * @param array $vacation Vacation rule
 * @return boolean true if sane, false if not
 */
function isSane($vacation)
{
    // User must set a vacation message.
    if (!$vacation['text']) {
        SmartSieve::setError(SmartSieve::text("please supply the message to send with auto-responses"));
        return false;
    }
    if (!$vacation['days'] && SmartSieve::getConf('require_vacation_days')) {
        SmartSieve::setError(SmartSieve::text("please select the number of days to wait between responses"));
        return false;
    }
    // Reject invalid email addresses.
    foreach ($vacation['addresses'] as $addr) {
        if (!preg_match("/^[\x21-\x7E]+@([0-9a-zA-Z-]+\.)+[0-9a-zA-Z]{2,}$/i", $addr)) {
            SmartSieve::setError(SmartSieve::text('"%s" is not a valid email address',
                array(htmlspecialchars($addr))));
            return false;
        }
        if (strlen($addr) > SmartSieve::getConf('max_field_chars', 500)) {
            SmartSieve::setError(SmartSieve::text('vacation address should not exceed %d characters.',
                array(SmartSieve::getConf('max_field_chars', 500))));
            return false;
        }
    }
    if (empty($vacation['addresses']) && SmartSieve::getConf('require_vacation_addresses')) {
        SmartSieve::setError(SmartSieve::text("please supply at least one valid vacation address"));
        return false;
    }
    if (strlen($vacation['text']) > SmartSieve::getConf('max_textbox_chars', 50000)) {
        SmartSieve::setError(SmartSieve::text('vacation message should not exceed %d characters.',
            array(SmartSieve::getConf('max_textbox_chars', 50000))));
        return false;
    }
    // Reject vacation days value containing non-digits.
    if (preg_match("/\D/", $vacation['days'])) {
        SmartSieve::setError(SmartSieve::text('vacation days must be a positive integer'));
        return false;
    }
    return true;
}

?>
