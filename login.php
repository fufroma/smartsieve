<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->config_dir/servers.php";
require "$default->lib_dir/sieve.lib";
require "$default->lib_dir/SmartSieve.lib";

session_name('SIEVE_SESSION');
@session_start();

// if a session already exists, go to main page
// unless failure or logout
if (isset($HTTP_SESSION_VARS['sieve']) && is_object($HTTP_SESSION_VARS['sieve'])) {

    if (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'logout') {
	if (!$HTTP_SESSION_VARS['sieve']->writeToLog("logout: " . 
				$HTTP_SESSION_VARS['sieve']->user, LOG_INFO))
	    print "ERROR: " . $HTTP_SESSION_VARS['sieve']->errstr . "<BR>";
	$HTTP_SESSION_VARS['sieve'] = null;
	session_unregister('sieve');
    }
    elseif (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'failure') {
	$HTTP_SESSION_VARS['sieve'] = null;
	session_unregister('sieve');
    }
    else {
	header('Location: ' . AppSession::setUrl('main.php'),true);
	exit;
    }
}


// create new session if login form submitted
if (isset($HTTP_POST_VARS['sieveuid']) && isset($HTTP_POST_VARS['passwd'])) {
    $sieve = new AppSession();
    if ($sieve->initialize() && $sieve->authenticate()) {
	// must have created session, and authenticated ok

	if (!$sieve->writeToLog("login: " . $sieve->user . ' [' . 
		$GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'] . '] {' . 
		$sieve->server . ':' . $sieve->sieveport . '}', LOG_INFO))
	    print "ERROR: " . $sieve->errstr . "<BR>";
	header('Location: ' . AppSession::setUrl('main.php'),true);
	exit;
    }

    if (!$sieve->writeToLog("FAILED LOGIN: " . $sieve->user . ' [' .
	$GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'] . '] {' .
	$sieve->server . ':' . $sieve->sieveport . '}', LOG_ERR))
      print "ERROR: " . $sieve->errstr . "<BR>";
    header('Location: ' . AppSession::setUrl('login.php?reason=failure'),true);
    exit;
}


// the main login page should go down here
// we assume no login has yet been submitted (or perhaps not filled in right).

?>

<HTML>
<HEAD><TITLE><?php print $default->page_title; ?></TITLE>
<LINK HREF="<?php print $default->config_dir; ?>/smartsieve.css" REL="stylesheet" TYPE="text/css">
<?php

include "$default->include_dir/login.js";

?>

</HEAD>

<BODY onload="setFocus()">


<CENTER>

<FORM ACTION="<?php echo AppSession::setUrl('login.php'); ?>" METHOD="post" NAME="login">

<TABLE WIDTH="300" CELLPADDING="5" BORDER="0" CELLSPACING="0">
  <TR>
    <TD CLASS="welcome" ALIGN="center" COLSPAN="2">
      <?php echo $default->login_page_heading; ?>
    </TD>
  </TR>

  <TR>
    <TD ALIGN="center" COLSPAN="2">&nbsp;
<?php
if (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'failure') { ?>
      Login failed! Please try again.
<?php }
elseif (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'logout'){ ?>
      You have been logged out.
<?php }

$tabindex = 1;
?>
    </TD>
  </TR>

<TR CLASS="menu">
    <TD ALIGN="right"> Sieve Username: 
    </TD>
    <TD ALIGN="left"><INPUT TYPE="text" tabindex="<?php print $tabindex;$tabindex++;?>" name="sieveuid">
    </TD>
</TR>
<TR CLASS="menu">
    <TD ALIGN="right"> Password:
    </TD>
    <TD ALIGN="left"><INPUT TYPE="password" TABINDEX="<?php print $tabindex;$tabindex++;?>" NAME="passwd">
    </TD>
</TR>

<?php
if ($default->user_select_server)
{
?>
<TR CLASS="menu">
    <TD ALIGN="right">Server:
    </TD>
    <TD ALIGN="left">
      <SELECT NAME="server" TABINDEX="<?php print $tabindex;?>">
<?php
    $tabindex++;
    $sel = true;
    foreach ($servers as $key => $val)
    {
	$opt = "\t<OPTION VALUE=\"$key\"";
	if ($sel) {
	    $opt .= " SELECTED";
	    $sel = false;
	}
	$opt .= ">" . $val['display'] . "</OPTION>\n";
	print $opt;
    }
    print "      </SELECT>\n    </TD>\n</TR>\n";
}
else {
    // take 1st entry as default server values
    $server_val = '';
    foreach ($servers as $key => $val)
    {
	if (!$server_val) $server_val = $key;
	else break;
    }
    print "<INPUT TYPE=\"hidden\" NAME=\"server\" VALUE=\"$server_val\">\n";
}

if ($default->user_supply_scriptfile)
{
?>
<TR CLASS="menu">
    <TD ALIGN="right">Script name:
    </TD>
    <TD ALIGN="left"><INPUT TYPE="text" TABINDEX="<?php print $tabindex;?>" NAME="scriptfile" VALUE="<?php print $default->scriptfile;?>">
    </TD>
</TR>
<?php
    $tabindex++;
}
else print "<INPUT TYPE=\"hidden\" NAME=\"scriptfile\" VALUE=\"".$default->scriptfile."\">\n";

?>

<TR CLASS="menu">
    <TD ALIGN="center" COLSPAN="2"><INPUT TYPE="submit" NAME="submit" TABINDEX="<?php print $tabindex;$tabindex++;?>" VALUE="Log In">
    </TD>
<TR>
</TABLE>
</CENTER>

</FORM>

<?php

if (is_readable($default->config_dir . '/motd.php')){
    include $default->config_dir . '/motd.php';
}

?>


</BODY>
</HTML>


