<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * A script to test the Managesieve class.
 */

function printForm()
{
    ?>
<HTML>
<HEAD><TITLE>Testing class Managesieve</TITLE></HEAD>
<BODY>
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'];?>" METHOD="post">
    Server: <INPUT TYPE="text" NAME="server" VALUE="127.0.0.1"><BR>
    Port: <INPUT TYPE="text" NAME="port" VALUE="2000"><BR>
    Auth name: <INPUT TYPE="text" NAME="auth" VALUE=""><BR>
    Password: <INPUT TYPE="text" NAME="passwd" VALUE=""><BR>
    Authz user: <INPUT TYPE="text" NAME="authz" VALUE=""><BR>
    SASL method: <INPUT TYPE="text" NAME="sasl_mech" VALUE="plain"><BR>
    <INPUT TYPE="submit" NAME="submit" VALUE="Submit"><BR>
</FORM>
</BODY>
</HTML>
    <?php
    exit;
}

foreach (array('server','auth','passwd','authz','sasl_mech','port') as $var) {
    if (!isset($_POST[$var]) || $_POST[$var] === '') {
        printForm();
    }
    ${$var} = $_POST[$var];
}

include 'lib/Managesieve.php';

$managesieve = new Managesieve();
?>

<HTML><HEAD><TITLE>Testing class Managesieve</TITLE>
</HEAD>

<BODY>
<H2>Testing class Managesieve</H2>

<OL>
  <LI><B>Testing $managesieve->open(): </B>

<?php
if ($managesieve->open($server,$port) && is_resource($managesieve->_socket)) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}

echo "Implementation: " . $managesieve->_capabilities['implementation'] . '<BR>';
echo "SASL auth mechanisms: ";
foreach ($managesieve->_capabilities['sasl'] as $mech) {
    echo "$mech ";
}
echo '<BR>';
echo "Supported Sieve extensions: ";
foreach ($managesieve->_capabilities['extensions'] as $extn) {
    echo "$extn ";
}
if (isset($managesieve->_capabilities['unknown_banners'])) {
  foreach ($managesieve->_capabilities['unknown_banners'] as $u) {
    echo "Unknown banner: $u<BR>";
  }
}
echo '<BR>'; ?>
  </LI>

  <LI><B>Testing response when not authenticated: </B>

<?php
$script = $managesieve->getScript('default');
if (is_array($script)){
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['errstr'][0] != "Authenticate first") {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
}
echo "Response: " . $managesieve->getError() . '<BR>';
?>
  </LI>

  <LI>
    <B>Testing an authentication failure: </B>

<?php
$res = $managesieve->authenticate($auth,"awrongpasswd",$authz,$sasl_mech);
if ($res === true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO || ($managesieve->resp['errstr'][0] != 'Authentication Error' && $managesieve->resp['errstr'][0] != 'Authentication error')) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI>
    <B>Testing SASL auth methods: </B>
    <UL>
<?php
// Test each SASL auth mechanism.
foreach ($managesieve->_capabilities['sasl'] as $mech) {
?>
      <LI>
        <B>$managesieve->authenticate using <?php echo $mech;?>: </B>

<?php
$res = $managesieve->authenticate($auth,$passwd,$authz,strtolower($mech));
if ($res === true) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    $managesieve->logout();
    $managesieve->close();
    $res = $managesieve->open($server,$port);
    if ($res !== true) {
        echo "Response: " . $managesieve->getError() . '<BR>';
    }
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
      </LI>
<?php
} //end foreach.
?>
    </UL>
  </LI>

  <LI><B>Testing $managesieve->authenticate(): </B>

<?php
if ($managesieve->authenticate($auth,$passwd,$authz,$sasl_mech)){
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->capability(): </B>
                                                                                                                 
<?php
$ret = $managesieve->capability();
if (is_array($ret)) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Implementation: " . $managesieve->_capabilities['implementation'] . '<BR>';
    echo "SASL auth mechanisms: ";
    foreach ($managesieve->_capabilities['sasl'] as $mech) {
        echo "$mech ";
    }
    echo '<BR>';
    echo "Supported Sieve extensions: ";
    foreach ($managesieve->_capabilities['extensions'] as $extn) {
        echo "$extn ";
    }
    if (isset($managesieve->_capabilities['unknown_banners'])) {
      foreach ($managesieve->_capabilities['unknown_banners'] as $u) {
        echo "Unknown banner: $u<BR>";
      }
    }
    echo '<BR>';
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->listScripts(): </B>

<?php
$scripts = $managesieve->listScripts();
if (is_array($scripts)){
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    foreach ($scripts as $s=>$active) {
        echo $s . (($active) ? ' <- active' : '') . "<BR>";
    }
    if (!empty($managesieve->activescript)) {
        echo "Active script: " . $managesieve->activescript . '<BR>';
    }
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}

// Bail if test user has active scripts.
if (is_array($scripts) && count($scripts) != 0) {
    echo "<FONT COLOR=\"red\">User has existing scripts. Please run these tests using a test user with no existing scripts.</FONT><BR>";
    exit;
}
?>
  </LI>

<?php  /* Don't do havespave tests on broken Cyrus 2.0.x server. */
if (preg_match("/Cyrus timsieved (v1\.1|v2\.\d)/", $managesieve->_capabilities['implementation'])){
?>

  <LI><B>Testing $managesieve->haveSpace() with zero length script name: </B>

<?php
$res = $managesieve->haveSpace('',20);
if ($res === true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO || $managesieve->resp['errstr'][0] != 'Invalid script name') {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . "<BR>";
}
?>
  </LI>

  <LI><B>Testing $managesieve->haveSpace() with zero size script: </B>

<?php
$res = $managesieve->haveSpace('wobble',0);
if ($res === true) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->haveSpace() with excessive script size: </B>

<?php
$res = $managesieve->haveSpace('wobble',99999);
if ($res === true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO ||
     $managesieve->resp['code'] != RC_QUOTA) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

<?php } /* end if timsieve v1.0 */ ?>
  <LI><B>Testing putScript with parse error: </B>

<?php
$text = 'require ["fileinto"];
if address :contains "From" "wobble" {{
    fileinto "some.folder";
}';
if ($managesieve->putScript("test2", $text)) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO || 
      $managesieve->resp['errstr'][0] != "script errors:" && $managesieve->resp['errstr'][1] != "line 2: parse error") {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->putScript(): </B>
                                                                                        
<?php
$text = 'require ["fileinto"];
if address :contains "From" "wobble" {
    fileinto "some.folder";
}';
// Create 5 scripts which should fill our quota if sieve_maxscripts is 5.
for ($i=1; $i<6; $i++) {
  $res = $managesieve->putScript("test$i", $text);
  if ($res !== true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
    break;
  }
}
if ($res === true) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
}
?>
  </LI>

  <LI><B>Testing $managesieve->putScript() when over quota: </B>

<?php
$res = $managesieve->putScript("test6", $text);
if ($res === true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO ||
          $managesieve->resp['code'] != RC_QUOTA) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->listScripts(): </B>
                                                                                        
<?php
//FIXME
$scripts = $managesieve->listScripts();
if (!is_array($scripts)){
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    if ($scripts['test1'] !== false || $scripts['test2'] !== false || $scripts['test3'] !== false ||
      $scripts['test4'] !== false || $scripts['test5'] !== false) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
    foreach ($scripts as $s=>$active) {
        echo "$s" . (($active) ? "ACTIVE" : "") . "<BR>";
    }
}
?>
  </LI>

  <LI><B>Testing $managesieve->getScript() with nonexistent script: </B>

<?php
$res = $managesieve->getScript('nosuchscript');
if ($res === true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO ||
          $managesieve->resp['errstr'][0] != "Script doesn't exist") {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->getScript() with existing script: </B>
                                                                                          
<?php
$res = $managesieve->getScript('test5');
if ($res === false) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    if ($res['raw'] != $text ||
        $res['size'] != strlen($text) ) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
        echo "LEN: " . $res['size'] . '<BR>' . $res['raw'] . '<BR>';
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
}
?>
  </LI>

  <LI><B>Testing $managesieve->setActive() with no active script: </B>

<?php
$res = $managesieve->setActive();
if ($res === true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO || 
          $managesieve->resp['errstr'][0] != 'Unable to unlink active script') {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->setActive() with nonexistent script: </B>
                                                                                          
<?php
$res = $managesieve->setActive("nosuchscript");
if ($res === true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO ||
          $managesieve->resp['errstr'][0] != 'Script does not exist') {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->setActive() on test1: </B>
                                                                                          
<?php
$res = $managesieve->setActive("test1");
if ($res !== true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    $scripts = $managesieve->listScripts();
    if (array_search(true, $scripts) != 'test1') {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
        if (array_search(true, $scripts) === false) {
            echo "No active script<BR>";
        } else {
            "Active script: " . array_search(true, $scripts) . "<BR>";
        }
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
        echo "Active script: " . array_search(true, $scripts) . "<BR>";
    }
}
?>
  </LI>

  <LI><B>Testing $managesieve->setActive() with no script name: </B>
                                                                                          
<?php
$res = $managesieve->setActive();
if ($res !== true) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    $scripts = $managesieve->listScripts();
    if (array_search(true, $scripts) !== false) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
        echo "Active script: " . array_search(true, $scripts) . "<BR>";
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
}
?>
  </LI>

  <LI><B>Testing $managesieve->deleteScript() with nonexistent script: </B>

<?php
$res = $managesieve->deleteScript('nonexistent');
if ($res === true){
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO ||
          $managesieve->resp['errstr'][0] != 'Error deleting script' ) {
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->deleteScript() with zero length script name: </B>
                                                                                       
<?php
$res = $managesieve->deleteScript('');
if ($res === true){
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO ||
          $managesieve->resp['errstr'][0] != 'Invalid script name' ) {
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->deleteScript(): </B>

<?php
foreach ($scripts as $s=>$active) {
    $res = $managesieve->deleteScript($s);
    if ($res !== true){
        echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
        echo 'Response: ' . $managesieve->getError() . '<BR>';
        break;
    }
}
if ($res === true) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
}
?>
  </LI>

<?php
$large = "require [\"fileinto\"];\n";
$rule = "if address :contains \"From\" \"wobble\" {\n\tfileinto \"some.folder\";\n}\n";
$len = 0;
while ($len < 32000) {
    $large .= $rule;
    $len = strlen($large);
}
?>
  <LI><B>Testing $managesieve->putScript() with zero length script name: </B>
                                                                                       
                                                                                       
<?php
$res = $managesieve->putScript('');
if ($res === true){
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
} elseif ($managesieve->resp['state'] != F_NO || 
          $managesieve->resp['errstr'][0] != 'Invalid script name') {
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
}
?>
  </LI>

  <LI><B>Testing $managesieve->putScript() with zero byte script: </B>
                                                                                             
<?php
$res = $managesieve->putScript('zero', '');
if ($res !== true){
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
}
?>
  </LI>

  <LI><B>Testing $managesieve->getScript() with zero byte script: </B>
                                                                                             
<?php
$res = $managesieve->getScript('zero');
if ($res === false) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    if ($res['raw'] !== '' ||
        $res['size'] !== 0 ) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
        echo "LEN: " . $res['size'] . '<BR>' . $res['raw'] . '<BR>';
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
    $managesieve->deleteScript("zero");
}
?>
  </LI>

  <LI><B>Testing $managesieve->putScript() with <?php echo $len;?> byte script: </B>
                                                                                          
<?php
$res = $managesieve->putScript('large', $large);
if ($res !== true){
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
}
?>
  </LI>

  <LI><B>Testing $managesieve->getScript() with <?php echo $len;?> byte script: </B>

<?php
$res = $managesieve->getScript('large');
if ($res === false) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    if ($res['raw'] != $large ||
        $res['size'] != $len ) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
        echo "LEN: " . $res['size'] . '<BR>' . $res['raw'] . '<BR>';
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
    $managesieve->deleteScript("large");
}
?>
  </LI>

<?php
// Make script larger than sieve_maxscriptsize.
while ($len < 33000) {
    $large .= $rule;
    $len = strlen($large);
}
?>
  <LI><B>Testing $managesieve->putScript() with <?php echo $len;?> byte script: </B>
                                                                                          
<?php
$res = $managesieve->putScript('large', $large);
if ($res === true){
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    $managesieve->deleteScript("large");
} elseif ($managesieve->resp['state'] != F_NO || 
          ($managesieve->resp['errstr'][0] != 'Did not specify script data' &&
           $managesieve->resp['errstr'][0] != 'Did not specify legal script data length')) {
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
} else {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->putScript() with script name containing a space: </B>

<?php
$space = "if address :contains \"To\" \"Space\" {\ndiscard;\n}";
if ($managesieve->putScript('My Script', $space)) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}
                                                                                         
?>
  </LI>

  <LI><B>Testing $managesieve->getScript() with a script name containing a space: </B>

<?php
$res = $managesieve->getScript('My Script');
if ($res === false) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    if ($res['raw'] != $space) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
        echo "LEN: " . $res['size'] . '<BR>' . $res['raw'] . '<BR>';
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
}
?>
  </LI>

  <LI><B>Testing $managesieve->putScript() with script name containing quotes: </B>

<?php
$quotes = "if address :contains \"To\" \"Quotes\" {\ndiscard;\n}";
if ($managesieve->putScript('My"Script', $quotes)) {
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
}

?>
  </LI>

  <LI><B>Testing $managesieve->getScript() with a script name containing quotes: </B>

<?php
$res = $managesieve->getScript('My"Script');
if ($res === false) {
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    if ($res['raw'] != $quotes) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
        echo "LEN: " . $res['size'] . '<BR>' . $res['raw'] . '<BR>';
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
}
?>
  </LI>

  <LI><B>Testing $managesieve->listScripts(): </B>

<?php
$scripts = $managesieve->listScripts();
if (!is_array($scripts)){
    echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    echo "Response: " . $managesieve->getError() . '<BR>';
} else {
    if ($scripts['My Script'] !== false || $scripts['My"Script'] !== false) {
        echo "<FONT COLOR=\"red\">Test failed</FONT><BR>";
    } else {
        echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
    }
    foreach ($scripts as $s=>$active) {
        echo "$s" . (($active) ? "ACTIVE" : "") . "<BR>";
        $managesieve->deleteScript($s);
    }
}
?>
  </LI>

  <LI><B>Testing $managesieve->logout(): </B>

<?php
if ($managesieve->logout()){
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
}
?>
  </LI>

  <LI><B>Testing $managesieve->close(): </B>

<?php
if ($managesieve->close()){
    echo "<FONT COLOR=\"green\">Test Passed</FONT><BR>";
} else {
    echo "<FONT COLOR=\"red\">Test Failed</FONT><BR>";
    echo 'Response: ' . $managesieve->getError() . '<BR>';
}
?>
  </LI>
</OL>

</BODY>
</HTML>

