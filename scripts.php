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
$scripts = &$_SESSION['scripts'];

$managesieve = &$GLOBALS['managesieve'];

/* do script actions if necessary. */

$action = SmartSieve::getFormValue('action');

if ($action == 'setactive')
{
    $sids = SmartSieve::getFormValue('scriptID');
    if (is_array($sids)){
        // might have been more than one checkbox selected.
        // set only the first one active.
        $slist = array_keys(SmartSieve::getScriptList());
        if (isset($slist[$sids[0]])) {
            $s = $slist[$sids[0]];
            $ret = $managesieve->setActive($s);
            if ($ret === false) {
                SmartSieve::setError(SmartSieve::text('activatescript failed').': ' . $managesieve->getError());
            } else {
                SmartSieve::setNotice(SmartSieve::text("Script \"%s\" successfully activated.", array($s)));
            }
        }
    }
}

if ($action == 'deactivate')
{
    // this deactivates whichever script, if any, is currently set
    // as the active script, so we don't care about the scriptID array.
    $ret = $managesieve->setActive('');
    if ($ret === false) {
        SmartSieve::setError(SmartSieve::text('activatescript failed').': ' . $managesieve->getError());
    } else {
        SmartSieve::setNotice(SmartSieve::text("Successfully deactivated all scripts."));
    }
}

if ($action == 'createscript')
{
    $newscript = SmartSieve::getFormValue('newscript');
    if ($newscript){
        if (SmartSieve::scriptExists($newscript)) {
            SmartSieve::setError(SmartSieve::text("Script \"%s\" already exists.", array($newscript)));
        } else {
            if (!isset($scripts[$newscript]) || !is_object($scripts[$newscript])) {
                $scripts[$newscript] = new Script($newscript);
            }
            if (!$scripts[$newscript]->updateScript()) {
                SmartSieve::setError(SmartSieve::text('ERROR: ') . $scripts[$newscript]->errstr);
                SmartSieve::writeToLog(sprintf('failed writing script "%s" for %s: %s',
                    $scripts[$newscript]->name, $_SESSION['smartsieve']['authz'], $scripts[$newscript]->errstr), LOG_ERR);
            }
            if (SmartSieve::scriptExists($newscript)) {
                SmartSieve::setNotice(SmartSieve::text("Successfully created script \"%s\".", array($newscript)));
            } else {
                SmartSieve::setError(SmartSieve::text("Could not create script \"%s\".", array($newscript)));
            }
        }
    }
}

if ($action == 'delete')
{
    $sids = SmartSieve::getFormValue('scriptID');
    if (is_array($sids)){
        // might have been more than one checkbox selected.
        // try to delete each one in turn.
        foreach ($sids as $sid){
            $slist = array_keys(SmartSieve::getScriptList());
            if (isset($slist[$sid])) {
                $sname = $slist[$sid];
                $ret = $managesieve->deleteScript($sname);
                if ($ret === false) {
                    SmartSieve::setError('deletescript '.SmartSieve::text('failed: ') . $managesieve->getError());
                } else {
                    SmartSieve::setNotice(SmartSieve::text("Script \"%s\" successfully deleted.", array($sname)));
                    if (isset($scripts[$sname])) {
                        unset($scripts[$sname]);
                    }
                    if ($_SESSION['smartsieve']['workingScript'] == $sname) {
                        SmartSieve::setWorkingScript();
                    }
                }
            }
        }
    }
}

if ($action == 'rename')
{
    $oldscript = '';
    $newscript = SmartSieve::getFormValue('newscript');
    $sids = SmartSieve::getFormValue('scriptID');
    if (is_array($sids)){
        // might have been more than one checkbox selected.
        // rename the first one only.
        $slist = array_keys(SmartSieve::getScriptList());
        if (isset($slist[$sids[0]])) {
            $oldscript = $slist[$sids[0]];
        }
    }
    if ($newscript && $oldscript){
        if (SmartSieve::scriptExists($newscript)) {
            SmartSieve::setError(SmartSieve::text("Script \"%s\" already exists.", array($newscript)));
        } elseif (!SmartSieve::scriptExists($oldscript)) {
            SmartSieve::setError(SmartSieve::text("Script \"%s\" does not exist.", array($oldscript)));
        } else {
            $resp = $managesieve->getScript($oldscript);
            if ($resp === false || !is_array($resp)) {
                SmartSieve::setError('getscript '.SmartSieve::text('failed: ') . $managesieve->getError());
            } else {
                $old = $resp['raw'];
                $ret = $managesieve->putScript($newscript, $old);
                if ($ret === false) {
                    SmartSieve::setError('putscript '.SmartSieve::text('failed: ') . $managesieve->getError());
                } else {
                    // Check old and new are the same.
                    $resp = $managesieve->getScript($newscript);
                    if ($resp === false) {
                        SmartSieve::setError('getscript '.SmartSieve::text('failed: ') . $managesieve->getError());
                    } elseif ($resp['raw'] != $old) {
                        SmartSieve::setError(SmartSieve::text("Failed to rename \"%s\" as \"%s\"",array($oldscript,$newscript)));
                        $managesieve->deleteScript($newscript);
                    } else {
                        // Successfully copied old to new. Delete old.
                        $ret = $managesieve->deleteScript($oldscript);
                        if ($ret === false) {
                            SmartSieve::setError('deletescript '.SmartSieve::text('failed: ') . $managesieve->getError());
                        } else {
                            SmartSieve::setNotice(SmartSieve::text("Successfully renamed \"%s\" as \"%s\".", array($oldscript,$newscript)));
                            if (isset($scripts[$oldscript])) {
                                $scripts[$newscript] = $scripts[$oldscript];
                                unset($scripts[$oldscript]);
                            }
                            if ($_SESSION['smartsieve']['workingScript'] == $oldscript) {
                                SmartSieve::setWorkingScript();
                            }
                        }
                    }
                }
            }
        }
    }
}

if ($action == 'viewscript') 
{
    $s = SmartSieve::getFormValue('viewscript');
    if ($s){
        SmartSieve::setWorkingScript($s);
        header('Location: ' . SmartSieve::setUrl('main.php'),true);
        exit;
    }
}


$jsfile = 'scripts.js';
$jsonload = '';
$help_url = SmartSieve::getConf('scripts_help_url', '');
$slist = SmartSieve::getScriptList();
$i = 0;

include SmartSieve::getConf('include_dir', 'include') . '/common-head.inc';
include SmartSieve::getConf('include_dir', 'include') . '/menu.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common_status.inc';
include SmartSieve::getConf('include_dir', 'include') . '/scripts.inc';
include SmartSieve::getConf('include_dir', 'include') . '/common-footer.inc';

SmartSieve::close();

?>
