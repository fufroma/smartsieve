<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->config_dir/servers.php";
require "$default->config_dir/locales.php";
require "$default->lib_dir/Managesieve.php";
require "$default->lib_dir/SmartSieve.lib";
require "$default->lib_dir/Script.php";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$reason = SmartSieve::getFormValue('reason');

// if a session already exists, go to main page
// unless failure or logout
if (isset($_SESSION['smartsieve']) && is_array($_SESSION['smartsieve'])) {
    $smartsieve = &$_SESSION['smartsieve'];
    if ($reason == 'logout') {
        SmartSieve::writeToLog('logout: ' . $smartsieve['authz'], LOG_INFO);
	unset($_SESSION['smartsieve']);
	session_unregister('smartsieve');
        unset($_SESSION['scripts']);
        session_unregister('scripts');
        session_destroy();
        session_start();
    }
    elseif ($reason == 'failure') {
	unset($_SESSION['smartsieve']);
	session_unregister('smartsieve');
        unset($_SESSION['scripts']);
    }
    else {
        // we have a session. if we can authenticate, redirect to main.php.
        // if not, we have a cookie problem.
        if (SmartSieve::authenticate()) {
	    header('Location: ' . SmartSieve::setUrl('main.php'));
	    exit;
        }
        echo SmartSieve::text('ERROR: failed to authenticate. please check your SmartSieve cookie settings').'<BR>';
        SmartSieve::writeToLog('ERROR: login.php: cookie problem', LOG_ERR);
        unset($_SESSION['smartsieve']);
        session_unregister('smartsieve');
        unset($_SESSION['scripts']);
        session_unregister('scripts');
    }
}


// create new session if login form submitted
if (isset($_POST['auth']) && isset($_POST['passwd'])) {
    $auth = SmartSieve::getFormValue('auth');
    $passwd = SmartSieve::getFormValue('passwd');
    $authz = SmartSieve::getFormValue('authz');
    $server = SmartSieve::getFormValue('server');
    if (($ret = SmartSieve::setSession($auth, $passwd, $authz, $server)) === true &&
        ($ret = SmartSieve::authenticate()) === true) {
        // must have created session, and authenticated ok
        $smartsieve = &$_SESSION['smartsieve'];

        $ret = SmartSieve::writeToLog('login: ' . (($smartsieve['auth'] != $smartsieve['authz']) ? $smartsieve['auth'] . ' as ' : '') . $smartsieve['authz'] . ' [' . $_SERVER['REMOTE_ADDR'] . '] {' . $smartsieve['server'] . ':' . $smartsieve['sieveport'] . '}', LOG_INFO);
        if ($ret !== true) {
            SmartSieve::setError($ret);
        }

        /* set scripts array in session. */
        $_SESSION['scripts'] = array();
        // Set which script to edit first.
        SmartSieve::setWorkingScript(SmartSieve::getFormValue('scriptfile'));

        if (isset($_POST['lang'])) {
	    $_SESSION['smartsieve_lang'] = SmartSieve::getFormValue('lang');
        }

        header('Location: ' . SmartSieve::setUrl('main.php'));
        exit;
    }

    SmartSieve::writeToLog('FAILED LOGIN: ' . $auth . ((!empty($authz)) ? " as $authz" : '') . ' [' . $_SERVER['REMOTE_ADDR'] . '] {' . $server . '}: ' . $ret, LOG_ERR);

    header('Location: ' . SmartSieve::setUrl('login.php?reason=failure'),true);
    exit;
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
