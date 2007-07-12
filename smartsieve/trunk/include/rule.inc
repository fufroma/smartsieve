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
      <TD CLASS="<?php echo ($display['status'] == 'ENABLED') ? 'enabled' : 'disabled';?>">
        <?php echo ($display['status'] == 'ENABLED') ? SmartSieve::text('ENABLED') : SmartSieve::text('DISABLED');?> 
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
    <INPUT TYPE="checkbox" NAME="continue" VALUE="continue"<?php echo ($display['startNewBlock'] == true) ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text('Next rule starts a new if/elseif/else block');?> 
<?php if (SmartSieve::getConf('allow_regex')): ?>
    <INPUT TYPE="checkbox" NAME="regexp" VALUE="regexp"<?php echo ($display['useRegex'] == true) ? ' CHECKED="checked"' : '';?>><?php echo SmartSieve::text('Use regular expression matching');?> 
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
          <OPTION VALUE="0"<?php echo (empty($display['matchAny'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('all of');?></OPTION>
          <OPTION VALUE="1"<?php echo (!empty($display['matchAny'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('any of');?></OPTION>
        </SELECT>
      </TD>
      <TD NOWRAP="nowrap">
        &nbsp;
      </TD>
    </TR>
<?php $i = 0; foreach ($display['conditions'] as $condition):?>
    <TR>
      <TD>
        &nbsp;
        <SELECT NAME="condition<?php echo $i;?>" onchange="document.thisRule.submit();return true;">
<?php if ($condition['type'] == 'new'):?>
          <OPTION VALUE="new" SELECTED="selected">Add condition</OPTION>
<?php endif;?>
          <OPTION VALUE="from"<?php echo ($condition['type'] == 'from') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'From:' contains");?></OPTION>
          <OPTION VALUE="to"<?php echo ($condition['type'] == 'to') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'To:' contains");?></OPTION>
          <OPTION VALUE="subject"<?php echo ($condition['type'] == 'subject') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'Subject:' contains");?></OPTION>
          <OPTION VALUE="size"<?php echo ($condition['type'] == 'size') ? ' SELECTED="selected"' : ((in_array('size', $display['usedConditions'])) ? ' DISABLED="disabled"' : '');?>><?php echo SmartSieve::text("If message size is");?></OPTION>
          <OPTION VALUE="header"<?php echo ($condition['type'] == 'header') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If mail header");?></OPTION>
        </SELECT>
      </TD>
      <TD>
<?php if ($condition['type'] == 'new'):?>
        &nbsp;
<?php elseif ($condition['type'] == 'header'):?>
        <INPUT TYPE="text" NAME="field<?php echo $i;?>" VALUE="<?php echo $condition['header'];?>" SIZE="20">
        <?php echo SmartSieve::text("contains");?>:
        <INPUT TYPE="text" NAME="field_val<?php echo $i;?>" VALUE="<?php echo $condition['matchStr'];?>" SIZE="30">
        <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
        &nbsp;<A HREF="" onclick="document.forms.thisRule.field<?php echo $i;?>.value=''; document.forms.thisRule.field_val<?php echo $i;?>.value=''; document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;">
          <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>'" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';">
        </a>
<?php elseif ($condition['type'] == 'size'):?>
        <SELECT NAME="gthan">
          <OPTION VALUE="0"<?php echo (empty($condition['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("less than");?></OPTION>
          <OPTION VALUE="1"<?php echo (!empty($condition['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("greater than");?></OPTION>
        </SELECT>
        <INPUT TYPE="text" NAME="size" VALUE="<?php echo $condition['size'];?>" SIZE="5"> <?php echo SmartSieve::text("KiloBytes");?>
        <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
        &nbsp;<A HREF="" onclick="document.forms.thisRule.size.value=''; document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;">
          <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>'" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';">
        </a>
<?php else:?>
        <INPUT TYPE="text" NAME="<?php echo $condition['type'] . $i;?>" VALUE="<?php echo $condition['matchStr'];?>" SIZE="50">
        <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
        &nbsp;<A HREF="" onclick="document.forms.thisRule.<?php echo $condition['type'] . $i;?>.value=''; document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;">
          <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>'" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';">
        </a>
<?php endif;?>
      </TD>
    </TR>
<?php $i++; endforeach;?>
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
        <INPUT TYPE="hidden" NAME="address" VALUE="<?php echo (!empty($display['action']['address'])) ? $display['action']['address'] : '';?>">
        <INPUT TYPE="hidden" NAME="reject" VALUE="<?php echo (!empty($display['action']['message'])) ? $display['action']['message'] : '';?>">
        <INPUT TYPE="hidden" NAME="custom" VALUE="<?php echo (!empty($display['action']['sieve'])) ? $display['action']['sieve'] : '';?>">
<?php elseif ($display['action']['type'] == 'address'):?>
        <INPUT TYPE="hidden" NAME="folder" VALUE="<?php echo (!empty($display['action']['folder'])) ? $display['action']['folder'] : '';?>">
        <INPUT TYPE="text" NAME="address" VALUE="<?php echo (!empty($display['action']['address'])) ? $display['action']['address'] : '';?>" SIZE="40">
        <INPUT TYPE="hidden" NAME="reject" VALUE="<?php echo (!empty($display['action']['message'])) ? $display['action']['message'] : '';?>">
        <INPUT TYPE="hidden" NAME="custom" VALUE="<?php echo (!empty($display['action']['sieve'])) ? $display['action']['sieve'] : '';?>">
<?php elseif ($display['action']['type'] == 'reject'):?>
        <INPUT TYPE="hidden" NAME="folder" VALUE="<?php echo (!empty($display['action']['folder'])) ? $display['action']['folder'] : '';?>">
        <INPUT TYPE="hidden" NAME="address" VALUE="<?php echo (!empty($display['action']['address'])) ? $display['action']['address'] : '';?>">
        <TEXTAREA NAME="reject" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php echo $display['action']['message'];?></TEXTAREA>
        <INPUT TYPE="hidden" NAME="custom" VALUE="<?php echo (!empty($display['action']['sieve'])) ? $display['action']['sieve'] : '';?>">
<?php elseif ($display['action']['type'] == 'discard'):?>
        <INPUT TYPE="hidden" NAME="folder" VALUE="<?php echo (!empty($display['action']['folder'])) ? $display['action']['folder'] : '';?>">
        <INPUT TYPE="hidden" NAME="address" VALUE="<?php echo (!empty($display['action']['address'])) ? $display['action']['address'] : '';?>">
        <INPUT TYPE="hidden" NAME="reject" VALUE="<?php echo (!empty($display['action']['message'])) ? $display['action']['message'] : '';?>">
        <INPUT TYPE="hidden" NAME="custom" VALUE="<?php echo (!empty($display['action']['sieve'])) ? $display['action']['sieve'] : '';?>">
<?php endif;?>
      </TD>
    </TR>
    <TR>
      <TD>
        <INPUT TYPE="checkbox" NAME="keep" VALUE="keep"<?php echo ($display['keep'] == true) ? ' CHECKED="checked"' : '';?>> <?php echo SmartSieve::text('Keep a copy in your Inbox');?> 
      </TD>
      <TD>
        &nbsp;
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
        <A CLASS="option" HREF="" onclick="Submit('save'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Save Changes");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Save Changes");?></a>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
          |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Enable");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Enable");?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Disable");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Disable");?></a>
          |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='<?php echo SmartSieve::text("Delete");?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text("Delete");?></a>
<?php endif; ?>
          |
        <a class="option" href="<?php echo SmartSieve::setUrl('rule.php?mode=custom');?>" onmouseover="window.status='<?php echo SmartSieve::text('Custom rule');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Custom rule');?></a>
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
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>
