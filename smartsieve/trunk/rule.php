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

// get the list of mailboxes for this user.
// we will need it below for file into: select box.
if (!$sieve->mboxlist){
  if (!$sieve->retrieveMailboxList()){
    array_push($errors, 'ERROR: ' . $sieve->errstr);
    $sieve->writeToLog("ERROR: " . $sieve->errstr, LOG_ERROR);
  }
}

// open sieve connection
if (!$sieve->openSieveSession()) {
    print "ERROR: " . $sieve->connection->errstr . "<BR>\n";
    $sieve->writeToLog('ERROR: openSieveSession failed for ' . $sieve->user . 
        ': ' . $sieve->connection->errstr, LOG_ERROR);
    exit;
}


$ruleID = null;   /* rule number. */
$rule = null;     /* sieve rule $script->rules[$ruleID]. */

/* if one of the save, enable etc options was selected from rule.php, 
 * then get the rule values from POST data. if rule selected from main.php 
 * $ruleID will be set in GET data. if $ruleID not set in POST or GET, or 
 * if $script->rules[$ruleID] does not exist, this will be a new rule page.
 */
if (isset($GLOBALS['HTTP_POST_VARS']['ruleID'])) {
    $ruleID = AppSession::getFormValue('ruleID');
    $rule = getRulePOSTValues($ruleID);
}
elseif (isset($GLOBALS['HTTP_GET_VARS']['ruleID'])) {
    $ruleID = AppSession::getFormValue('ruleID');
    if (isset($script->rules[$ruleID]))
        $rule = $script->rules[$ruleID];
}

/* save rule changes if requested. */

$action = AppSession::getFormValue('thisAction');

if ($action == 'enable') 
{
    if ($script->rules[$ruleID]){
        $script->rules[$ruleID]['status'] = 'ENABLED';
	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERROR);
	}
	else {
	    array_push($msgs, 'rule successfully enabled.');
            if ($default->return_after_update){
                header('Location: ' . AppSession::setUrl('main.php'),true);
                exit;
            }
            $rule['status'] = 'ENABLED';
	}
    }
    else {
        array_push($errors, 'ERROR: rule does not exist.');
        $sieve->writeToLog('ERROR: rule does not exist.', LOG_ERROR);
    }
}
if ($action == 'disable') 
{
    if ($script->rules[$ruleID]){
        $script->rules[$ruleID]['status'] = 'DISABLED';
	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERROR);
	}
	else {
	    array_push($msgs, 'rule successfully disabled.');
            if ($default->return_after_update){
                header('Location: ' . AppSession::setUrl('main.php'),true);
                exit;
            }
            $rule['status'] = 'DISABLED';
	}
    }
    else {
        array_push($errors, 'ERROR: rule does not exist.');
        $sieve->writeToLog('ERROR: rule does not exist.', LOG_ERROR);
    }
}
if ($action == 'delete') 
{
    if ($script->rules[$ruleID]){
        $script->rules[$ruleID]['status'] = 'DELETED';
	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERROR);
	}
	else {
	    header('Location: ' . AppSession::setUrl('main.php'),true);
	    exit;
	}
    }
    array_push($errors, 'ERROR: rule does not exist');
    $sieve->writeToLog('ERROR: rule does not exist.', LOG_ERROR);
}
if ($action == 'save') 
{
    $ret = checkRule(&$rule);
    if ($ret == 'OK'){    /* rule passed sanity checks */

        // if existing rule, update. add new if not.
	if ($script->rules[$ruleID]){
	    $script->rules[$ruleID] = $rule;
	}
	else array_push($script->rules, $rule);

	// write and save the new script.
	if (!$script->updateScript($sieve->connection)) {
	    array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog('ERROR: ' . $script->errstr, LOG_ERROR);
	}
	else {
            array_push($msgs, 'your changes have been successfully saved.');
            if ($default->return_after_update){
	        header('Location: ' . AppSession::setUrl('main.php'),true);
	        exit;
            }
	}

    } # if checkRule()
    else
        array_push($errors, 'ERROR: ' . $ret);
}

?>

<HTML>
<HEAD><TITLE><?php print $default->page_title; ?></TITLE>
<LINK HREF="<?php print $default->config_dir; ?>/smartsieve.css" REL="stylesheet" TYPE="text/css">
<?php

require "$default->include_dir/rule.js";

?>

</HEAD>


<BODY>

<FORM ACTION="<?php print AppSession::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="menuouter">
    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
      <TR>
        <TD CLASS="menu">
          &nbsp;
          <a href="<?php print AppSession::setUrl('login.php?reason=logout');?>">Logout</a> |
          <a href="<?php print AppSession::setUrl('main.php');?>">View All
Rules</a> |
          <a href="<?php print AppSession::setUrl('vacation.php');?>">Vacation Settings</a> |
          <a href="<?php print AppSession::setUrl('rule.php');?>">New Filter Rule</a> <?php if ($default->main_help_url){ ?>|
          <a href="<?php print $default->main_help_url; ?>">Help</a> <?php } /* endif. */ ?>

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

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="ruleinfo">
    <?php if ($rule) {
	 print "Edit Mail Filter Rule</TD><TD CLASS=";
	 if ($rule['status'] == 'ENABLED'){
	    print "\"ruleenabled\"> ENABLED ";
	 }
	 else print "\"ruledisabled\"> DISABLED ";
       } 
       else print "New Mail Filter Rule</TD><TD CLASS=\"ruleinfo\">"; 
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
      <TD>
    <INPUT TYPE="checkbox" NAME="continue" VALUE="continue" <?php if ($rule['continue']) print "CHECKED"; ?> >Check message against next rule also
    <INPUT TYPE="checkbox" NAME="keep" VALUE="keep" <?php if ($rule['keep']) print "CHECKED"; ?> >Keep a copy of the message in your Inbox
 <?php if ($default->allow_regex){ ?>
    <INPUT TYPE="checkbox" NAME="regexp" VALUE="regexp" <?php if ($rule['regexp']) print "CHECKED"; ?> >Use regular expressions
 <?php }  ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="heading">CONDITIONS:
      </TD>
      <TD CLASS="heading">&nbsp;</TD>
    </TR>
    <TR>
      <TD NOWRAP="nowrap">
Match 
        <SELECT NAME="anyof">
	    <OPTION <?php if (!$rule['anyof']) print "SELECTED"; ?> VALUE="0"> all of
	    <OPTION <?php if ($rule['anyof']) print "SELECTED"; ?> VALUE="1"> any of
        </SELECT>
      </TD>
      <TD NOWRAP="nowrap">
    If message 'From:' contains: <INPUT TYPE="text" NAME="from" <?php if ($rule['from']) print "VALUE=\"" . $rule['from'] . "\" "; ?>SIZE="50">
  </TD>
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    If message 'To:' contains: <INPUT TYPE="text" NAME="to" <?php if ($rule['to']) print "VALUE=\"" . $rule['to'] . "\" "; ?>SIZE="50">
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    If message 'Subject:' contains: <INPUT TYPE="text" NAME="subject" <?php if ($rule['subject']) print "VALUE=\"" . $rule['subject'] . "\" "; ?>SIZE="50">
  </TD>
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    If message size is 
	<SELECT NAME="gthan">
	    <OPTION <?php if (!$rule['gthan']) print "SELECTED"; ?> VALUE="0"> less than
	    <OPTION <?php if ($rule['gthan']) print "SELECTED"; ?> VALUE="1"> greater than
	</SELECT>
	<INPUT TYPE="text" NAME="size" <?php if ($rule['size']) print "VALUE=\"" . $rule['size'] . "\" "; ?>SIZE="5"> KiloBytes
  </TD>
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    If mail header: <INPUT TYPE="text" NAME="field" <?php if ($rule['field']) print "VALUE=\"" . $rule['field'] . "\" "; ?>SIZE="20"> contains: <INPUT TYPE="text" NAME="field_val" <?php if ($rule['field_val']) print "VALUE=\"" . $rule['field_val'] . "\" "; ?>SIZE="30">
  </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR>
  <TD CLASS="heading">
    ACTIONS:
  </TD>
  <TD CLASS="heading">
    &nbsp;
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="folder" <?php if ($rule['action'] == 'folder') print "CHECKED"; ?> > File Into: 
  </TD>
  <TD>
    <SELECT NAME="folder">
<?php foreach ($sieve->mboxlist as $mbox){
      $opt =  "\t<OPTION ";
      if ($rule['action_arg'] == $mbox) $opt .= "SELECTED ";
      $opt .= "VALUE=\"$mbox\">$mbox</OPTION>\n";
      print $opt;
   }
?>
    </SELECT>
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="address" <?php if ($rule['action'] == 'address') print "CHECKED"; ?> > Forward to address: 
  </TD>
  <TD>
    <INPUT TYPE="text" NAME="address" <?php if ($rule['action'] == 'address') print "VALUE=\"" . $rule['action_arg'] . "\" "; ?>SIZE="40">
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="reject" <?php if ($rule['action'] == 'reject') print "CHECKED"; ?> > Send a reject message: 
  </TD>
  <TD>
    <TEXTAREA NAME="reject" ROWS="3" COLS="40" WRAP="hard" TABINDEX="14">
<?php if ($rule['action'] == 'reject') print $rule['action_arg']; ?>
</TEXTAREA>
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="discard" <?php if ($rule['action'] == 'discard') print "CHECKED"; ?> > Discard the message.
  </TD>
  <TD>&nbsp;</TD>
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
<?php // if ($rule) { ?>
<?php if ($script->rules[$ruleID]) { ?>
          |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="status='Enable'; return true;" onmouseout="status='';">Enable</a>
          |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="status='Disable'; return true;" onmouseout="status='';">Disable</a>
          |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="status='Delete'; return true;" onmouseout="status='';">Delete</a>
<?php } ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>


<INPUT TYPE="hidden" NAME="priority" VALUE="<?php
    if ($rule) {
	print $rule['priority'];
    }
    else print $script->pcount+1;
?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php
    if ($rule) {
	print $rule['status'];
    }
    else print "ENABLED";
?>">
<INPUT TYPE="hidden" NAME="flg" VALUE="<?php
    if ($rule) {
	print $rule['flg'];
    }
?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php
   if ($rule && isset($ruleID)) {
	print $ruleID;
   }
   else print "new";
?>">

</FORM>

<?php

$sieve->closeSieveSession();


/* if rule values supplied from form on rule.php, get rule values 
 * from POST data.
 */
function getRulePOSTValues ($ruleID)
{
    $rule = array();
    $rule['priority'] = AppSession::getFormValue('priority');
    $rule['status'] = AppSession::getFormValue('status');
    $rule['from'] = AppSession::getFormValue('from');
    $rule['to'] = AppSession::getFormValue('to');
    $rule['subject'] = AppSession::getFormValue('subject');
    $rule['action'] = AppSession::getFormValue('action');
    $rule['action_arg'] = AppSession::getFormValue($rule['action']);
    $rule['field'] = AppSession::getFormValue('field');
    $rule['field_val'] = AppSession::getFormValue('field_val');
    $rule['size'] = AppSession::getFormValue('size');
    $rule['continue'] = 0;
    if (AppSession::getFormValue('continue')) $rule['continue'] = 1;
    $rule['gthan'] = 0;
    if (AppSession::getFormValue('gthan')) $rule['gthan'] = 2;
    $rule['anyof'] = 0;
    if (AppSession::getFormValue('anyof')) $rule['anyof'] = 4;
    $rule['keep'] = 0;
    if (AppSession::getFormValue('keep')) $rule['keep'] = 8;
    $rule['regexp'] = 0;
    if (AppSession::getFormValue('regexp')) $rule['regexp'] = 128;
    if (!$rule['from'] && !$rule['to'] && !$rule['subject'] &&
       !$rule['field'] && !$rule['size'] && $rule['action'])
       $rule['unconditional'] = 1;
    $rule['flg'] = $rule['continue'] | $rule['gthan'] | $rule['anyof'] | $rule['keep'] | $rule['regexp'];

    return $rule;
}


/* basic sanity checks on rule.
 * any value returned will be an error msg.
 */
function checkRule($rule) {
    global $default;

    /* check values do not exceed acceptible sizes. */
    $conds = array('from','to','subject','field','field_val');
    foreach ($conds as $cond) {
        if (strlen($rule[$cond]) > $default->max_field_chars)
	    return 'the condition value you supplied is too long. it should not exceed ' . 
		$default->max_field_chars . ' characters.';
    }
    if ($rule['action'] == 'address' &&
        strlen($rule['action_arg']) > $default->max_field_chars)
	    return '"the forward address you supplied is too long. it should not exceed ' . 
		$default->max_field_chars . ' characters.';
    if ($rule['action'] == 'reject' &&
        strlen($rule['action_arg']) > $default->max_textbox_chars)
	    return 'your reject message is too long. it should not exceed ' . 
		$default->max_textbox_chars . ' characters.';

    if ($rule['field'] && !$rule['field_val'])
        return "you must supply a value for the field " . $rule['field'];
    if (!$rule['action'])
        return "please supply an action";
    if ($rule['action'] != 'discard' && !$rule['action_arg'])
        return "you must supply an argument for this action";
    /* if this is a forward rule, forward address must be a valid email. */
    if ($rule['action'] == 'address' && !preg_match("/\@/",$rule['action_arg']))
        return "'" . $rule['action_arg'] . "' is not a valid email address";
    /* complain if msg size contains non-digits. */
    if (preg_match("/\D/",$rule['size']))
        return "message size value must be a positive integer";

    /* apply alternative namespacing to mailbox if necessary. */
    if ($rule['action'] == 'folder')
        $rule['action_arg'] = SmartSieve::getMailboxName($rule['action_arg']);

    return 'OK';
}


?>

</BODY>
</HTML>

