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
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
session_start();

SmartSieve::checkAuthentication();

// If we get here, we must have a valid session. Redirect to initial page.
header(sprintf('Location: %s',
    SmartSieve::setUrl(SmartSieve::getConf('initial_page', 'main.php'))));
exit;

?>
