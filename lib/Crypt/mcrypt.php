<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


/**
 * Class Crypto_MCRYPT extends the Crypto class and is a wrapper for
 * the mcrypt library.
 *
 * @author  Stephen Grier <stephengrier@users.sourceforge.net>
 * @version $Revision$
 */
class Crypto_MCRYPT extends Crypto {


   /**
    * The mcrypt module descriptor.
    * @var resource descriptor
    * @access private
    */
    var $_td;

   /**
    * Secret key
    * @var string
    * @access public
    */
    var $key;

   /**
    * mcrypt mode
    * @var string
    * @access public
    */
    var $mode;

   /**
    * mcrypt modes directory
    * @var string
    * @access public
    */
    var $mode_dir;

   /**
    * mcrypt cipher
    * @var string
    * @access public
    */
    var $cipher;

   /**
    * mcrypt ciphers directory
    * @var string
    * @access public
    */
    var $cipher_dir;


   /**
    * Constructor
    *
    * @param array $args Additional parameters. Will use $args['key'].
    * @return void
    */
    function Crypto_MCRYPT($args=array())
    {
        $this->key = $args['key'];
        // FIXME: we currently only support the ECB mode, because the same IV
        // is needed for encrypt/decrypt, but we don't yet store it.
        $this->mode = isset($args['mode']) ? $args['mode'] : MCRYPT_MODE_ECB;
        $this->cipher = isset($args['cipher']) ? $args['cipher'] : MCRYPT_BLOWFISH;
        $this->cipher_dir = isset($args['cipher_dir']) ? $args['cipher_dir'] : '';
        $this->mode_dir = isset($args['mode_dir']) ? $args['mode_dir'] : '';
        $this->_td = mcrypt_module_open($this->cipher, $this->cipher_dir, $this->mode, $this->mode_dir);
    }

   /**
    * Encrypt a string.
    *
    * @param string $string Item to be encrypted
    * @return string The encrypted string
    */
    function encrypt($string)
    {
        // An IV is not needed with the ECB mode, but...
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->_td), MCRYPT_DEV_RANDOM);
        mcrypt_generic_init($this->_td, $this->key, $iv);
        $encrypted_data = mcrypt_generic($this->_td, $string);
        mcrypt_generic_deinit($this->_td);
        return $encrypted_data;
    }

   /**
    * Decrypt a string.
    *
    * @param string $encrypted_data The encrypted data to decrypt
    * @return string The decrypted string
    */
    function decrypt($encrypted_data)
    {
        // FIXME: can only use ECB mode until we can get the IV encrypt creates.
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->_td), MCRYPT_DEV_RANDOM);
        // Reinitialize the encryption buffer before decrypt.
        mcrypt_generic_init($this->_td, $this->key, $iv);
        $decrypted = mdecrypt_generic($this->_td, $encrypted_data);
        mcrypt_generic_deinit($this->_td);
        // strip null chars added by mcrypt_generic.
        return rtrim($decrypted, "\0");
    }

}

?>
