<FORM ACTION="<?php print SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
        <?php echo SmartSieve::text("Edit Mail Filter Rule");?> 
      </TD>
      <TD CLASS="<?php echo (isset($rule['status']) && $rule['status'] == 'ENABLED') ? 'enabled' : 'disabled';?>">
        <?php echo (isset($rule['status']) && $rule['status'] == 'ENABLED') ? SmartSieve::text('ENABLED') : SmartSieve::text('DISABLED');?> 
<?php else: ?>
        <?php echo SmartSieve::text("New Mail Filter Rule");?> 
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

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR>
      <TD>
    <INPUT TYPE="checkbox" NAME="continue" VALUE="continue"<?php echo (!empty($rule['continue'])) ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text('Check message against next rule also');?> 
    <INPUT TYPE="checkbox" NAME="keep" VALUE="keep"<?php echo (!empty($rule['keep'])) ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text('Keep a copy of the message in your Inbox');?> 
<?php if (SmartSieve::getConf('allow_regex')): ?>
    <INPUT TYPE="checkbox" NAME="regexp" VALUE="regexp"<?php echo (!empty($rule['regexp'])) ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text('Use regular expressions');?> 
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
      <TD CLASS="heading">
        <?php echo SmartSieve::text('CONDITIONS');?>:
      </TD>
      <TD CLASS="heading">
        &nbsp;
      </TD>
    </TR>
    <TR>
      <TD NOWRAP="nowrap">
        <?php echo SmartSieve::text('Match');?> 
        <SELECT NAME="anyof">
          <OPTION VALUE="0"<?php echo (empty($rule['anyof'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('all of');?></OPTION>
          <OPTION VALUE="1"<?php echo (!empty($rule['anyof'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('any of');?></OPTION>
        </SELECT>
      </TD>
      <TD NOWRAP="nowrap">
        <?php echo SmartSieve::text("If message 'From:' contains");?>: <INPUT TYPE="text" NAME="from" VALUE="<?php echo (isset($rule['from'])) ? SmartSieve::utf8Decode($rule['from']) : '';?>" SIZE="50">
      </TD>
    </TR>
    <TR>
      <TD>
        &nbsp;
      </TD>
      <TD>
        <?php echo SmartSieve::text("If message 'To:' contains");?>: <INPUT TYPE="text" NAME="to" VALUE="<?php echo (isset($rule['to'])) ? SmartSieve::utf8Decode($rule['to']) : '';?>" SIZE="50">
      </TD>
    </TR>
    <TR>
      <TD>
        &nbsp;
      </TD>
      <TD>
        <?php echo SmartSieve::text("If message 'Subject:' contains");?>: <INPUT TYPE="text" NAME="subject" VALUE="<?php echo (isset($rule['subject'])) ? SmartSieve::utf8Decode($rule['subject']) : '';?>" SIZE="50">
      </TD>
    </TR>
    <TR>
      <TD>
        &nbsp;
      </TD>
      <TD>
        <?php echo SmartSieve::text("If message size is");?> 
        <SELECT NAME="gthan">
          <OPTION VALUE="0"<?php echo (empty($rule['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("less than");?></OPTION>
          <OPTION VALUE="1"<?php echo (!empty($rule['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("greater than");?></OPTION>
        </SELECT>
        <INPUT TYPE="text" NAME="size" VALUE="<?php echo (isset($rule['size'])) ? $rule['size'] : '';?>" SIZE="5"> <?php echo SmartSieve::text("KiloBytes");?> 
      </TD>
    </TR>
    <TR>
      <TD>
        &nbsp;
      </TD>
      <TD>
        <?php echo SmartSieve::text("If mail header");?>: <INPUT TYPE="text" NAME="field" VALUE="<?php echo (isset($rule['field'])) ? SmartSieve::utf8Decode($rule['field']) : '';?>" SIZE="20"> <?php echo SmartSieve::text("contains");?>: <INPUT TYPE="text" NAME="field_val" VALUE="<?php echo (isset($rule['field_val'])) ? SmartSieve::utf8Decode($rule['field_val']) : '';?>" SIZE="30">
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
        <INPUT TYPE="radio" NAME="action" VALUE="folder"<?php echo (isset($rule['action']) && $rule['action'] == 'folder') ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text("File Into");?>:
      </TD>
      <TD>
        <SELECT NAME="folder">
<?php foreach ($_SESSION['smartsieve']['mailboxes'] as $mbox): ?>
          <OPTION VALUE="<?php echo $mbox;?>"<?php echo (isset($rule['action_arg']) && $rule['action_arg'] == $mbox) ? ' SELECTED="selected"' : '';?>><?php echo $mbox;?></OPTION>
<?php endforeach; ?>
        </SELECT>
      </TD>
    </TR>
    <TR>
      <TD>
        <INPUT TYPE="radio" NAME="action" VALUE="address"<?php echo (isset($rule['action']) && $rule['action'] == 'address') ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text("Forward to address");?>:
      </TD>
      <TD>
        <INPUT TYPE="text" NAME="address" VALUE="<?php echo (isset($rule['action_arg']) && $rule['action'] == 'address') ? SmartSieve::utf8Decode($rule['action_arg']) : '';?>" SIZE="40">
      </TD>
    </TR>
    <TR>
      <TD>
        <INPUT TYPE="radio" NAME="action" VALUE="reject"<?php echo (isset($rule['action']) && $rule['action'] == 'reject') ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text("Send a reject message");?>:
      </TD>
      <TD>
        <TEXTAREA NAME="reject" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php if (isset($rule['action_arg']) && $rule['action'] == 'reject') echo SmartSieve::utf8Decode($rule['action_arg']);?></TEXTAREA>
      </TD>
    </TR>
    <TR>
      <TD>
        <INPUT TYPE="radio" NAME="action" VALUE="discard"<?php echo (isset($rule['action']) && $rule['action'] == 'discard') ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text("Discard the message");?>.
      </TD>
      <TD>
        &nbsp;
      </TD>
    </TR>
<?php if (SmartSieve::getConf('allow_custom') !== false || (isset($rule['action']) && $rule['action'] == 'custom')): ?>
    <TR>
      <TD>
        <INPUT TYPE="radio" NAME="action" VALUE="custom"<?php echo (isset($rule['action']) && $rule['action'] == 'custom') ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text('Custom Rule');?>:
      </TD>
      <TD>
        <TEXTAREA NAME="custom" ROWS="3" COLS="40" WRAP="off"><?php if (isset($rule['action_arg']) && $rule['action'] == 'custom') echo SmartSieve::utf8Decode($rule['action_arg']);?></TEXTAREA>
      </TD>
    </TR>
<?php endif; ?>
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
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
          |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Enable");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Enable");?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Disable");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Disable");?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Delete");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Delete");?></a>
<?php endif; ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>


<INPUT TYPE="hidden" NAME="priority" VALUE="<?php echo (isset($rule['priority'])) ? $rule['priority']
: $script->pcount+1;?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo (isset($rule['status'])) ? $rule['status'] : 'ENABLED';?>">
<INPUT TYPE="hidden" NAME="flg" VALUE="<?php echo (isset($rule['flg'])) ? $rule['flg'] : '';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">

</FORM>

</BODY>
</HTML>

