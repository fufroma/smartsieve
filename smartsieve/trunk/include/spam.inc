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
      <TD CLASS="<?php echo (isset($rule['status']) && $rule['status'] == 'ENABLED') ? 'enabled' : 'disabled';?>">
        <?php echo (isset($rule['status']) && $rule['status'] == 'ENABLED') ? SmartSieve::text('ENABLED') : SmartSieve::text('DISABLED');?> 
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
      <TD>
        <INPUT TYPE="radio" NAME="action" VALUE="folder"<?php echo (!isset($rule['action']) || $rule['action'] == 'folder') ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text("File Into");?>:
      </TD>
      <TD>
        <SELECT NAME="folder">
<?php foreach ($_SESSION['smartsieve']['mailboxes'] as $mbox): ?>
          <OPTION VALUE="<?php echo $mbox;?>"<?php echo (isset($rule['action_arg']) && $rule['action_arg'] == $mbox) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::mutf7Decode($mbox);?></OPTION>
<?php endforeach; ?>
        </SELECT>
      </TD>
    </TR>
    <TR>
      <TD>
        <INPUT TYPE="radio" NAME="action" VALUE="reject"<?php echo (isset($rule['action']) && $rule['action'] == 'reject') ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text("Send a reject message");?>:
      </TD>
      <TD>
        <TEXTAREA NAME="reject" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php if (isset($rule['action_arg']) && $rule['action'] == 'reject') echo SmartSieve::utf8Decode($rule['action_arg']);?></TEXTAREA>
      </TD>
    </TR>
    <TR>
      <TD>
        <INPUT TYPE="radio" NAME="action" VALUE="discard"<?php echo (isset($rule['action']) && $rule['action'] == 'discard') ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text("Discard the message");?> 
      </TD>
      <TD>
        &nbsp;
      </TD>
    </TR>
      <TD>
        <INPUT TYPE="checkbox" NAME="stop" VALUE="stop"<?php echo (!empty($rule['stop'])) ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text('Stop processing filter rules');?>
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


<INPUT TYPE="hidden" NAME="priority" VALUE="<?php echo (isset($rule['priority'])) ? $rule['priority'] : $script->pcount+1;?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo (isset($rule['status'])) ? $rule['status'] : 'ENABLED';?>">
<INPUT TYPE="hidden" NAME="flg" VALUE="<?php echo (isset($rule['flg'])) ? $rule['flg'] : '';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="anyof" VALUE="">
<INPUT TYPE="hidden" NAME="from" VALUE="">
<INPUT TYPE="hidden" NAME="to" VALUE="">
<INPUT TYPE="hidden" NAME="subject" VALUE="">
<INPUT TYPE="hidden" NAME="gthan" VALUE="">
<INPUT TYPE="hidden" NAME="size" VALUE="">
<INPUT TYPE="hidden" NAME="field" VALUE="<?php echo $config['header'];?>">
<INPUT TYPE="hidden" NAME="field_val" VALUE="<?php echo $config['value'];?>">
<INPUT TYPE="hidden" NAME="regexp" VALUE="<?php echo (!empty($config['regexp'])) ? 'regexp' : '';?>">
<INPUT TYPE="hidden" NAME="continue" VALUE="continue">
<INPUT TYPE="hidden" NAME="custom" VALUE="">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>
