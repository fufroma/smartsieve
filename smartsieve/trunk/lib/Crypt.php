<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */

/*
 * class Encrypt is a collection of SmartSieve's cryptograhic functions.
 */

class Encrypt {


   /*
    * Generate and return a secret key used in encryption/decryption.
    * If browser is accepting cookies, generate a key using a Mersenne Twister 
    * style randon number generator and store this in a cookie. If not, return 
    * a less random key based on session_id.
    */
    function generateKey () {

        if (isset($GLOBALS['HTTP_COOKIE_VARS']) &&
            isset($GLOBALS['HTTP_COOKIE_VARS'][session_name()])) {

            // seed the generator. should only happen once.
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


   /*
    * Retrieve the key from the cookie left by generateKey() if it exists,
    * or the less random one based on session_id.
    */
    function retrieveKey () {
        global $HTTP_COOKIE_VARS;

        if (isset($HTTP_COOKIE_VARS['key']))
            $key = $HTTP_COOKIE_VARS['key'];
        else
            $key = md5(session_id());

        return $key;
    }


   /*
    * Encrypt a piece of data.
    */
    function encrypt ($string,$key) {

        static $crypt;

        if (!isset($crypt) || !is_object($crypt)){
            $lib = Encrypt::getCryptLib();
            $args = array('key'=>$key);
            $crypt = SmartSieveCrypt::factory($lib, $args);
        }
        return $crypt->encrypt($string);
    }


   /*
    * Decrypt a piece of encrypted data.
    */
    function decrypt ($data,$key) {

        static $crypt;

        if (!isset($crypt) || !is_object($crypt)){
            $lib = Encrypt::getCryptLib();
            $args = array('key'=>$key);
            $crypt = SmartSieveCrypt::factory($lib, $args);
        }
        return $crypt->decrypt($data);
    }


   /*
    * Decide which encryption 'driver' we want the encryption functions
    * to use. ie. which set of cryptography libraries should be used.
    * Use the lib specified by $default->crypt_lib if acceptable. If not,
    * check for mcrypt, PEAR's Rc4, and PEAR's HCEMD5, in that order.
    */
    function getCryptLib ()
    {
        $crypt_lib = '';
        if ($GLOBALS['default']->crypt_lib != ''){
            foreach (array('MCRYPT','RC4','HCEMD5') as $lib){
                if ($GLOBALS['default']->crypt_lib == $lib)
                    $crypt_lib = $lib;
            }
        }
        if ($crypt_lib == ''){
            if (extension_loaded('mcrypt')){
                $crypt_lib = 'MCRYPT';
            }
            elseif (@include_once('Crypt/Rc4.php')){
                $crypt_lib = 'RC4';
            }
            elseif (@include_once('Crypt/HCEMD5.php')){
                $crypt_lib = 'HCEMD5';
            }
        }

        if ($crypt_lib == ''){
            AppSession::writeToLog('ERROR: cannot find a usable cryptography library.', LOG_ERR);
            echo 'ERROR: Cannot find a usable cryptography library. ' .
                        'Please read the INSTALL file for info on this.';
            exit;
        }
        AppSession::writeToLog('getCryptLib: found cryptography library ' . $crypt_lib, LOG_DEBUG);
        return $crypt_lib;
    }


}
/* end class Encrypt. */


/*
 * class SmartSieveCrypt is an abstracted class for encrypting small 
 * strings of text. It should instantiate a subclass to do the actual 
 * encryption and decryption, the choice of which is controlled by the 
 * $driver value.
 */

class SmartSieveCrypt {


   /*
    * Return a reference to an instance of an encryption driver object.
    */
    function &factory ($driver = '', $args = array()) {
        if ($driver == '')
            return new SmartSieveCrypt();

        require_once $GLOBALS['default']->lib_dir . '/Crypt/' . strtolower($driver) . '.php';

        $subclass = 'SmartSieveCrypt' . $driver;
        if (class_exists($subclass)){
            return new $subclass($args);
        }
        else {
            return false;
        }
    }


   /*
    * Encrypt function to be extended by classes which extend SmartSieveCrypt.
    */
    function encrypt ($string) {

        return $string;
    }


   /*
    * Decrypt function to be extended by classes which extend SmartSieveCrypt.
    */
    function decrypt ($string) {

        return $string;
    }


}


?>
