<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->config_dir/servers.php";
require "$default->lib_dir/sieve.lib";
require "$default->lib_dir/SmartSieve.lib";

session_name($default->session_name);
@session_start();

// if a session already exists, go to main page
// unless failure or logout
if (isset($HTTP_SESSION_VARS['sieve']) && is_object($HTTP_SESSION_VARS['sieve'])) {

    if (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'logout') {
	if (!$HTTP_SESSION_VARS['sieve']->writeToLog("logout: " . 
				$HTTP_SESSION_VARS['sieve']->user, LOG_INFO))
	    print "ERROR: " . $HTTP_SESSION_VARS['sieve']->errstr . "<BR>";
	$HTTP_SESSION_VARS['sieve'] = null;
	session_unregister('sieve');
        $HTTP_SESSION_VARS['scripts'] = null;
        session_unregister('scripts');
    }
    elseif (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'failure') {
	$HTTP_SESSION_VARS['sieve'] = null;
	session_unregister('sieve');
    }
    else {
        // we have a session. if we can authenticate, redirect to main.php.
        // if not, we have a cookie problem.
        if ($HTTP_SESSION_VARS['sieve']->authenticate()) {
	    header('Location: ' . AppSession::setUrl('main.php'));
	    exit;
        }
        else {
            echo 'ERROR: failed to authenticate. please check your SmartSieve cookie settings<BR>';
            $HTTP_SESSION_VARS['sieve']->writeToLog('ERROR: login.php: cookie problem', LOG_ERR);
            $HTTP_SESSION_VARS['sieve'] = null;
            session_unregister('sieve');
            $HTTP_SESSION_VARS['scripts'] = null;
            session_unregister('scripts');
        }
    }
}


// create new session if login form submitted
if (isset($HTTP_POST_VARS['sieveuid']) && isset($HTTP_POST_VARS['passwd'])) {
    $sieve = new AppSession();
    if ($sieve->initialize() && $sieve->authenticate()) {
	// must have created session, and authenticated ok

	if (!$sieve->writeToLog("login: " . $sieve->user . ' [' . 
		$GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'] . '] {' . 
		$sieve->server . ':' . $sieve->sieveport . '}', LOG_INFO))
	    print "ERROR: " . $sieve->errstr . "<BR>";

        /* set scripts array in session. */
        $GLOBALS['HTTP_SESSION_VARS']['scripts'] = array();
        session_register('scripts');
        if (!is_array($GLOBALS['HTTP_SESSION_VARS']['scripts'])) {
            $sieve->writeToLog('login.php: failed to set scripts array in session');
            print 'ERROR: failed to set scripts array in session<BR>';
        }

	header('Location: ' . AppSession::setUrl('main.php'));
	exit;
    }

    if (!$sieve->writeToLog("FAILED LOGIN: " . $sieve->user . ' [' .
	$GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'] . '] {' .
	$sieve->server . ':' . $sieve->sieveport . '}: ' . $sieve->errstr, LOG_ERR))
      print "ERROR: " . $sieve->errstr . "<BR>";
    header('Location: ' . AppSession::setUrl('login.php?reason=failure'),true);
    exit;
}


// the main login page should go down here
// we assume no login has yet been submitted (or perhaps not filled in right).


$jsfile = 'login.js';
$jsonload = 'setFocus()';

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/login.inc';

?>
