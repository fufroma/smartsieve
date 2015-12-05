#!/usr/local/bin/php -q
<?php
/**
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * A script to test the Managesieve class.
 * 
 * Run by doing:
 *
 * ./ms-test.php auth authz server 2000 plain
 *
 * or if necessary...
 *
 * /usr/bin/php ms-test.php auth authz server 2000 plain
 */


$ver = '$Revision$';
// Path to Managesieve.php library. Do not include trailing slash.
$path_to_lib = '../lib';

ini_set('display_errors', false);

if ($argc < 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) :
?>
Usage: ./ms-test.php [auth] [authz] <server> <port> <sasl method>
<?php
	exit;
endif;

$auth = $argv[1];
$authz = $argv[2];
$server = (isset($argv[3])) ? $argv[3] : '127.0.0.1';
$port = (isset($argv[4])) ? $argv[4] : '2000';
$sasl_mech = (isset($argv[5])) ? $argv[5] : 'plain';

// Prompt for a password.
echo "Password: ";
system("stty -echo");
$passwd = trim(fgets(STDIN));
system("stty echo");

include $path_to_lib . '/Managesieve.php';

$managesieve = new Managesieve();

?> 

Managesieve: <?php echo $managesieve->getVersion(); ?> 
PHP: <?php echo phpversion(); ?> 
Zend: <?php echo zend_version(); ?> 
ms-test: <?php echo $ver; ?> 
System: <?php echo php_uname(); ?> 

Testing class Managesieve
=========================

* Testing $managesieve->open(): <?php

if ($managesieve->open($server, $port) && is_resource($managesieve->_socket)) {
	echo "Test Passed\n";
} else {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
  * Implementation: <?php echo $managesieve->_capabilities['implementation']; ?> 
  * SASL auth mechanisms: <?php
foreach ($managesieve->_capabilities['sasl'] as $mech) {
	echo "$mech ";
}?> 
  * Supported Sieve extensions: <?php
foreach ($managesieve->_capabilities['extensions'] as $extn) {
	echo "$extn ";
}?> 
<?php
if (isset($managesieve->_capabilities['unknown_banners'])) {
  foreach ($managesieve->_capabilities['unknown_banners'] as $u) {
	echo "  * Unknown banner: $u\n";
  }
}?>
<?php if (in_array('starttls', $managesieve->_capabilities) &&
		  function_exists('stream_socket_enable_crypto')) :
?>
* Testing $managesieve->starttls(): <?php

$res = $managesieve->starttls();
if ($res !== true){
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
}
endif;?>
* Testing response when not authenticated: <?php

$script = $managesieve->getScript('default');
if (is_array($script)){
	echo "Test failed\n";
} elseif ($managesieve->resp['errstr'][0] != "Authenticate first") {
	echo "Test failed\n";
} else {
	echo "Test Passed\n";
}
echo sprintf("  Response: %s\n", $managesieve->getError());
?>
* Testing an authentication failure: <?php

$res = $managesieve->authenticate($auth, "awrongpasswd", $authz, $sasl_mech);
if ($res === true) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO || ($managesieve->resp['errstr'][0] != 'Authentication Error' && $managesieve->resp['errstr'][0] != 'Authentication error')) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing SASL auth methods: 
<?php

// Test each SASL auth mechanism.
foreach ($managesieve->_capabilities['sasl'] as $mech) : ?>
  * $managesieve->authenticate() using <?php echo $mech; ?>: <?php

	$res = $managesieve->authenticate($auth, $passwd, $authz, strtolower($mech));
	if ($res === true) {
		echo "Test Passed\n";
		$managesieve->logout();
		$managesieve->close();
		$res = $managesieve->open($server, $port);
		if ($res !== true) {
			echo sprintf("  Response: %s\n", $managesieve->getError());
		}
	} else {
		echo "Test failed\n";
		echo sprintf("  Response: %s\n", $managesieve->getError());
	}
endforeach;
?>
* Testing $managesieve->authenticate(): <?php

if ($managesieve->authenticate($auth, $passwd, $authz, $sasl_mech)) {
	echo "Test Passed\n";
} else {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->capability(): <?php

$ret = $managesieve->capability();
if (is_array($ret)) {
	echo "Test Passed\n";
	echo sprintf("  * Implementation: %s\n", $managesieve->_capabilities['implementation']);
	echo "  * SASL auth mechanisms: ";
	foreach ($managesieve->_capabilities['sasl'] as $mech) {
		echo "$mech ";
	}
	echo "\n";
	echo "  * Supported Sieve extensions: ";
	foreach ($managesieve->_capabilities['extensions'] as $extn) {
		echo "$extn ";
	}
	echo "\n";
	if (isset($managesieve->_capabilities['unknown_banners'])) {
	  foreach ($managesieve->_capabilities['unknown_banners'] as $u) {
		echo "  * Unknown banner: $u\n";
	  }
	}
} else {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->listScripts(): <?php

$scripts = $managesieve->listScripts();
if (is_array($scripts)){
	echo "Test Passed\n";
	foreach ($scripts as $s=>$active) {
		echo sprintf("%s%s\n", $s, ($active) ? ' <- active' : '');
	}
	if (!empty($managesieve->activescript)) {
		echo sprintf("  * Active script: %s\n", $managesieve->activescript);
	}
} else {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}

// Bail if test user has active scripts.
if (is_array($scripts) && count($scripts) != 0) {
	echo "User has existing scripts. Please run these tests using a test account with no existing scripts.\n";
	exit;
}

/* Don't do havespave tests on broken Cyrus 2.0.x server. */
if (preg_match("/Cyrus timsieved (v1\.1|v2\.\d)/", $managesieve->_capabilities['implementation'])) :
?>
* Testing $managesieve->haveSpace() with zero length script name: <?php

$res = $managesieve->haveSpace('', 20);
if ($res === true) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO || $managesieve->resp['errstr'][0] != 'Invalid script name') {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->haveSpace() with zero size script: <?php

$res = $managesieve->haveSpace('wobble', 0);
if ($res === true) {
	echo "Test Passed\n";
} else {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->haveSpace() with excessive script size: <?php

$res = $managesieve->haveSpace('wobble', 99999);
if ($res === true) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO ||
	 $managesieve->resp['code'] != RC_QUOTA) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
endif; /* end if timsieved v1.0 */ ?>
* Testing putScript with parse error: <?php

$text = 'require ["fileinto"];
if address :contains "From" "wobble" {{
    fileinto "some.folder";
}';
if ($managesieve->putScript("test2", $text)) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO || 
	  $managesieve->resp['errstr'][0] != "script errors:" && $managesieve->resp['errstr'][1] != "line 2: parse error") {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->putScript(): <?php

$text = 'require ["fileinto"];
if address :contains "From" "wobble" {
    fileinto "some.folder";
}';
// Create 5 scripts which should fill our quota if sieve_maxscripts is 5.
for ($i = 1; $i < 6; $i++) {
  $res = $managesieve->putScript("test$i", $text);
  if ($res !== true) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
	break;
  }
}
if ($res === true) {
	echo "Test Passed\n";
}
?>
* Testing $managesieve->putScript() when over quota: <?php

$res = $managesieve->putScript("test6", $text);
if ($res === true) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO ||
		  $managesieve->resp['code'] != RC_QUOTA) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->listScripts(): <?php

//FIXME
$scripts = $managesieve->listScripts();
if (!is_array($scripts)){
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	if ($scripts['test1'] !== false || $scripts['test2'] !== false || $scripts['test3'] !== false ||
	  $scripts['test4'] !== false || $scripts['test5'] !== false) {
		echo "Test failed\n";
	} else {
		echo "Test Passed\n";
	}
	foreach ($scripts as $s=>$active) {
		echo sprintf("%s%s\n", $s, ($active) ? " <- active" : "");
	}
}
?>
* Testing $managesieve->getScript() with nonexistent script: <?php

$res = $managesieve->getScript('nosuchscript');
if ($res === true) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO ||
		  $managesieve->resp['errstr'][0] != "Script doesn't exist") {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->getScript() with existing script: <?php

$res = $managesieve->getScript('test5');
if ($res === false) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	if ($res['raw'] != $text ||
		$res['size'] != strlen($text) ) {
		echo "Test failed\n";
		echo sprintf("LEN: %s\n%s\n", $res['size'], $res['raw']);
	} else {
		echo "Test Passed\n";
	}
}
?>
* Testing $managesieve->setActive() with no active script: <?php

$res = $managesieve->setActive();
if ($res === true) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO || 
		  $managesieve->resp['errstr'][0] != 'Unable to unlink active script') {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->setActive() with nonexistent script: <?php

$res = $managesieve->setActive("nosuchscript");
if ($res === true) {
	echo "Test failed\n";
} elseif ($managesieve->resp['state'] != F_NO ||
		  $managesieve->resp['errstr'][0] != 'Script does not exist') {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->setActive() on test1: <?php

$res = $managesieve->setActive("test1");
if ($res !== true) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	$scripts = $managesieve->listScripts();
	if (array_search(true, $scripts) != 'test1') {
		echo "Test failed\n";
		if (array_search(true, $scripts) === false) {
			echo "  No active script\n";
		} else {
			echo sprintf("  Active script: %s\n", array_search(true, $scripts));
		}
	} else {
		echo "Test Passed\n";
		echo sprintf("  Active script: %s\n", array_search(true, $scripts));
	}
}
?>
* Testing $managesieve->setActive() with no script name: <?php

$res = $managesieve->setActive();
if ($res !== true) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	$scripts = $managesieve->listScripts();
	if (array_search(true, $scripts) !== false) {
		echo "Test failed\n";
		echo "  Active script: " . array_search(true, $scripts) . "\n";
	} else {
		echo "Test Passed\n";
	}
}
?>
* Testing $managesieve->deleteScript() with nonexistent script: <?php

$res = $managesieve->deleteScript('nonexistent');
if ($res === true){
	echo "Test Failed\n";
} elseif ($managesieve->resp['state'] != F_NO ||
		  $managesieve->resp['errstr'][0] != 'Error deleting script' ) {
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->deleteScript() with zero length script name: <?php

$res = $managesieve->deleteScript('');
if ($res === true){
	echo "Test Failed\n";
} elseif ($managesieve->resp['state'] != F_NO ||
		  $managesieve->resp['errstr'][0] != 'Invalid script name' ) {
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->deleteScript(): <?php

foreach ($scripts as $s=>$active) {
	$res = $managesieve->deleteScript($s);
	if ($res !== true){
		echo "Test Failed\n";
		echo sprintf("  Response: %s\n", $managesieve->getError());
		break;
	}
}
if ($res === true) {
	echo "Test Passed\n";
}

$large = "require [\"fileinto\"];\n";
$rule = "if address :contains \"From\" \"wobble\" {\n\tfileinto \"some.folder\";\n}\n";
$len = 0;
while ($len < 32000) {
	$large .= $rule;
	$len = strlen($large);
}
?>
* Testing $managesieve->putScript() with zero length script name: <?php

$res = $managesieve->putScript('');
if ($res === true){
	echo "Test Failed\n";
} elseif ($managesieve->resp['state'] != F_NO || 
		  $managesieve->resp['errstr'][0] != 'Invalid script name') {
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
}
?>
* Testing $managesieve->putScript() with zero byte script: <?php

$res = $managesieve->putScript('zero', '');
if ($res !== true){
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
}
?>
* Testing $managesieve->getScript() with zero byte script: <?php

$res = $managesieve->getScript('zero');
if ($res === false) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	if ($res['raw'] !== '' ||
		$res['size'] !== 0 ) {
		echo "Test failed\n";
		echo sprintf("LEN: %s\n%s\n", $res['size'], $res['raw']);
	} else {
		echo "Test Passed\n";
	}
	$managesieve->deleteScript("zero");
}
?>
* Testing $managesieve->putScript() with <?php echo $len;?> byte script: <?php

$res = $managesieve->putScript('large', $large);
if ($res !== true){
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
}
?>
* Testing $managesieve->getScript() with <?php echo $len;?> byte script: <?php

$res = $managesieve->getScript('large');
if ($res === false) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	if ($res['raw'] != $large ||
		$res['size'] != $len ) {
		echo "Test failed\n";
		echo sprintf("LEN: %s\n%s\n", $res['size'], $res['raw']);
	} else {
		echo "Test Passed\n";
	}
	$managesieve->deleteScript("large");
}

// Make script larger than sieve_maxscriptsize.
while ($len < 33000) {
	$large .= $rule;
	$len = strlen($large);
}
?>
* Testing $managesieve->putScript() with <?php echo $len;?> byte script: <?php

$res = $managesieve->putScript('large', $large);
if ($res === true){
	echo "Test Failed\n";
	$managesieve->deleteScript("large");
} elseif ($managesieve->resp['state'] != F_NO || 
		  ($managesieve->resp['errstr'][0] != 'Did not specify script data' &&
		   $managesieve->resp['errstr'][0] != 'Did not specify legal script data length')) {
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	echo "Test Passed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->putScript() with script name containing a space: <?php

$space = "if address :contains \"To\" \"Space\" {\ndiscard;\n}";
if ($managesieve->putScript('My Script', $space)) {
	echo "Test Passed\n";
} else {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->getScript() with a script name containing a space: <?php

$res = $managesieve->getScript('My Script');
if ($res === false) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	if ($res['raw'] != $space) {
		echo "Test failed\n";
		echo sprintf("LEN: %s\n%s\n", $res['size'], $res['raw']);
	} else {
		echo "Test Passed\n";
	}
}
?>
* Testing $managesieve->putScript() with script name containing quotes: <?php

$quotes = "if address :contains \"To\" \"Quotes\" {\ndiscard;\n}";
if ($managesieve->putScript('My"Script', $quotes)) {
	echo "Test Passed\n";
} else {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->getScript() with a script name containing quotes: <?php

$res = $managesieve->getScript('My"Script');
if ($res === false) {
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	if ($res['raw'] != $quotes) {
		echo "Test failed\n";
		echo sprintf("LEN: %s\n%s\n", $res['size'], $res['raw']);
	} else {
		echo "Test Passed\n";
	}
}
?>
* Testing $managesieve->listScripts(): <?php

$scripts = $managesieve->listScripts();
if (!is_array($scripts)){
	echo "Test failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
} else {
	if ($scripts['My Script'] !== false || $scripts['My"Script'] !== false) {
		echo "Test failed\n";
	} else {
		echo "Test Passed\n";
	}
	foreach ($scripts as $s=>$active) {
		echo sprintf("%s%s\n", $s, ($active) ? " <- active" : "");
		$managesieve->deleteScript($s);
	}
}
?>
* Testing $managesieve->logout(): <?php

if ($managesieve->logout()){
	echo "Test Passed\n";
} else {
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
* Testing $managesieve->close(): <?php

if ($managesieve->close()){
	echo "Test Passed\n";
} else {
	echo "Test Failed\n";
	echo sprintf("  Response: %s\n", $managesieve->getError());
}
?>
