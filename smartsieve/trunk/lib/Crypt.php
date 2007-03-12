<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */

 
/**
 * Class Crypto is an abstracted class for encrypting small 
 * strings of text. It should instantiate a subclass to do the actual 
 * encryption and decryption, the choice of which is controlled by the 
 * $driver value.
 *
 * @author Stephen Grier <stephengrier@users.sourceforge.net>
 * @version $Revision$
 */
class Crypto {


   /**
    * Constructor
    *
    * Note, this will only be called if Crypto::getCryptLib() cannot find a 
    * crypto library to use and Crypto::factory() can therefore not instantiate 
    * a subclass. This parent class does not do any encryption.
    *
    * @param array $args Additional parameters.
    * @return void
    */
    function Crypto($args=array())
    {
        SmartSieve::log('Crypto: Crypto parent class does not provide encryption', LOG_WARNING);
        SmartSieve::setError(SmartSieve::text('The cryptographic library used is not providing adequate encryption'));
    }

   /**
    * Return a reference to an instance of a crypto driver object.
    *
    * @param string $driver The crypto library to use (see lib/Crypt/ dir).
    * @param array $args Parameters to pass to crypto library.
    * @return object Reference to a Crypto object instance
    */
    function &factory($driver=null, $args=array())
    {
        if ($driver !== null) {
            @require_once sprintf('%s/Crypt/%s.php', SmartSieve::getConf('lib_dir', 'lib'), strtolower($driver));
            $subclass = sprintf('Crypto_%s', strtoupper($driver));
            if (class_exists($subclass)){
                $crypto = new $subclass($args);
                return $crypto;
            }
        }
        $crypto = new Crypto($args);
        return $crypto;
    }

   /**
    * Encrypt a string.
    *
    * @param string $string Item to be encrypted
    * @return string The encrypted string
    */
    function encrypt($string)
    {
        if (isset($this) && is_object($this)) {
            return $string;
        }
        static $crypto;
        if (!isset($crypto) || !is_object($crypto)) {
            $args = SmartSieve::getConf('crypt_args', array());
            $args['key'] = Crypto::generateKey();
            $crypto = Crypto::factory(Crypto::getCryptLib(), $args);
        }
        return $crypto->encrypt($string);
    }

   /**
    * Decrypt a string encrypted by Crypt::encrypt().
    *
    * @param string $string The encrypted string to decrypt
    * @return string The decrypted string
    */
    function decrypt($string)
    {
    if (isset($this) && is_object($this)) {
            return $string;
        }
        static $crypto;
        if (!isset($crypto) || !is_object($crypto)) {
            $args = SmartSieve::getConf('crypt_args', array());
            $args['key'] = Crypto::getKey();
            $crypto = Crypto::factory(Crypto::getCryptLib(), $args);
        }
        return $crypto->decrypt($string);
    }

   /**
    * Generate a secret key.
    *
    * Generate a secret key to use in encryption/decryption. If browser is
    * accepting cookies, generate a 32 bit key using a Mersenne Twister
    * style random number generator and store this in a cookie. If not,
    * return a less random key based on session_id.
    *
    * @return string A 32 bit secret key. Possibly sets a cookie value.
    */
    function generateKey()
    {
        static $srand;
        if (isset($_COOKIE) && isset($_COOKIE[session_name()])) {
            if (isset($_COOKIE['smartsieve_key'])) {
                $key = $_COOKIE['smartsieve_key'];
            } else {
                // Seed the generator. Should only happen once.
                // As of PHP-4.2 this is no longer necessary.
                if (!isset($srand)) {
                    mt_srand((double)microtime() * 1000000);
                    $srand = true;
                }
                $key = md5(uniqid(mt_rand(), 1));
                $_COOKIE['smartsieve_key'] = $key;
                setcookie('smartsieve_key', $key, 0, SmartSieve::getConf('cookie_path', ''),
                    SmartSieve::getConf('cookie_domain', ''), 0);
            }
        } else {
            // Looks like browser doesn't have cookies enabled. Use a standard value.
            $key = md5(session_id());
            setcookie('smartsieve_key', $key, 0, SmartSieve::getConf('cookie_path', ''),
                SmartSieve::getConf('cookie_domain', ''), 0);
        }
        return $key;
    }

   /**
    * Retrieve the key from the cookie if it exists, or the less random one
    * based on session_id if not.
    *
    * @return string The 32 bit secret key
    */
    function getKey()
    {
        if (isset($_COOKIE['smartsieve_key'])) {
            return $_COOKIE['smartsieve_key'];
        }
        return md5(session_id());
    }

   /**
    * Select which encryption library to use. Use the lib specified in the
    * config file if set. If not, check for mcrypt, PEAR's Rc4, and PEAR's
    * HCEMD5, in that order. Function mcrypt_module_open is only found in
    * libmcrypt 2.4.x and above.
    *
    * @return mixed The crypto library to use, or null
    */
    function getCryptLib()
    {
        $libs = array();
        if (extension_loaded('mcrypt') && function_exists('mcrypt_module_open')) {
            $libs[] = 'mcrypt';
        }
        if (@include_once('Crypt/Rc4.php')) {
            $libs[] = 'rc4';
        }
        if (@include_once('Crypt/HCEMD5.php')) {
            $libs[] = 'hcemd5';
        }
        if (!empty($libs)) {
            $lib = $libs[0];
            if (($cLib = SmartSieve::getConf('crypt_lib')) !== null &&
                in_array($cLib, $libs)) {
                $lib = $cLib;
            }
            SmartSieve::log(sprintf('getCryptLib: using %s', $lib), LOG_DEBUG);
            return $lib;
        }
        SmartSieve::log('getCryptLib: no usable cryptographic library', LOG_WARNING);
        return null;
    }

}

?>
