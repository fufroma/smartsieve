<FORM ACTION="<?php print AppSession::setUrl('forward.php');?>" METHOD="post" NAME="thisForward">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
    <?php if ($rule) {
         echo SmartSieve::text("Edit Forward Rule") . "</TD><TD CLASS=";
         if ($rule['status'] == 'ENABLED'){
            print "\"enabled\"> " . SmartSieve::text('ENABLED');
         }
         else print "\"disabled\"> " . SmartSieve::text('DISABLED');
       }
       else echo SmartSieve::text('Create Forward Rule') . "</TD><TD>&nbsp;";
    ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR>
      <TD>
        <?php echo SmartSieve::text('Forwarding Address:');?> 
      </TD>
      <TD>
        <INPUT TYPE="text" NAME="address" <?php if ($rule['action'] == 'address') print "VALUE=\"" . $rule['action_arg'] . "\" "; ?>SIZE="40">
      </TD>
    </TR>
    <TR>
      <TD>
        <?php echo SmartSieve::text('Keep a copy in Inbox:');?> 
      </TD>
      <TD>
        <INPUT TYPE="checkbox" NAME="keep" VALUE="keep" <?php if ($rule['keep']) print "CHECKED"; ?>>
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
        <A CLASS="option" HREF="" onclick="Submit('save'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Save Changes');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Save Changes');?></a>
<?php //if ($script->rules[$ruleID]) { ?>
<?php if (isset($ruleID)){ ?>
          |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Enable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Enable');?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Disable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Disable');?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Delete');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Delete');?></a>
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
<INPUT TYPE="hidden" NAME="anyof" VALUE="">
<INPUT TYPE="hidden" NAME="from" VALUE="">
<INPUT TYPE="hidden" NAME="to" VALUE="">
<INPUT TYPE="hidden" NAME="subject" VALUE="">
<INPUT TYPE="hidden" NAME="gthan" VALUE="">
<INPUT TYPE="hidden" NAME="size" VALUE="">
<INPUT TYPE="hidden" NAME="field" VALUE="">
<INPUT TYPE="hidden" NAME="field_val" VALUE="">
<INPUT TYPE="hidden" NAME="action" VALUE="address">
<INPUT TYPE="hidden" NAME="folder" VALUE="">
<INPUT TYPE="hidden" NAME="reject" VALUE="">
<INPUT TYPE="hidden" NAME="discard" VALUE="">
<INPUT TYPE="hidden" NAME="custom" VALUE="">

</FORM>

</BODY>
</HTML>

