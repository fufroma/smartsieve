<?php
 
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->lib_dir/SmartSieve.lib";

session_name($default->session_name);
session_start();

// if session already exists, redirect to initial page.
// if not, redirect to login page.
if (isset($HTTP_SESSION_VARS['sieve']) && is_object($HTTP_SESSION_VARS['sieve'])) {
    header('Location: ' . AppSession::setUrl('main.php'));
    exit;
}
else {
    header('Location: login.php');
    exit;
}



?>
