<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


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

        require_once $GLOBALS['default']->lib_dir . '/Crypt/' . strtolower($driver) . '.php';

        $subclass = 'SmartSieveCrypt' . strtoupper($driver);
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
