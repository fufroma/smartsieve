<?php
/*
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */

/**
 * Class Crypto_OPENSSL extends the Crypto class and uses the OpenSSL
 * extension (bundled with PHP) to encrypt/decrypt strings using
 * AES-256-CBC. It replaces the old mcrypt driver, since the mcrypt
 * extension was removed in PHP 7.2.
 *
 * @version $Revision$
 */
class Crypto_OPENSSL extends Crypto {

   /**
    * The cipher method to use.
    *
    * @var string
    */
	var $cipher = 'aes-256-cbc';

   /**
    * The binary encryption key.
    *
    * @var string
    */
	var $key = '';

   /**
    * Constructor.
    *
    * @param array $args Additional parameters. Will use $args['key'].
    * @return void
    */
	public function __construct($args = array())
	{
		$key = isset($args['key']) ? $args['key'] : '';
		/* Derive a fixed-length binary key from the supplied key. */
		$this->key = hash('sha256', $key, true);
	}

   /**
    * Encrypt a string.
    *
    * @param string $string Item to be encrypted
    * @return string Base64 encoded string: IV followed by ciphertext
    */
	public function encrypt($string)
	{
		$ivlen = openssl_cipher_iv_length($this->cipher);
		$iv = random_bytes($ivlen);
		$ct = openssl_encrypt($string, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);
		return base64_encode($iv . $ct);
	}

   /**
    * Decrypt a string encrypted by Crypto_OPENSSL::encrypt().
    *
    * @param string $string The encrypted string to decrypt
    * @return string The decrypted string, or '' on failure
    */
	public function decrypt($string)
	{
		$raw = base64_decode($string, true);
		$ivlen = openssl_cipher_iv_length($this->cipher);
		if ($raw === false || strlen($raw) <= $ivlen) {
			return '';
		}
		$iv = substr($raw, 0, $ivlen);
		$pt = openssl_decrypt(substr($raw, $ivlen), $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);
		return ($pt === false) ? '' : $pt;
	}

}
