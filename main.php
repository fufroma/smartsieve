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
require "$default->config_dir/style.php";

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

// start sieve session, and get the rules via the script object
if (!$sieve->openSieveSession()) {
    print "ERROR: " . $sieve->errstr . "<BR>\n";
    $sieve->writeToLog("ERROR: openSieveSession failed for " . $sieve->user .
	': ' . $sieve->errstr, LOG_ERR);
    exit;
}

// if user has just logged in select which script to open.
if (!$sieve->workingscript){
    if (!$sieve->initialWorkingScript()){
        $sieve->writeToLog('ERROR: ' . $sieve->errstr);
        array_push($errors, 'ERROR: ' . $sieve->errstr);
    }
}

// change working script if requested.
if (isset($GLOBALS['HTTP_POST_VARS']['script'])) {
    $sieve->workingscript = AppSession::getFormValue('script');
}

// create script object if doesn't already exist.
if (!isset($scripts[$sieve->workingscript]) || 
    !is_object($scripts[$sieve->workingscript])){
    $scripts[$sieve->workingscript] = new Script($sieve->workingscript);
    if (!is_object($scripts[$sieve->workingscript])){
        writeToLog('main.php: failed to create script object ' . $sieve->workingscript);
        array_push($errors, 'failed to create script object ' . $sieve->workingscript);
    }
}

$script = &$scripts[$sieve->workingscript];

if (!$script->retrieveRules($sieve->connection)) {
    array_push($errors, 'ERROR: ' . $script->errstr);
    $sieve->writeToLog("ERROR: retrieveRules failed for " . $sieve->user .
	": " . $script->errstr, LOG_ERR);
}

// warn if script encoding was not recognised.
if ($script->so == false){
    array_push($msgs, 'WARNING: this script does not appear to be in a format SmartSieve can read.<BR>Modifying this script may result in existing rules being lost.');
}
if ($script->mode == 'advanced'){
    array_push($msgs, 'WARNING: this script appears to be in advanced mode.<BR>Modifying this script may result in existing rules being lost.');
}

/* do rule status change if requested. */

$action = AppSession::getFormValue('action');

if ($action) {

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
	    . ': ' . $script->errstr, LOG_ERR);
    }
    /* get the rules from the saved script again. */
    else {
	if (!$script->retrieveRules($sieve->connection)) {
	    array_push($errors, 'ERROR: ' . $script->errstr);
	    $sieve->writeToLog('ERROR: retrieveRules failed for ' . $sieve->user
	    	. ': ' . $script->errstr, LOG_ERR);
	}
    }
}



?>

<HTML>
<HEAD><TITLE><?php print $default->page_title; ?></TITLE>
<LINK HREF="<?php print AppSession::setUrl('css.php'); ?>" REL="stylesheet" TYPE="text/css">
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
	  <a href="<?php print AppSession::setUrl('rule.php');?>">New Filter Rule</a>
<?php if ($default->allow_multi_scripts) { ?>|
          <A HREF="<?php print AppSession::setUrl('scripts.php');?>">Manage Scripts</A>
<?php } ?>
<?php if ($default->main_help_url){ ?>| 
	  <a href="<?php print $default->main_help_url; ?>">Help</a> 
<?php } ?>

	</TD>
<?php if ($default->allow_multi_scripts) { ?>
        <TD CLASS="menu" ALIGN="right">
          &nbsp;
          <SELECT NAME="script" onchange="document.rules.submit();">
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
      <TR CLASS="status">
	<TD>
          &nbsp;User: <?php print $sieve->user; ?> 
        </TD>
        <TD>
          &nbsp;Server: <?php print $sieve->server; ?> 
        </TD>
        <TD>
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
        <TH WIDTH="5%">&nbsp;</TH>
        <TH WIDTH="10%">Status</TH>
        <TH WIDTH="80%">Rule</TH>
        <TH WIDTH="5%">Order</TH>
      </TR>
<?php

    $i = 0;
    foreach ($script->rules as $rule){
	$complete = buildRule($rule);
        $class = 'disabledrule';
        $eclass = 'disabled';
        $onmouseover = $css['.disabledrule-over']['background-color'];
        $onmouseout = $css['.disabledrule']['background-color'];
        if ($rule['status'] == 'ENABLED'){
            $class = 'enabledrule';
            $eclass = 'enabled';
            $onmouseover = $css['.enabledrule-over']['background-color'];
            $onmouseout = $css['.enabledrule']['background-color'];
        }
?>
    <TR CLASS="<?php echo $class; ?>" onmouseover="javascript:style.backgroundColor='<?php echo $onmouseover;?>'" onmouseout="javascript:style.backgroundColor='<?php echo $onmouseout;?>'">
      <TD>
        <INPUT TYPE="checkbox" NAME="ruleID[]" VALUE="<?php print $i; ?>">
      </TD>
      <TD CLASS="<?php echo $eclass; ?>">
        <?php echo $rule['status']; ?> 
      </TD>
      <TD>
        <A CLASS="rule" HREF="<?php print AppSession::setUrl("rule.php?ruleID=$i"); ?>" onmouseover="window.status='Edit This Rule'; return true;" onmouseout="window.status='';"><?php print $complete; ?></A>
      </TD>
      <TD NOWRAP="nowrap">
        <A HREF="" onclick="ChangeOrder('increase',<?php print $i; ?>); return false;"><IMG SRC="<?php print $default->image_dir; ?>/up.gif" ALT="Move rule up" BORDER="0" onmouseover="window.status='Move rule up'; return true;" onmouseout="window.status='';"></A>
        <A HREF="" onclick="ChangeOrder('decrease',<?php print $i; ?>); return false;"><IMG SRC="<?php print $default->image_dir; ?>/down.gif" ALT="Move rule down" BORDER="0" onmouseover="window.status='Move rule down'; return true;" onmouseout="window.status='';"></A>
      </TD>
    </TR>
<?php
	$i++;
    }
}
else { ?>
    <TR CLASS="enabledrule">
      <TD COLSPAN="4">[No rules found]</TD>
    </TR>
<?php
}

if ($script->vacation){
    $class = 'disabledrule';
    $eclass = 'disabled';
    $onmouseover = $css['.disabledrule-over']['background-color'];
    $onmouseout = $css['.disabledrule']['background-color'];
    $status = 'DISABLED';
    if ($script->vacation['status'] == 'on'){
        $class = 'enabledrule';
        $eclass = 'enabled';
        $onmouseover = $css['.enabledrule-over']['background-color'];
        $onmouseout = $css['.enabledrule']['background-color'];
        $status = 'ENABLED';
    }
?>
    <TR>
      <TD CLASS="heading" COLSPAN="4">Vacation Message Settings:</TD>
    </TR>
    <TR CLASS="<?php echo $class; ?>" onmouseover="javascript:style.backgroundColor='<?php echo $onmouseover;?>'" onmouseout="javascript:style.backgroundColor='<?php echo $onmouseout; ?>'">
      <TD>
        &nbsp;
      </TD>
      <TD CLASS="<?php echo $eclass; ?>">
        <?php echo $status; ?> 
      </TD>
      <TD COLSPAN="2">
        <A CLASS="rule" HREF="<?php print AppSession::setUrl('vacation.php'); ?>">days: <?php print $script->vacation['days']; ?> addresses: <?php

        $first = 1;
        foreach ($script->vacation['addresses'] as $address) {
            if (!$first) print ", ";
            print "\"$address\"";
            $first = 0;
        }
        print " text: " . $script->vacation['text']; ?></A>
      </TD>
    </TR>
<?php
} // end if $vacation.
?>

    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">
 
    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
    <BR>
      <TD CLASS="options">
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='Enable'; return true;" onmouseout="window.status='';">Enable</a>
         | 
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='Disable'; return true;" onmouseout="window.status='';">Disable</a>
         | 
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='Delete'; return true;" onmouseout="window.status='';">Delete</a>
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
    $started = 0;
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

