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
 * display: the name to be displayed in the drop-down list of 
 * servers on the login page (if $default->user_select_server 
 * is set to true in conf/conf.php).
 *
 * imapport: port to connect to imapd; usually 143. If you
 * want to do imap-ssl set this to '993/imap/ssl', or even 
 * '143/imap/tls'. Note that if you are using a self-signed 
 * certificate on your imap server, you sould set this to 
 * '993/imap/ssl/novalidate-cert'. Also, if you have compiled 
 * the c-client libraries with ssl support and you are using 
 * php-4.1.2 or later with imap-ssl enabled, you will need to 
 * set this to '143/imap/notls' if you do not want imap-ssl.
 *
 * alt_namespace: set this to true if the server is using the 
 * alternative namespace. You should also set namespace_user_prefix 
 * and namespace_shared_prefix to the prefixes the server uses 
 * for the other users namespace and the shared namespaces 
 * respectively.
 *
 * maildomain: ie. username@maildomain. You should not include 
 * the @ character. if require_vacation_addresses is true in 
 * conf/config.php, this will be used in the :addresses vacation 
 * rule argument when the user does not supply any addresses. 
 * if empty, SmartSieve will demand the user supply at least one. 
 * if require_vacation_addresses is false in conf/config.php 
 * this value has no effect.
 *
 * auth_domain: You can use this option to set a domain value 
 * which will be appended to usernames to make them fully-qualified. 
 * This is useful if your server supports virtual domains but can 
 * only determine the domain from the username, and you want to save 
 * your users the trouble of typing their fully-qualified username 
 * themselves.
 *
 * $Id$
 */

$servers['example'] = array(
    'display' => 'My Example Server',
    'server' => '127.0.0.1',
    'sieveport' => '2000',
    'imapport' => '143/imap/notls',
    'alt_namespace' => false,
    'maildomain' => 'localhost'
);

$servers['example2'] = array(
    'display' => 'Example2',
    'server' => 'imap.example.co.uk',
    'sieveport' => '2000',
    'imapport' => '143',
    'alt_namespace' => true,
    'namespace_user_prefix' => 'user',
    'namespace_shared_prefix' => 'shared',
    'maildomain' => ''
);

?>
