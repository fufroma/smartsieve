<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


/*
 * class SmartSieveCryptRC4 extends the SmartSieveCrypt class.
 * It allows SmartSieve to use the PEAR Rc4.php library for encryption.
 */

class SmartSieveCryptRC4 extends SmartSieveCrypt {


    /* The rc4 object reference. */
    var $rc4;

    /* secret key. */
    var $key;


    /* constructor. */
    function SmartSieveCryptRC4 ($args = array()) 
    {
        require_once 'Crypt/Rc4.php';
        $this->key = $args['key'];
        $this->rc4 = new Crypt_RC4($this->key);
    }


    function encrypt ($string) 
    {
        $this->rc4->key($this->key);
        $this->rc4->crypt($string);
        return $string;
    }


    function decrypt ($string) 
    {
        $this->rc4->key($this->key);
        $this->rc4->decrypt($string);
        return $string;
    }


}


?>
