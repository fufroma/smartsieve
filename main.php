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
$errors = array();
$msgs = array();

$sieve = &$GLOBALS['HTTP_SESSION_VARS']['sieve'];
$script = &$GLOBALS['HTTP_SESSION_VARS']['script'];

// if a session does not exist, go to login page
if (!is_object($sieve) || !$sieve->authenticate()) {
	header('Location: ' . AppSession::setUrl('login.php'),true);
	exit;
}

// should have a valid session at this point

// start sieve session, and get the rules via the script object
if (!$sieve->openSieveSession()) {
    print "ERROR: " . $sieve->connection->errstr . "<BR>\n";
    $sieve->writeToLog("ERROR: openSieveSession failed for " . $sieve->user .
	': ' . $sieve->connection->errstr, LOG_ERROR);
    exit;
}
if (!$script->retrieveRules($sieve->connection)) {
    array_push($errors, 'ERROR: ' . $script->errstr);
    $sieve->writeToLog("ERROR: retrieveRules failed for " . $sieve->user .
	": " . $script->errstr, LOG_ERROR);
}

/* do rule status change if requested. */

if (isset($GLOBALS['HTTP_POST_VARS']['action'])) {

    $action = AppSession::getFormValue('action');

    if ($action == 'enable') {
        $rules = AppSession::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'ENABLED';
        }
    }

    if ($action == 'disable') {
        $rules = AppSession::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'DISABLED';
        }
    }

    if ($action == 'delete') {
        $rules = AppSession::getFormValue('ruleID');
        foreach ($rules as $ruleID) {
            $script->rules[$ruleID]['status'] = 'DELETED';
        }
    }

    /* increase rule priority. */
    if ($action == 'increase') {
        $rindex = AppSession::getFormValue('rindex');
        /* if this rule and one before it exists, switch them. */
        if ($script->rules[$rindex] &&
		$script->rules[$rindex-1]) {
	    $tmp = $script->rules[$rindex-1];
	    $script->rules[$rindex-1] = $script->rules[$rindex];
	    $script->rules[$rindex] = $tmp;
        }
    }
    /* decrease rule priority. */
    if ($action == 'decrease') {
        $rindex = AppSession::getFormValue('rindex');
        /* if this rule and one after it exists, switch them. */
        if ($script->rules[$rindex] &&
            $script->rules[$rindex+1]) {
	    $tmp = $script->rules[$rindex+1];
	    $script->rules[$rindex+1] = $script->rules[$rindex];
	    $script->rules[$rindex] = $tmp;
        }
    }
    /* write these changes. */
    if (!$script->updateScript($sieve->connection)) {
	array_push($errors, 'ERROR: ' . $script->errstr);
	$sieve->writeToLog('ERROR: updateScript failed for ' . $sieve->user
	    . ': ' . $script->errstr, LOG_ERROR);
    }
    /* get the rules from the saved script again. */
    else {
	if (!$script->retrieveRules($sieve->connection)) {
	    array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog('ERROR: retrieveRules failed for ' . $sieve->user
	    	. ': ' . $script->errstr, LOG_ERROR);
	}
    }
}



?>

<HTML>
<HEAD><TITLE><?php print $default->page_title; ?></TITLE>
<LINK HREF="<?php print $default->config_dir; ?>/smartsieve.css" REL="stylesheet" TYPE="text/css">
<?php

require "$default->include_dir/main.js";

?>

</HEAD>

<BODY>


<FORM ACTION="<?php print AppSession::setUrl('main.php');?>" METHOD="post" NAME="rules">

<TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="menuouter">
    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
      <TR>
	<TD CLASS="menu">
	  &nbsp;
	  <a href="<?php print AppSession::setUrl('login.php?reason=logout');?>">Logout</a> |
	  <a href="<?php print AppSession::setUrl('main.php');?>">View All Rules</a> |
	  <a href="<?php print AppSession::setUrl('vacation.php');?>">Vacation Settings</a> |
	  <a href="<?php print AppSession::setUrl('rule.php');?>">New Filter Rule</a> <?php if ($default->main_help_url){ ?>| 
	  <a href="<?php print $default->main_help_url; ?>">Help</a> <?php } /* endif. */ ?>

	</TD>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

<BR>
<?php if ($errors || $msgs) {  ?>

<TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
<?php foreach ($errors as $err){ ?>
  <TR>
    <TD CLASS="errors">
      <?php print "$err\n"; ?>
    </TD>
  </TR>
<?php } ?>
<?php foreach ($msgs as $msg){ ?>
  <TR>
    <TD CLASS="messages">
      <?php echo "$msg\n"; ?>
    </TD>
  </TR>
<?php } ?>
</TABLE>

<BR>
<?php } //end if $errors ?>

<TABLE WIDTH="100%" CELLPADDING="1" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="statusouter">
    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
      <TR>
	<TD CLASS="status">&nbsp;User: <?php print $sieve->user; ?></TD>
        <TD CLASS="status">&nbsp;Server: <?php print $sieve->server; ?></TD>
        <TD CLASS="status">&nbsp;Script: <?php print $sieve->scriptfile; ?></TD>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

<BR>

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="heading">Mail Filter Rules:</TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
<?php

if ($script->rules){ ?>
      <TR>
        <TH CLASS="heading">&nbsp;</TH>
        <TH CLASS="heading">Status</TH>
        <TH CLASS="heading">Rule</TH>
        <TH CLASS="heading">Order</TH>
      </TR>
<?php

    $i = 0;
    foreach ($script->rules as $rule){
	$complete = buildRule($rule);
?>
    <TR onmouseover="javascript:style.background='grey'" onmouseout="javascript:style.background='#e5e5e5'">
      <TD CLASS="rules"><INPUT TYPE="checkbox" NAME="ruleID[]" VALUE="<?php print $i; ?>"></TD>
      <TD CLASS="<?php if ($rule['status'] == 'ENABLED') print "enabled"; else print "disabled"; print "\">" . $rule['status']; ?></TD>
      <TD CLASS="rules" NOWRAP="nowrap"><A CLASS="rule" HREF="<?php print AppSession::setUrl("rule.php?ruleID=$i"); ?>" onmouseover="status='Edit This Rule'; return true;" onmouseout="status='';"><?php print $complete; ?></A></TD>
      <TD CLASS="rules" NOWRAP="nowrap"><A HREF="" onclick="ChangeOrder('increase',<?php print $i; ?>); return false;"><IMG SRC="<?php print $default->image_dir; ?>/up.gif" ALT="Move rule up" BORDER="0" onmouseover="status='Move rule up'; return true;" onmouseout="status='';"></A> <A HREF="" onclick="ChangeOrder('decrease',<?php print $i; ?>); return false;"><IMG SRC="<?php print $default->image_dir; ?>/down.gif" ALT="Move rule down" BORDER="0" onmouseover="status='Move rule down'; return true;" onmouseout="status='';"></A></TD>
    </TR>
<?php
	$i++;
    }
}
else { ?>
    <TR>
      <TD CLASS="rules" COLSPAN="4">[No rules found]</TD>
    </TR>
<?php
}

if ($script->vacation){
?>
    <TR>
      <TD CLASS="heading" COLSPAN="4">Vacation Message Settings:</TD>
    </TR>
    <TR onmouseover="javascript:style.background='grey'" onmouseout="javascript:style.background='#e5e5e5'">
      <TD CLASS="rules">&nbsp;</TD>
      <TD CLASS="<?php if ($script->vacation['status'] == 'on'){print "enabled\">ENABLED";} else print "disabled\">DISABLED"; ?></TD>
      <TD CLASS="rules" NOWRAP="nowrap" COLSPAN="2"><A CLASS="rule" HREF="<?php print AppSession::setUrl('vacation.php'); ?>">days: <?php print $script->vacation['days']; ?> addresses: <?php

        $first = 1;
        foreach ($script->vacation['addresses'] as $address) {
            if (!$first) print ", ";
            print "\"$address\"";
            $first = 0;
        }
        print " text: " . $script->vacation['text'];
        print "</A></TD></TR>\n";
}

?>

    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">
 
    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
    <BR>
      <TD CLASS="options">
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="status='Enable'; return true;" onmouseout="status='';">Enable</a>
         | 
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="status='Disable'; return true;" onmouseout="status='';">Disable</a>
         | 
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="status='Delete'; return true;" onmouseout="status='';">Delete</a>
      </TD>
    </BR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="rindex" VALUE="" >

</FORM>

<?php

$sieve->closeSieveSession();


function buildRule($rule) {
    $andor = " AND ";
    if ($rule['anyof']) $andor = " OR ";
    $match = "contains";
    if ($rule['regexp']) $match = "matches regexp";
    $complete = "IF ";
    if ($rule['unconditional']) $complete = "[Unconditional] ";

    if ($rule['from']) {
	$complete .= "'From:' " . $match . " '" . $rule['from'] . "'";
	$started = 1;
    }
    if ($rule['to']) {
	if ($started) $complete .= $andor;
	$complete .= "'To:' " . $match . " '" . $rule['to'] . "'";
	$started = 1;
    }
    if ($rule['subject']) {
	if ($started) $complete .= $andor;
	$complete .= "'Subject:' " . $match . " '" . $rule['subject'] . "'";
	$started = 1;
    }
    if ($rule['field'] && $rule['field_val']) {
	if ($started) $complete .= $andor;
	$complete .= "'" . $rule['field'] . "' " . $match . " '" . $rule['field_val'] . "'";
	$started = 1;
    }
    if ($rule['size']) {
	$xthan = " less than '";
	if ($rule['gthan']) $xthan = " greater than '";
	if ($started) $complete .= $andor;
	$complete .= "message " . $xthan . $rule['size'] . "KB'";
	$started = 1;
    }
    if (!$rule['unconditional']) $complete .= " THEN ";
    if (preg_match("/folder/i",$rule['action']))
	$complete .= "file into '" . $rule['action_arg'] . "';";
    if (preg_match("/reject/i",$rule['action']))
	$complete .= "reject '" . $rule['action_arg'] . "';";
    if (preg_match("/address/i",$rule['action']))
        $complete .= "forward to '" . $rule['action_arg'] . "';";
    if (preg_match("/discard/i",$rule['action']))
        $complete .= "discard;";
    if ($rule['continue']) $complete .= " [Continue]";
    if ($rule['keep']) $complete .= " [Keep a copy]";
    return $complete;
}

?>

</BODY>
</HTML>

