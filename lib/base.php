<?php
/**
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */

require './lib/SmartSieve.lib';
require SmartSieve::getConf('lib_dir', 'lib') . '/Managesieve.php';
require SmartSieve::getConf('lib_dir', 'lib') . "/Script.php";

ini_set('session.use_trans_sid', 0);
session_set_cookie_params(0, SmartSieve::getConf('cookie_path', '/smartsieve'), SmartSieve::getConf('cookie_domain', $_SERVER['SERVER_NAME']));
session_name(SmartSieve::getConf('session_name', 'SmartSieve'));
@session_start();
