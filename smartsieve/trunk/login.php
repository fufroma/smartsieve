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
require "$default->lib_dir/sieve.lib";
require "$default->lib_dir/SmartSieve.lib";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$reason = AppSession::getFormValue('reason');

// if a session already exists, go to main page
// unless failure or logout
if (isset($HTTP_SESSION_VARS['sieve']) && is_object($HTTP_SESSION_VARS['sieve'])) {
    if ($reason == 'logout') {
	if (!$HTTP_SESSION_VARS['sieve']->writeToLog("logout: " . 
				$HTTP_SESSION_VARS['sieve']->authz, LOG_INFO))
	    echo SmartSieve::text('ERROR: ') . $HTTP_SESSION_VARS['sieve']->errstr . "<BR>";
	unset($HTTP_SESSION_VARS['sieve']);
	session_unregister('sieve');
        unset($HTTP_SESSION_VARS['scripts']);
        session_unregister('scripts');
        session_destroy();
        session_start();
    }
    elseif ($reason == 'failure') {
	unset($HTTP_SESSION_VARS['sieve']);
	session_unregister('sieve');
        unset($HTTP_SESSION_VARS['scripts']);
    }
    else {
        // we have a session. if we can authenticate, redirect to main.php.
        // if not, we have a cookie problem.
        if ($HTTP_SESSION_VARS['sieve']->authenticate()) {
	    header('Location: ' . AppSession::setUrl('main.php'));
	    exit;
        }
        else {
            echo SmartSieve::text('ERROR: failed to authenticate. please check your SmartSieve cookie settings').'<BR>';
            $HTTP_SESSION_VARS['sieve']->writeToLog('ERROR: login.php: cookie problem', LOG_ERR);
            unset($HTTP_SESSION_VARS['sieve']);
            session_unregister('sieve');
            unset($HTTP_SESSION_VARS['scripts']);
            session_unregister('scripts');
        }
    }
}


// create new session if login form submitted
if (isset($HTTP_POST_VARS['auth']) && isset($HTTP_POST_VARS['passwd'])) {
    $sieve = new AppSession();
    if ($sieve->initialize() && $sieve->authenticate()) {
	// must have created session, and authenticated ok

	if (!$sieve->writeToLog("login: " . (($sieve->auth != $sieve->authz) ? "$sieve->auth as " : "") .  $sieve->authz . ' [' . 
		$GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'] . '] {' . 
		$sieve->server . ':' . $sieve->sieveport . '}', LOG_INFO))
	    echo SmartSieve::text('ERROR: ') . $sieve->errstr . "<BR>";

        /* set scripts array in session. */
        $GLOBALS['HTTP_SESSION_VARS']['scripts'] = array();
        session_register('scripts');
        if (!is_array($GLOBALS['HTTP_SESSION_VARS']['scripts'])) {
            $sieve->writeToLog('login.php: failed to set scripts array in session');
            echo SmartSieve::text('ERROR: failed to set scripts array in session').'<BR>';
        }

	if (isset($HTTP_POST_VARS['lang']))
	    $HTTP_SESSION_VARS['smartsieve_lang'] = AppSession::getFormValue('lang');

	header('Location: ' . AppSession::setUrl('main.php'));
	exit;
    }

    if (!$sieve->writeToLog("FAILED LOGIN: " . $sieve->authz . ' [' .
	$GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'] . '] {' .
	$sieve->server . ':' . $sieve->sieveport . '}: ' . $sieve->errstr, LOG_ERR)) {
        echo SmartSieve::text('ERROR: ') . $sieve->errstr . "<BR>";
    }
    header('Location: ' . AppSession::setUrl('login.php?reason=failure'),true);
    exit;
}


// the main login page should go down here
// we assume no login has yet been submitted (or perhaps not filled in right).


$jsfile = 'login.js';
$jsonload = 'setFocus()';

// The first entry in $servers.
$srvkeys = array_keys($servers);
$fsrv = (!empty($srvkeys)) ? $srvkeys[0] : '';

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/login.inc';

?>
