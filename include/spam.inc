<FORM ACTION="<?php echo SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
        <?php echo SmartSieve::text('What do you want to do with spam?');?> 
      </TD>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
      <TD CLASS="<?php echo ($display['status'] == 'ENABLED') ? 'enabled' : 'disabled';?>">
        <?php echo ($display['status'] == 'ENABLED') ? SmartSieve::text('ENABLED') : SmartSieve::text('DISABLED');?>
      </TD>
<?php else: ?>
      <TD>
        &nbsp;
      </TD>
<?php endif; ?>
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
        <SELECT NAME="action" onchange="document.thisRule.submit(); return true;">
          <OPTION VALUE="folder"<?php echo ($display['action']['type'] == 'folder') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("File Into");?></OPTION>
          <OPTION VALUE="address"<?php echo ($display['action']['type'] == 'address') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Forward to address");?></OPTION>
          <OPTION VALUE="reject"<?php echo ($display['action']['type'] == 'reject') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Send a reject message");?></OPTION>
          <OPTION VALUE="discard"<?php echo ($display['action']['type'] == 'discard') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Discard the message");?></OPTION>
        </SELECT>
      </TD>
      <TD>
        &nbsp;
<?php if ($display['action']['type'] == 'folder'):?>
        <SELECT NAME="folder">
<?php foreach ($_SESSION['smartsieve']['mailboxes'] as $mbox): ?>
          <OPTION VALUE="<?php echo $mbox;?>"<?php echo ($display['action']['folder'] == $mbox) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::mutf7Decode($mbox);?></OPTION>
<?php endforeach; ?>
        </SELECT>
        <INPUT TYPE="hidden" NAME="address" VALUE="<?php echo $display['action']['address'];?>">
        <INPUT TYPE="hidden" NAME="reject" VALUE="<?php echo $display['action']['message'];?>">
        <INPUT TYPE="hidden" NAME="custom" VALUE="<?php echo $display['action']['sieve'];?>">
<?php elseif ($display['action']['type'] == 'address'):?>
        <INPUT TYPE="hidden" NAME="folder" VALUE="<?php echo $display['action']['folder'];?>">
        <INPUT TYPE="text" NAME="address" VALUE="<?php echo $display['action']['address'];?>" SIZE="40">
        <INPUT TYPE="hidden" NAME="reject" VALUE="<?php echo $display['action']['message'];?>">
        <INPUT TYPE="hidden" NAME="custom" VALUE="<?php echo $display['action']['sieve'];?>">
<?php elseif ($display['action']['type'] == 'reject'):?>
        <INPUT TYPE="hidden" NAME="folder" VALUE="<?php echo $display['action']['folder'];?>">
        <INPUT TYPE="hidden" NAME="address" VALUE="<?php echo $display['action']['address'];?>">
        <TEXTAREA NAME="reject" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php echo $display['action']['message'];?></TEXTAREA>
        <INPUT TYPE="hidden" NAME="custom" VALUE="<?php echo $display['action']['sieve'];?>">
<?php endif;?>
      </TD>
    </TR>
      <TD>
        <INPUT TYPE="checkbox" NAME="stop" VALUE="stop"<?php echo ($display['stop'] == true) ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text('Stop processing filter rules');?>
      </TD>
      <TD>
        &nbsp;
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
<INPUT TYPE="hidden" NAME="condition0" VALUE="header">
<INPUT TYPE="hidden" NAME="field" VALUE="<?php echo $config['header'];?>">
<INPUT TYPE="hidden" NAME="field_val" VALUE="<?php echo $config['value'];?>">
<INPUT TYPE="hidden" NAME="regexp" VALUE="<?php echo (!empty($config['regexp'])) ? 'regexp' : '';?>">
<INPUT TYPE="hidden" NAME="continue" VALUE="">
<INPUT TYPE="hidden" NAME="keep" VALUE="">
<INPUT TYPE="hidden" NAME="custom" VALUE="">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>
