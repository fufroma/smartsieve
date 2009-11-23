<?php
/**
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
 * use_starttls: by default, SmartSieve will try to use TLS 
 * to protect the connection to the managesieve server. This will 
 * only work with PHP-5.1 and above built with OpenSSL support, 
 * and where the server supports it. Set this to false if you want
 * to disable TLS.
 *
 * starttls_auto_capability: some managesieve servers (notably 
 * Cyrus-imapd 2.2.10-2.3.10) did not issue a CAPABILITY response
 * following a successful STARTTLS, as required by sections 1.7 and
 * 2.2 of draft-martin-managesieve-12. SmartSieve should spot these
 * broken servers automatically, but if not set this to boolean
 * false for servers that do not issue an auto CAPABILITY response,
 * or boolean true for those that do. The default is true.
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
 * maildomain: If your users have email addresses of the form
 * username@yourdomain and you want to suggest this as a vacation
 * address to your users when they create a new vacation rule, set
 * this to your mail domain. Do not include the '@' character.
 * Note: you can also suggest vacation addresses to your users via
 * the get_email_addresses_hook option in config.php.
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
    'maildomain' => 'localhost'
);

$servers['example2'] = array(
    'display' => 'Example2',
    'server' => 'imap.example.co.uk',
    'sieveport' => '2000',
    'imapport' => '143',
    'maildomain' => ''
);

?>
