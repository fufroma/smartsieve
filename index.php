<?php
 
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->lib_dir/SmartSieve.lib";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, $default->cookie_path, $default->cookie_domain);
session_name($default->session_name);
session_start();

// if session already exists, redirect to initial page.
// if not, redirect to login page.
if (isset($_SESSION['smartsieve']) && is_array($_SESSION['smartsieve'])) {
    header('Location: ' . SmartSieve::setUrl('main.php'));
    exit;
}
else {
    header('Location: ' . SmartSieve::setUrl('login.php'));
    exit;
}



?>
