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

session_name($default->session_name);
@session_start();
$errors = array();
$msgs = array();

$sieve = &$GLOBALS['HTTP_SESSION_VARS']['sieve'];
$scripts = &$GLOBALS['HTTP_SESSION_VARS']['scripts'];

// if a session does not exist, go to login page
if (!is_object($sieve) || !$sieve->authenticate()) {
	header('Location: ' . AppSession::setUrl('login.php'),true);
	exit;
}

// should have a valid session at this point

// change working script if requested.
if (isset($GLOBALS['HTTP_POST_VARS']['script'])) {
    $sieve->workingscript = AppSession::getFormValue('script');
    header('Location:' . AppSession::setUrl('main.php'),true);
    exit;
}

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
    print "ERROR: " . $sieve->errstr . "<BR>\n";
    $sieve->writeToLog('ERROR: openSieveSession failed for ' . $sieve->user . 
        ': ' . $sieve->errstr, LOG_ERROR);
    exit;
}


/* save script changes if requested. */

$action = AppSession::getFormValue('action');

if ($action == 'activate')
{
    $s = AppSession::getFormValue('scriptID');
    $sieve->connection->activatescript($s[0]);
    if ($sieve->connection->errstr)
        array_push($errors,$sieve->connection->errstr);
}

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

?>

<HTML>
<HEAD><TITLE><?php print $default->page_title; ?></TITLE>
<LINK HREF="<?php print AppSession::setUrl('css.php'); ?>" REL="stylesheet" TYPE="text/css">
<?php

require "$default->include_dir/scripts.js";

?>

</HEAD>


<BODY>

<FORM ACTION="<?php print AppSession::setUrl('scripts.php');?>" METHOD="post" NAME="changescript">

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
          <a href="<?php print AppSession::setUrl('rule.php');?>">New Filter Rule</a> <?php if ($default->rule_help_url){ ?>|
          <a href="<?php print $default->rule_help_url; ?>">Help</a> <?php } /* endif. */ ?>

        </TD>
<?php if ($default->allow_multi_scripts) { ?>
        <TD CLASS="menu" ALIGN="right">
          &nbsp;
          <SELECT NAME="script" onchange="document.changescript.submit();">
<?php     foreach ($sieve->scriptlist as $s){
              $str = "\t\t<OPTION VALUE=\"$s\"";
              if ($s == $sieve->workingscript)
                  $str .= " SELECTED=\"selected\"";
              $str .= ">$s</OPTION>\n";
              print $str;
          } ?>
          </SELECT>
        </TD>
<?php } //end if ?>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

</FORM>
 
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
        <TD CLASS="status">
          &nbsp;User: <?php print $sieve->user; ?>
        </TD>
        <TD CLASS="status">
          &nbsp;Server: <?php print $sieve->server; ?>
        </TD>
        <TD CLASS="status">
          &nbsp;Script: <?php print $sieve->workingscript; ?>
        </TD>
<?php if (AppSession::isActiveScript($sieve->workingscript)) { ?>
        <TD CLASS="active">
          ACTIVE
        </TD>
<?php } else { ?>
        <TD CLASS="inactive">
          NOT ACTIVE
        </TD>
<?php } ?>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

<BR>

<FORM ACTION="<?php print AppSession::setUrl('scripts.php');?>" METHOD="post" NAME="scripts">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="heading">Sieve Scripts:</TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
<?php

if ($sieve->scriptlist){ ?>
      <TR>
        <TH CLASS="heading">&nbsp;</TH>
        <TH CLASS="heading">Script</TH>
        <TH CLASS="heading">Active</TH>
        <TH CLASS="heading">No of Rules</TH>
      </TR>
<?php

    $i = 0;
    foreach ($sieve->scriptlist as $script){
?>
    <TR onmouseover="javascript:style.background='grey'" onmouseout="javascript:style.background='#e5e5e5'">
      <TD CLASS="rules"><INPUT TYPE="checkbox" NAME="scriptID[]" VALUE="<?php print $i; ?>"></TD>
      <TD CLASS="rules"><A CLASS="rule" HREF="" onclick="Submit('<?php echo $script; ?>'); return false;" onmouseover="status='Edit This Script'; return true;" onmouseout="status='';"><?php echo $script; ?></A></TD>
      <TD CLASS="<?php if (AppSession::isActiveScript($script)) echo "enabled"; else echo "disabled"; ?>">Active</TD>
      <TD CLASS="rules">&nbsp;</TD>
    </TR>
<?php
        $i++;
    }
}
else { ?>
    <TR>
      <TD CLASS="rules" COLSPAN="4">[No existing scripts]</TD>
    </TR>
<?php
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
        <A CLASS="option" HREF="" onclick="Activate(); return false;" onmouseover="status='Activate Script'; return true;" onmouseout="status='';">Activate</a>
         |
        <A CLASS="option" HREF="" onclick="Submit('deactivate'); return false;" onmouseover="status='Deactivate All'; return true;" onmouseout="status='';">Deactivate</a>
         |
        <A CLASS="option" HREF="" onclick="Submit('create'); return false;" onmouseover="status='Create New Script'; return true;" onmouseout="status='';">Create</a>
         |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="status='Delete Script'; return true;" onmouseout="status='';">Delete</a>
      </TD>
    </BR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >

</FORM>

<?php

$sieve->closeSieveSession();

?>

</BODY>
</HTML>

