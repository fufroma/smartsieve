<FORM ACTION="<?php echo SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
        <?php echo SmartSieve::text('Edit mail forwarding');?> 
      </TD>
      <TD CLASS="<?php echo ($display['status'] == 'ENABLED') ? 'enabled' : 'disabled';?>">
        <?php echo ($display['status'] == 'ENABLED') ? SmartSieve::text('ENABLED') : SmartSieve::text('DISABLED');?>
<?php else: ?>
        <?php echo SmartSieve::text('Set mail forwarding');?> 
      </TD>
      <TD>
        &nbsp;
<?php endif; ?>
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
        <?php echo SmartSieve::text('Forward mail to address:');?> 
      </TD>
      <TD>
        <INPUT TYPE="text" NAME="address" VALUE="<?php echo  $display['action']['address'];?>" SIZE="40">
      </TD>
    </TR>
    <TR>
      <TD>
        <?php echo SmartSieve::text('Keep a copy in Inbox:');?> 
      </TD>
      <TD>
        <INPUT TYPE="checkbox" NAME="keep" VALUE="keep"<?php echo ($display['keep'] == true) ? ' CHECKED="checked"' : '';?>>
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
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
          |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Enable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Enable');?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Disable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Disable');?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Delete');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Delete');?></a>
<?php endif; ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>


<INPUT TYPE="hidden" NAME="priority" VALUE="<?php echo $display['priority'];?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($display['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="flg" VALUE="<?php echo $display['flg'];?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="anyof" VALUE="">
<INPUT TYPE="hidden" NAME="action" VALUE="address">
<INPUT TYPE="hidden" NAME="regexp" VALUE="">
<INPUT TYPE="hidden" NAME="continue" VALUE="">
<INPUT TYPE="hidden" NAME="stop" VALUE="">
<INPUT TYPE="hidden" NAME="custom" VALUE="">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>
