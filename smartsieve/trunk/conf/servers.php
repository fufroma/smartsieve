<?php
/*
 * servers.php: this file contains the configurations for 
 * the cyrus servers we can connect to. If you have set the 
 * $default->user_select_server option in conf.php to true, 
 * each of the servers below will appear in a select box on 
 * the login page. If you've set this to false, then the 
 * first entry below will be used as the default and no 
 * select box will appear on the login page.
 *
 * imapport: port to connect to imapd; usually 143. If you
 * want to do imap-ssl set this to '993/imap/ssl'. Note that 
 * if you are using a self-signed certificate on your imap 
 * server, you sould set this to '993/imap/novalidate-cert'
 * Also, if you have compiled the c-client libraries with ssl
 * support and you are using php-4.1.2 or later with imap-ssl
 * enabled, you will need to set this to '143/imap/notls'.
 *
 * maildomain: ie. username@maildomain. You should not include 
 * the @ character. if this is anything other than empty, this 
 * will be used in vacation auto-responses when no addresses 
 * are supplied, rather than demanding the user supply one.
 *
 * $Id$
 */

$servers['example'] = array(
    'display' => 'My Example Server',
    'server' => '127.0.0.1',
    'sieveport' => '2000',
    'imapport' => '143',
    'alt_namespace' => 'false',
    'maildomain' => 'localhost'
);

$servers['example2'] = array(
    'display' => 'Example2',
    'server' => 'imap.example.co.uk',
    'sieveport' => '2000',
    'imapport' => '143',
    'alt_namespace' => 'true',
    'maildomain' => ''
);

?>
