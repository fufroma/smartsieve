<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


/**
 * Class Crypto_HCEMD5 extends the Crypto class and is a wrapper for
 * the PEAR Crypt_HCEMD5 encryption library.
 *
 * @author  Stephen Grier <stephengrier@users.sourceforge.net>
 * @version $Revision$
 */
class Crypto_HCEMD5 extends Crypto {


   /**
    * The hcemd5 object reference.
    * @var object reference
    * @access private
    */
    var $_hcemd5;


   /**
    * Constructor
    *
    * @param array $args Additional parameters. Will use $args['key'].
    * @return void
    */
    function __construct($args=array())
    {
        require_once 'Crypt/HCEMD5.php';
        $this->_hcemd5 = new Crypt_HCEMD5($args['key']);
    }

   /**
    * Encrypt a string.
    *
    * @param string $string Item to be encrypted
    * @return string The encrypted string
    */
    function encrypt($string)
    {
        $encrypted = $this->_hcemd5->encodeMimeSelfRand($string);
        return $encrypted;
    }

   /**
    * Decrypt a string.
    *
    * @param string $string The encrypted string to decrypt
    * @return string The decrypted string
    */
    function decrypt($string)
    {
        $decrypted = $this->_hcemd5->decodeMimeSelfRand($string);
        return $decrypted;
    }

}

?>
