<?php
/*
 * $Id$
 *
 * Copyright 2002-2006 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


/*
 * class SmartSieveCryptHCEMD5 extends the SmartSieveCrypt class.
 * It allows SmartSieve to use the PEAR HCEMD5 library for encryption.
 */

class SmartSieveCryptHCEMD5 extends SmartSieveCrypt {


    /* The hcemd5 object reference. */
    var $hcemd5;


    /* constructor. */
    function SmartSieveCryptHCEMD5 ($args = array()) 
    {
        require_once 'Crypt/HCEMD5.php';
        $this->hcemd5 = new Crypt_HCEMD5($args['key']);
    }


    function encrypt ($string) 
    {
	$encrypted = $this->hcemd5->encodeMimeSelfRand($string);
	return $encrypted;
    }


    function decrypt ($string) 
    {
	$decrypted = $this->hcemd5->decodeMimeSelfRand($string);
	return $decrypted;
    }


}


?>
