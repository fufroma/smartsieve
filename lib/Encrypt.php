<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


//require 'Crypt/HCEMD5.php';
class Encrypt {


    function encrypt ($string,$key) {

//	require 'Crypt/HCEMD5.php';
include_once 'Crypt/HCEMD5.php';

	$hcemd5 = new Crypt_HCEMD5($key);
	$encrypted = $hcemd5->encodeMimeSelfRand($string);
	return $encrypted;
    }


    function decrypt ($string,$key) {

//	require 'Crypt/HCEMD5.php';
include_once 'Crypt/HCEMD5.php';

	$hcemd5 = new Crypt_HCEMD5($key);
	$decrypted = $hcemd5->decodeMimeSelfRand($string);
	return $decrypted;
    }


    function generateKey () {

	if (isset($GLOBALS['HTTP_COOKIE_VARS']) &&
            isset($GLOBALS['HTTP_COOKIE_VARS'][session_name()])) {

	    // here we use the Mersenne Twister randon number generator.
	    // we should seed the generator only once.
	    // this should give us a very unpredictable 32 char key.
	    mt_srand((double)microtime() * 1000000);
	    $key = md5(uniqid(mt_rand(),1));

	    $GLOBALS['HTTP_COOKIE_VARS']['key'] = $key;
	    setcookie('key',$key,0,$GLOBALS['default']->cookie_path,$GLOBALS['default']->cookie_domain,0);

	}
	else {

	    // can't save random key in cookie, so we have to use a standard one.
	    $key = md5(session_id());

	}

	return $key;
    }


    function retrieveKey () {
	global $HTTP_COOKIE_VARS;

	if (isset($HTTP_COOKIE_VARS['key']))
	    $key = $HTTP_COOKIE_VARS['key'];
	else
	    $key = md5(session_id());

	return $key;
    }


}


?>
