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
      <TD CLASS="<?php echo ($rule['status'] == 'ENABLED') ? 'enabled' : 'disabled';?>">
        <?php echo ($rule['status'] == 'ENABLED') ? SmartSieve::text('ENABLED') : SmartSieve::text('DISABLED');?> 
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
        <select name="control">
            <option value="<?php echo CONTROL_IF;?>"<?php echo ($rule['control'] == CONTROL_IF) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('If');?></option>
            <option value="<?php echo CONTROL_ELSEIF;?>"<?php echo ($rule['control'] == CONTROL_ELSEIF) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('Else If');?></option>
        </select>
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
          <OPTION VALUE="0"<?php echo (empty($rule['matchAny'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('all of');?></OPTION>
          <OPTION VALUE="<?php echo ANYOF_BIT;?>"<?php echo (!empty($rule['matchAny'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('any of');?></OPTION>
        </SELECT>
      </TD>
      <TD NOWRAP="nowrap">
        &nbsp;
      </TD>
    </TR>
<?php $i = 0; foreach ($rule['conditions'] as $condition):?>
    <TR>
      <TD>
        &nbsp;
        <SELECT NAME="condition<?php echo $i;?>" onchange="document.thisRule.submit();return true;">
<?php if ($condition['type'] == 'new'):?>
          <OPTION VALUE="new" SELECTED="selected">Add condition</OPTION>
<?php endif;?>
          <OPTION VALUE="from"<?php echo ($condition['type'] == TEST_ADDRESS && $condition['header'] == 'from') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'From:' contains");?></OPTION>
          <OPTION VALUE="to"<?php echo ($condition['type'] == TEST_ADDRESS && $condition['header'] == 'to') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'To:' contains");?></OPTION>
          <OPTION VALUE="subject"<?php echo ($condition['type'] == TEST_HEADER && $condition['header'] == 'subject') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'Subject:' contains");?></OPTION>
          <OPTION VALUE="size"<?php echo ($condition['type'] == TEST_SIZE) ? ' SELECTED="selected"' : (($sizeUsed == true) ? ' DISABLED="disabled"' : '');?>><?php echo SmartSieve::text("If message size is");?></OPTION>
          <OPTION VALUE="header"<?php echo ($condition['type'] == TEST_HEADER && $condition['header'] !== 'subject') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If mail header");?></OPTION>
        </SELECT>
      </TD>
      <TD>
<?php if ($condition['type'] == 'new'):?>
        &nbsp;
<?php elseif ($condition['type'] == TEST_HEADER && $condition['header'] !== 'subject'):?>
        <INPUT TYPE="text" NAME="header<?php echo $i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['header']);?>" SIZE="20">
        <select name="matchType<?php echo $i;?>">
            <option value="is"<?php echo ($condition['matchType'] == MATCH_IS && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('is');?></option>
            <option value="notis"<?php echo ($condition['matchType'] == MATCH_IS && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('is not');?></option>
            <option value="contains"<?php echo ($condition['matchType'] == MATCH_CONTAINS && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('contains');?></option>
            <option value="notcontains"<?php echo ($condition['matchType'] == MATCH_CONTAINS && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('does not contain');?></option>
            <option value="matches"<?php echo ($condition['matchType'] == MATCH_MATCHES && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('matches');?></option>
            <option value="notmatches"<?php echo ($condition['matchType'] == MATCH_MATCHES && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('does not match');?></option>
            <option value="regex"<?php echo ($condition['matchType'] == MATCH_REGEX && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('matches regular expression');?></option>
            <option value="notregex"<?php echo ($condition['matchType'] == MATCH_REGEX && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('does not match regular expression');?></option>
        </select>
        <INPUT TYPE="text" NAME="headerMatchStr<?php echo $i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['matchStr']);?>" SIZE="30">
        <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
        &nbsp;<A HREF="" onclick="document.forms.thisRule.header<?php echo $i;?>.value=''; document.forms.thisRule.headerMatchStr<?php echo $i;?>.value=''; document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;">
          <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>'" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';">
        </a>
<?php elseif ($condition['type'] == TEST_SIZE):?>
        <SELECT NAME="gthan">
          <OPTION VALUE="0"<?php echo (empty($condition['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("less than");?></OPTION>
          <OPTION VALUE="<?php echo SIZE_BIT;?>"<?php echo (!empty($condition['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("greater than");?></OPTION>
        </SELECT>
        <INPUT TYPE="text" NAME="size" VALUE="<?php echo $condition['kbytes'];?>" SIZE="5"> <?php echo SmartSieve::text("KiloBytes");?>
        <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
        &nbsp;<A HREF="" onclick="document.forms.thisRule.size.value=''; document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;">
          <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>'" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';">
        </a>
<?php else:?>
        <select name="matchType<?php echo $i;?>">
            <option value="is"<?php echo ($condition['matchType'] == MATCH_IS && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('is');?></option>
            <option value="notis"<?php echo ($condition['matchType'] == MATCH_IS && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('is not');?></option>
            <option value="contains"<?php echo ($condition['matchType'] == MATCH_CONTAINS && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('contains');?></option>
            <option value="notcontains"<?php echo ($condition['matchType'] == MATCH_CONTAINS && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('does not contain');?></option>
            <option value="matches"<?php echo ($condition['matchType'] == MATCH_MATCHES && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('matches');?></option>
            <option value="notmatches"<?php echo ($condition['matchType'] == MATCH_MATCHES && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('does not match');?></option>
            <option value="regex"<?php echo ($condition['matchType'] == MATCH_REGEX && empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('matches regular expression');?></option>
            <option value="notregex"<?php echo ($condition['matchType'] == MATCH_REGEX && !empty($condition['not'])) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('does not match regular expression');?></option>
        </select>
        <INPUT TYPE="text" NAME="<?php echo $condition['header'] . $i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['matchStr']);?>" SIZE="50">
        <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
        &nbsp;<A HREF="" onclick="document.forms.thisRule.<?php echo $condition['header'] . $i;?>.value=''; document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;">
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
<?php $i = 0; foreach ($rule['actions'] as $action):?>
    <TR>
      <TD>
        <SELECT NAME="action<?php echo $i;?>" onchange="document.thisRule.submit(); return true;">
<?php if ($action['type'] == 'new'):?>
          <OPTION VALUE="new" SELECTED="selected">Add action</OPTION>
<?php endif;?>
          <OPTION VALUE="<?php echo ACTION_FILEINTO;?>"<?php echo ($action['type'] == ACTION_FILEINTO) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("File Into");?></OPTION>
          <OPTION VALUE="<?php echo ACTION_REDIRECT;?>"<?php echo ($action['type'] == ACTION_REDIRECT) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Forward to address");?></OPTION>
          <OPTION VALUE="<?php echo ACTION_REJECT;?>"<?php echo ($action['type'] == ACTION_REJECT) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Send a reject message");?></OPTION>
          <OPTION VALUE="<?php echo ACTION_DISCARD;?>"<?php echo ($action['type'] == ACTION_DISCARD) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Discard the message");?></OPTION>
          <OPTION VALUE="<?php echo ACTION_KEEP;?>"<?php echo ($action['type'] == ACTION_KEEP) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Keep a copy in your Inbox');?></OPTION>
          <OPTION VALUE="<?php echo ACTION_STOP;?>"<?php echo ($action['type'] == ACTION_STOP) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Stop processing filter rules');?></OPTION>
        </SELECT>
      </TD>
      <TD>
        &nbsp;
<?php if ($action['type'] == ACTION_FILEINTO):?>
        <SELECT NAME="<?php echo ACTION_FILEINTO . $i;?>">
<?php foreach ($_SESSION['smartsieve']['mailboxes'] as $mbox): ?>
          <OPTION VALUE="<?php echo $mbox;?>"<?php echo ($action['folder'] == $mbox) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::mutf7Decode($mbox);?></OPTION>
<?php endforeach; ?>
        </SELECT>
<?php elseif ($action['type'] == ACTION_REDIRECT):?>
        <INPUT TYPE="text" NAME="<?php echo ACTION_REDIRECT . $i;?>" VALUE="<?php echo (!empty($action['address'])) ? SmartSieve::utf8Decode($action['address']) : '';?>" SIZE="40">
<?php elseif ($action['type'] == ACTION_REJECT):?>
        <TEXTAREA NAME="<?php echo ACTION_REJECT . $i;?>" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php echo SmartSieve::utf8Decode($action['message']);?></TEXTAREA>
<?php elseif ($action['type'] == ACTION_DISCARD || $action['type'] == ACTION_KEEP || $action['type'] == ACTION_STOP):?>
<?php endif;?>
      </TD>
    </TR>
<?php $i++; endforeach;?>
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


<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($rule['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>
