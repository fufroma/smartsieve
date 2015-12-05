<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


/**
 * Class Crypto_RC4 extends the Crypto class and is a wrapper for 
 * the PEAR Crypt_Rc4 encryption library.
 *
 * @author  Stephen Grier <stephengrier@users.sourceforge.net>
 * @version $Revision$
 */
class Crypto_RC4 extends Crypto {


   /**
    * The rc4 object reference.
    * @var object reference
    * @access private
    */
	var $_rc4;

   /**
    * The secret key.
    * @var string
    * @access public
    */
	var $key;


   /**
    * Constructor
    *
    * @param array $args Additional parameters. Will use $args['key'].
    * @return void
    */
	function __construct($args=array())
	{
		require_once 'Crypt/Rc4.php';
		$this->key = $args['key'];
		$this->_rc4 = new Crypt_RC4($this->key);
	}

   /**
    * Encrypt a string.
    *
    * @param string $string Item to be encrypted
    * @return string The encrypted string
    */
	function encrypt($string)
	{
		$this->_rc4->key($this->key);
		$this->_rc4->crypt($string);
		return $string;
	}

   /**
    * Decrypt a string.
    *
    * @param string $string The encrypted string to decrypt
    * @return string The decrypted string
    */
	function decrypt($string)
	{
		$this->_rc4->key($this->key);
		$this->_rc4->decrypt($string);
		return $string;
	}

}

?>
