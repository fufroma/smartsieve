<FORM ACTION="<?php print AppSession::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
    <?php if ($rule) {
         echo SmartSieve::text("Edit Mail Filter Rule")."</TD><TD CLASS=";
         if ($rule['status'] == 'ENABLED'){
            print "\"enabled\"> ".SmartSieve::text("ENABLED")." ";
         }
         else print "\"disabled\"> ".SmartSieve::text("DISABLED")." ";
       }
       else print SmartSieve::text("New Mail Filter Rule")."</TD><TD>&nbsp;";
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
    <INPUT TYPE="checkbox" NAME="continue" VALUE="continue" <?php if ($rule['continue']) print "CHECKED"; ?> ><?php echo SmartSieve::text('Check message against next rule also');?>
    <INPUT TYPE="checkbox" NAME="keep" VALUE="keep" <?php if ($rule['keep']) print "CHECKED"; ?> ><?php echo SmartSieve::text('Keep a copy of the message in your Inbox');?>
 <?php if ($default->allow_regex){ ?>
    <INPUT TYPE="checkbox" NAME="regexp" VALUE="regexp" <?php if ($rule['regexp']) print "CHECKED"; ?> ><?php echo SmartSieve::text('Use regular expressions');?>
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
      <TD CLASS="heading"><?php echo SmartSieve::text('CONDITIONS');?>:
      </TD>
      <TD CLASS="heading">&nbsp;</TD>
    </TR>
    <TR>
      <TD NOWRAP="nowrap">
        <?php echo SmartSieve::text('Match');?> 
        <SELECT NAME="anyof">
            <OPTION <?php if (!$rule['anyof']) print "SELECTED"; ?> VALUE="0"><?php echo SmartSieve::text('all of');?></OPTION>
            <OPTION <?php if ($rule['anyof']) print "SELECTED"; ?> VALUE="1"><?php echo SmartSieve::text('any of');?></OPTION>
        </SELECT>
      </TD>
      <TD NOWRAP="nowrap">
    <?php echo SmartSieve::text("If message 'From:' contains");?>: <INPUT TYPE="text" NAME="from" <?php if ($rule['from']) print "VALUE=\"" . $rule['from'] . "\" "; ?>SIZE="50">
  </TD>
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    <?php echo SmartSieve::text("If message 'To:' contains");?>: <INPUT TYPE="text" NAME="to" <?php if ($rule['to']) print "VALUE=\"" . $rule['to'] . "\" "; ?>SIZE="50">
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    <?php echo SmartSieve::text("If message 'Subject:' contains");?>: <INPUT TYPE="text" NAME="subject" <?php if ($rule['subject']) print "VALUE=\"" . $rule['subject'] . "\" "; ?>SIZE="50">
  </TD>
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    <?php echo SmartSieve::text("If message size is");?> 
        <SELECT NAME="gthan">
            <OPTION <?php if (!$rule['gthan']) print "SELECTED"; ?> VALUE="0"><?php echo SmartSieve::text("less than");?></OPTION>
            <OPTION <?php if ($rule['gthan']) print "SELECTED"; ?> VALUE="1"><?php echo SmartSieve::text("greater than");?></OPTION>
        </SELECT>
        <INPUT TYPE="text" NAME="size" <?php if ($rule['size']) print "VALUE=\"" . $rule['size'] . "\" "; ?>SIZE="5"> <?php echo SmartSieve::text("KiloBytes");?> 
  </TD>
</TR>
<TR>
  <TD>
  </TD>
  <TD>
    <?php echo SmartSieve::text("If mail header");?>: <INPUT TYPE="text" NAME="field" <?php if ($rule['field']) print "VALUE=\"" . $rule['field'] . "\" "; ?>SIZE="20"> <?php echo SmartSieve::text("contains");?>: <INPUT TYPE="text" NAME="field_val" <?php if ($rule['field_val']) print "VALUE=\"" . $rule['field_val'] . "\" "; ?>SIZE="30">
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
    <?php echo SmartSieve::text("ACTIONS");?>:
  </TD>
  <TD CLASS="heading">
    &nbsp;
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="folder" <?php if ($rule['action'] == 'folder') print "CHECKED"; ?> > <?php echo SmartSieve::text("File Into");?>:
  </TD>
  <TD>
    <SELECT NAME="folder">
<?php foreach ($_SESSION['smartsieve']['mailboxes'] as $mbox): ?>
      <OPTION VALUE="<?php echo $mbox;?>"<?php echo (($rule['action_arg'] == $mbox) ? ' SELECTED="selected"' : '');?>><?php echo $mbox;?></OPTION>
<?php endforeach; ?>
    </SELECT>
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="address" <?php if ($rule['action'] == 'address') print "CHECKED"; ?> > <?php echo SmartSieve::text("Forward to address");?>:
  </TD>
  <TD>
    <INPUT TYPE="text" NAME="address" <?php if ($rule['action'] == 'address') print "VALUE=\"" . $rule['action_arg'] . "\" "; ?>SIZE="40">
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="reject" <?php if ($rule['action'] == 'reject') print "CHECKED"; ?> > <?php echo SmartSieve::text("Send a reject message");?>:
  </TD>
  <TD>
    <TEXTAREA NAME="reject" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard" TABINDEX="14">
<?php if ($rule['action'] == 'reject') print $rule['action_arg']; ?>
</TEXTAREA>
  </TD>
</TR>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="discard" <?php if ($rule['action'] == 'discard') print "CHECKED"; ?> > <?php echo SmartSieve::text("Discard the message");?>.
  </TD>
  <TD>&nbsp;</TD>
</TR>
<?php if (!empty($default->allow_custom) || $rule['action'] == 'custom'){ ?>
<TR>
  <TD>
    <INPUT TYPE="radio" NAME="action" VALUE="custom" <?php if ($rule['action'] == 'custom') print "CHECKED"; ?> > <?php echo SmartSieve::text('Custom Rule');?>:
  </TD>
  <TD>
    <TEXTAREA NAME="custom" ROWS="3" COLS="40" WRAP="off" TABINDEX="15">
<?php if ($rule['action'] == 'custom') print $rule['action_arg']; ?>
</TEXTAREA>
  </TD>
</TR>
<?php } // end if $default->allow_custom. ?>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="options" COLSPAN="2">
        <BR>
        <A CLASS="option" HREF="" onclick="Submit('save'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Save Changes");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Save Changes");?></a>
<?php //if ($script->rules[$ruleID]) { ?>
<?php if (isset($ruleID)){ ?>
          |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Enable");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Enable");?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Disable");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Disable");?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Delete");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Delete");?></a>
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

</BODY>
</HTML>

