<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


/*
 * class SmartSieveCryptMCRYPT extends the SmartSieveCrypt class.
 * It allows SmartSieve to use the mcrypt library for encryption.
 */

class SmartSieveCryptMCRYPT extends SmartSieveCrypt {


    /* The mcrypt module descriptor. */
    var $td;

    /* secret key. */
    var $key;

    /* mcrypt mode. */
    var $mode;

    /* location of mode module. */
    var $mode_dir;

    /* mcrypt cipher. */
    var $cipher;

    /* location of algorithm module. */
    var $cipher_dir;


    /* constructor. */
    function SmartSieveCryptMCRYPT ($args = array()) 
    {
        $this->key = $args['key'];
        // FIXME: we currently only support the ECB mode, because the same IV
        // is needed for encrypt/decrypt, but we don't yet store it.
        $this->mode = isset($args['mode']) ? $args['mode'] : MCRYPT_MODE_ECB;
        $this->cipher = isset($args['cipher']) ? $args['cipher'] : MCRYPT_BLOWFISH;
        $this->cipher_dir = isset($args['cipher_dir']) ? $args['cipher_dir'] : '';
        $this->mode_dir = isset($args['mode_dir']) ? $args['mode_dir'] : '';
        $this->td = mcrypt_module_open($this->cipher,$this->cipher_dir,$this->mode,$this->mode_dir);
    }


    function encrypt ($string) 
    {
        // An IV is not needed with the ECB mode, but...
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_DEV_RANDOM);
        mcrypt_generic_init($this->td, $this->key, $iv);
        $encrypted_data = mcrypt_generic($this->td, $string);
        mcrypt_generic_deinit($this->td);
        return $encrypted_data;
    }


    function decrypt ($encrypted_data) 
    {
        // FIXME: can only use ECB mode until we can get the IV encrypt creates.
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_DEV_RANDOM);
        // Reinitialize the encryption buffer before decrypt.
        mcrypt_generic_init($this->td, $this->key, $iv);
        $decrypted = mdecrypt_generic($this->td, $encrypted_data);
        mcrypt_generic_deinit($this->td);
        // strip null chars added by mcrypt_generic.
        return rtrim($decrypted, "\0");
    }


}


?>
