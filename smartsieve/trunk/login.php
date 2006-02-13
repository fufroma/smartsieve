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
require SmartSieve::getConf('config_dir', 'conf') . "/servers.php";
require SmartSieve::getConf('config_dir', 'conf') . "/locales.php";
require SmartSieve::getConf('lib_dir', 'lib') . "/Managesieve.php";
require SmartSieve::getConf('lib_dir', 'lib') . "/Script.php";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$reason = SmartSieve::getFormValue('reason');

switch ($reason) {
    case ('logout'):
        SmartSieve::logout();
        break;

    case ('failure'):
        break;

    case ('session'):
        break;

    default:
        break;
}

// Existing session.
if (isset($_SESSION['smartsieve']) && is_array($_SESSION['smartsieve'])) {
    // If we can authenticate redirect into the application.
    // If not, destroy current session.
    if (SmartSieve::authenticate() === true) {
        header(sprintf('Location: %s',
            SmartSieve::setUrl(SmartSieve::getConf('initial_page', 'main.php'))));
        exit;
    }
    SmartSieve::destroy();
}

// If login details have been submitted, create new session and redirect.
if (($details = SmartSieve::getLoginDetails()) !== false) {
    if (SmartSieve::login($details) === true) {
        header(sprintf('Location: %s',
            SmartSieve::setUrl(SmartSieve::getConf('initial_page', 'main.php'))));
        exit;
    }
    $reason = 'failure';
}


// the main login page should go down here
// we assume no login has yet been submitted (or perhaps not filled in right).


$tabindex = 1;
$jsfile = 'login.js';
$jsonload = 'setFocus()';

// The first entry in $servers.
$srvkeys = array_keys($servers);
$fsrv = (!empty($srvkeys)) ? $srvkeys[0] : '';

$proxyusers = SmartSieve::getConf('proxy_authz_users', array());
$proxyall = false;
if (isset($proxyusers[0]) && $proxyusers[0] == 'all') {
    $proxyusers = array();
    $proxyall = true;
}

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/login.inc';

?>
