<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->lib_dir/sieve.lib";
require "$default->lib_dir/SmartSieve.lib";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
@session_start();

$errors = array();
$msgs = array();

$sieve = &$GLOBALS['HTTP_SESSION_VARS']['sieve'];
$scripts = &$GLOBALS['HTTP_SESSION_VARS']['scripts'];

// if a session does not exist, go to login page
if (!is_object($sieve) || !$sieve->authenticate()) {
	header('Location: ' . AppSession::setUrl('login.php'),true);
	exit;
}

// should have a valid session at this point

// open sieve connection
if (!$sieve->openSieveSession()) {
    print "ERROR: " . $sieve->errstr . "<BR>\n";
    $sieve->writeToLog('ERROR: openSieveSession failed for ' . $sieve->user . 
        ': ' . $sieve->errstr, LOG_ERR);
    exit;
}


/* do script actions if necessary. */

$action = AppSession::getFormValue('action');

if ($action == 'setactive')
{
    $sids = AppSession::getFormValue('scriptID');
    if (is_array($sids)){
        // might have been more than one checkbox selected.
        // set only the first one active.
        $s = $sieve->scriptlist[$sids[0]];
        if ($s){
            $sieve->connection->activatescript($s);
            if ($sieve->connection->errstr)
                array_push($errors,'activatescript failed: ' . $sieve->connection->errstr);
            else
                array_push($msgs,"script '$s' successfully activated");
            if (!AppSession::doListScripts())
                array_push($errors,'AppSession::doListScripts failed: ' . AppSession::getError());
        }
    }
}

if ($action == 'deactivate')
{
    // this deactivates whichever script, if any, is currently set
    // as the active script, so we don't care about the scriptID array.
    $sieve->connection->activatescript("");
    if ($sieve->connection->errstr)
        array_push($errors,'activatescript failed: ' . $sieve->connection->errstr);
    else
        array_push($msgs,"successfully deactivated all scripts");
    if (!AppSession::doListScripts())
        array_push($errors,'AppSession::doListScripts failed: ' . AppSession::getError());
}

if ($action == 'createscript')
{
    $newscript = AppSession::getFormValue('newscript');
    if ($newscript){
        if (AppSession::scriptExists($newscript)){
            array_push($errors,'script ' . $newscript . ' already exists');
        }
        else {
            if (!isset($scripts[$newscript]))
                $scripts[$newscript] = new Script($newscript);
            if (is_object($scripts[$newscript])){
                if (!$scripts[$newscript]->updateScript($sieve->connection)) {
                    array_push($errors, 'updateScript failed: ' . $scripts[$newscript]->errstr);
                    $sieve->writeToLog('scripts.php: updateScript failed for ' . $sieve->user
                        . ': ' . $scripts[$newscript]->errstr, LOG_ERR);
                }
            }
            if (AppSession::scriptExists($newscript))
                array_push($msgs,"successfully created script '$newscript'");
            else
                array_push($errors,"could not create script '$newscript'");
        }
    }
}

if ($action == 'delete')
{
    $sids = AppSession::getFormValue('scriptID');
    if (is_array($sids)){
        // might have been more than one checkbox selected.
        // try to delete each one in turn.
        foreach ($sids as $sid){
            $sname = $sieve->scriptlist[$sid];
            if ($sname){
                $sieve->connection->deletescript($sname);
                if ($sieve->connection->errstr)
                    array_push($errors,'deletescript failed: ' . $sieve->connection->errstr);
                else {
                    array_push($msgs,"script '$sname' successfully deleted");
                    if (isset($scripts[$sname]))
                        unset($scripts[$sname]);
                }
            }
        }
        if (!AppSession::doListScripts())
            array_push($errors,'AppSession::doListScripts failed: ' . AppSession::getError());
    }
}

if ($action == 'rename')
{
    $oldscript = '';
    $newscript = AppSession::getFormValue('newscript');
    $sids = AppSession::getFormValue('scriptID');
    if (is_array($sids)){
        // might have been more than one checkbox selected.
        // rename the first one only.
        $oldscript = $sieve->scriptlist[$sids[0]];
    }
    if ($newscript && $oldscript){
        if (AppSession::scriptExists($newscript)){
            array_push($errors,'script ' . $newscript . ' already exists');
        }
        else {
            if (!AppSession::scriptExists($oldscript)){
                array_push($errors,'script ' . $oldscript . ' does not exist');
            }
            else {
                if (!isset($scripts[$oldscript]))
                    $scripts[$oldscript] = new Script($oldscript);
                if (!$scripts[$oldscript]->retrieveRules($sieve->connection)){
                    array_push($errors, "retrieveRules failed for script $oldscript: " . $scripts[$oldscript]->errstr);
                }
                else {
                  if ($scripts[$oldscript]->so){
                    // safe to work with this script.
                    $scripts[$newscript] = $scripts[$oldscript];
                    if (is_object($scripts[$newscript])){
                        $scripts[$newscript]->name = $newscript;
                        if (!$scripts[$newscript]->updateScript($sieve->connection)) {
                            array_push($errors, 'updateScript failed: ' . $scripts[$newscript]->errstr);
                            $sieve->writeToLog('scripts.php: updateScript failed for ' . $sieve->user
                                . ': ' . $scripts[$newscript]->errstr, LOG_ERR);
                            unset($scripts[$newscript]);
                        }
                        else {
                            $sieve->connection->deletescript($oldscript);
                            if ($sieve->connection->errstr)
                                array_push($errors,'deletescript failed: ' . $sieve->connection->errstr);
                            else {
                                array_push($msgs,"successfully renamed '$oldscript' as '$newscript'");
                                unset($scripts[$oldscript]);
                            }
                        }
                    }
                  }
                }
            }
        }
        AppSession::doListScripts();
    }
}

if ($action == 'viewscript') 
{
    $s = AppSession::getFormValue('viewscript');
    if ($s){
        $sieve->workingscript = $s;
        header('Location: ' . AppSession::setUrl('main.php'),true);
        exit;
    }
}


$jsfile = 'scripts.js';
$jsonload = '';
if (!empty($default->scripts_help_url)){
    $help_url = $default->scripts_help_url;
} else {
    $help_url = '';
}

include $default->include_dir . '/common-head.inc';
include $default->include_dir . '/menu.inc';
include $default->include_dir . '/common_status.inc';
include $default->include_dir . '/scripts.inc';

$sieve->closeSieveSession();

?>
