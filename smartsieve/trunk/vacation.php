<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
require "$default->lib_dir/sieve.lib";
require "$default->lib_dir/SmartSieve.lib";

session_name('SIEVE_SESSION');
@session_start();
$errstr = '';


// if a session does not exist, go to login page
if (!is_object($HTTP_SESSION_VARS['sieve']) || !$sieve->authenticate()) {
	header('Location: ' . $baseurl . 'login.php',true);
	exit;
}

// should have a valid session at this point

// open sieve connection
if (!$sieve->openSieveSession()) {
    print "ERROR: " . $sieve->connection->errstr . "<BR>\n";
    $sieve->writeToLog("ERROR: openSieveSession failed for " . $sieve->user .
        ': ' . $sieve->connection->errstr, LOG_ERROR);
    exit;
}


/* save vacation settings if requested. */

$action = AppSession::getFormValue('thisAction');

if ($action == 'enable') {
    if ($script->vacation){
        $script->vacation['status'] = 'on';
        /* write and save the new script. */
        if (!$script->updateScript($sieve->connection)) {
            $errstr .= "ERROR: " . $script->errstr . "<BR>\n";
            $sieve->writeToLog("ERROR: vacation.php: can't update script: "
                . $script->errstr, LOG_ERROR);
        }
        else {
            header('Location: ' . $baseurl . 'main.php',true);
            exit;
        }
    }
    else {
        $errstr .= "ERROR: vacation setting not yet saved.";
        $sieve->writeToLog('ERROR: vacation setting not yet saved.', LOG_ERROR);
    }
}
if ($action == 'disable') {
    if ($script->vacation){
        $script->vacation['status'] = 'off';
        /* write and save the new script. */
        if (!$script->updateScript($sieve->connection)) {
            $errstr .= "ERROR: " . $script->errstr . "<BR>\n";
            $sieve->writeToLog("ERROR: vacation.php: can't update script: "                . $script->errstr, LOG_ERROR);
        }
        else {
            header('Location: ' . $baseurl . 'main.php',true);
            exit;
        }
    }
    else {
        $errstr .= "ERROR: vacation setting not yet saved.";
	$sieve->writeToLog('ERROR: vacation setting not yet saved.', LOG_ERROR);
    }
}
if ($action == 'save') 
{
    $script->vacation['text'] = AppSession::getFormValue('text');
    $script->vacation['days'] = AppSession::getFormValue('days');
    $address = AppSession::getFormValue('addresses');
    $address = preg_replace("/\"|\\\/","",$address);
    $addresses = array();
    $addresses = preg_split("/\s*,\s*|\s+/",$address);
    $script->vacation['addresses'] = &$addresses;

    /* if checkRule() doesn't return an error, write the modified script. */
    if (!$ret = checkRule($script->vacation)){
        if (!$script->updateScript($sieve->connection)) {
            $errstr .= "ERROR: " . $script->errstr . "<BR>\n";
	    $sieve->writeToLog("ERROR: vacation.php: can't update script: "
		. $script->errstr, LOG_ERROR);
        }
        else {
	    header('Location: ' . $baseurl . 'main.php',true);
	    exit;
        }
    }
    else
	$errstr .= "ERROR: " . $ret . "<BR>\n";
}


?>

<HTML>
<HEAD><TITLE><? print $default->page_title; ?></TITLE>
<LINK HREF="<?php print $default->config_dir; ?>/smartsieve.css" REL="stylesheet" TYPE="text/css">
<?

require "$default->include_dir/vacation.js";

?>

</HEAD>


<BODY>

<FORM ACTION="<? print $default->baseurl ?>vacation.php" METHOD="post" NAME="thisVacation">

<TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="menuouter">
    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
      <TR>
        <TD CLASS="menu">
          &nbsp;
          <a href="<? print $default->baseurl; ?>login.php?reason=logout">Logout</a> |
          <a href="<? print $default->baseurl; ?>vacation.php">Vacation Messages</a> |
          <a href="<? print $default->baseurl; ?>main.php">Main</a> |
          <a href="<? print $default->baseurl; ?>rule.php">New Filter Rule</a> <?php if ($default->vacation_help_url){ ?>|
          <a href="<?php print $default->vacation_help_url; ?>">Help</a> <?php } /* endif. */ ?>

        </TD>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>
 
<BR>
<?php if ($errstr) {  ?>

<TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
  <TR>
    <TD CLASS="errors">
      <? print $errstr; ?>
    </TD>
  </TR>
</TABLE>

<BR>
<? } //end if $errstr ?>

<TABLE WIDTH="100%" CELLPADDING="1" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="statusouter">
    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
      <TR>
        <TD CLASS="status">&nbsp;User: <?print $sieve->user; ?></TD>
        <TD CLASS="status">&nbsp;Server: <?print $sieve->server; ?></TD>
        <TD CLASS="status">&nbsp;Script: <?print $sieve->scriptfile; ?></TD>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

<BR>

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="ruleinfo">
    <? if ($script->vacation) {
	 print "Edit Vacation Auto-respond settings";
       } 
       else print "Create New Vacation Auto-respond settings:"; 
    ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="<? if ($script->vacation['status'] == 'on') {print "ruleenabled\">ENABLED";} 
				else print "ruledisabled\">DISABLED"; ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="ruleinfo">
Vacation:
      </TD>
      <TD CLASS="heading"></TD>
    </TR>
    <TR>
      <TD NOWRAP="nowrap">
Auto-respond text:
      </TD>
      <TD NOWRAP="nowrap">
        <TEXTAREA NAME="text" ROWS="3" COLS="40" WRAP="hard" TABINDEX="1">
<? if ($script->vacation['text']) print $script->vacation['text']; ?>
</TEXTAREA>
      </TD>
    </TR>
    <TR>
      <TD>
Days:
      </TD>
      <TD>
        <SELECT NAME="days">
<?
if (!$default->max_vacation_days) $default->max_vacation_days = 10;
for ($i = 0; $i <= $default->max_vacation_days; $i++){
    $opt = "\t\t<OPTION ";
    if ($script->vacation['days'] == $i) $opt .= "SELECTED ";
    $opt .= "VALUE=\"$i\">$i</OPTION>\n";
    print $opt;
}
?>
        </SELECT>
      </TD>
    </TR>
    <TR>
      <TD>
Addresses:
      </TD>
      <TD>
        <INPUT TYPE="text" NAME="addresses" <? 
if (is_array($script->vacation['addresses'])) {
    print "VALUE=\"";
    $first = 1;
    foreach ($script->vacation['addresses'] as $address) {
      if (!$first) print ", ";
      print $address; 
      $first = 0;
    }
    print "\"";
}
?> SIZE="50">
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">
 
    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="options" COLSPAN="2">
        <BR>
        <A CLASS="option" HREF="" onclick="Submit('save'); return false;" onmouseover="status='Save Changes'; return true;" onmouseout="status='';">Save Changes</a>
<? if ($script->vacation) { ?>
         |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="status='Enable'; return true;" onmouseout="status='';">Enable</a>
         | 
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="status='Disable'; return true;" onmouseout="status='';">Disable</a>
<? } ?>
      </TD>
    </TR>
    </TABLE>
 
  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="thisAction" VALUE="">

</FORM>

<?

$sieve->closeSieveSession();


/* basic sanity checks on vacation rule.
 * any value returned will be an error msg.
 * note: we will only demand a value from user if no default is set in config.
 */
function checkRule($vacation) {
    global $default,$sieve;

    if (!$default->vacation_text && !$vacation['text'])
	return "please supply the message to send with auto-responses";
    if (!$default->vacation_days && !$vacation['days'])
	return "please select the number of vacation days";
    if (!$sieve->maildomain){
	foreach ($vacation['addresses'] as $addr){
	    if (preg_match("/.+\@.+/",$addr)) return 0;
	}
	// must have no addresses set.
	return "please supply at least one vacation address";
    }

    /* check values don't exceed acceptible sizes. */
    foreach ($vacation['addresses'] as $addr){
        if (strlen($addr) > $default->max_field_chars)
            return 'vacation address should not exceed ' . 
		$default->max_field_chars . ' characters.';
    }
    if (strlen($vacation['text']) > $default->max_textbox_chars)
	return 'vacation message should not exceed ' . 
	    $default->max_textbox_chars . ' characters.';

    /* complain if vacation days contains non-digits. */
    if (preg_match("/\D/",$vacation['days']))
	return 'vacation days must be a positive integer.';

    return 0;
}


?>

</BODY>
</HTML>

