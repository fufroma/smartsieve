<?php
 
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */



// if session already exists, goto main page. if not, goto login page.
if (isset($HTTP_SESSION_VARS['sieve']) && is_object($HTTP_SESSION_VARS['sieve']))
{
    header('Location: ' . $baseurl . 'main.php',true);
    exit;
}
else
{
    header('Location: ' . $baseurl . 'login.php',true);
    exit;
}



?>
