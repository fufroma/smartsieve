<?php
 
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './lib/base.php';

SmartSieve::checkAuthentication();

// If we get here, we must have a valid session. Redirect to initial page.
header(sprintf('Location: %s',
    SmartSieve::setUrl(SmartSieve::getConf('initial_page', 'main.php'))));
exit;

?>
