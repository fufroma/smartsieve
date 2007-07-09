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
    <INPUT TYPE="hidden" NAME="regexp" VALUE="<?php echo ($display['useRegex'] == true) ? 'regexp' : '';?>">
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
        <?php echo SmartSieve::text("Custom sieve rule");?>:
      </TD>
      <TD CLASS="heading">
        &nbsp;
      </TD>
    </TR>
    <TR>
      <TD>
        <TEXTAREA NAME="custom" ROWS="3" COLS="40" WRAP="off"><?php echo $display['action']['sieve'];?></TEXTAREA>
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
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>


<INPUT TYPE="hidden" NAME="anyof" VALUE="<?php echo ($display['matchAny'] === true) ? 1 : 0;?>">
<?php $i = 0; foreach ($display['conditions'] as $condition):?>
<INPUT TYPE="hidden" NAME="condition<?php echo $i;?>" VALUE="<?php echo $condition['type'];?>">
<?php if ($condition['type'] == 'new'):?>
<?php elseif ($condition['type'] == 'header'):?>
<INPUT TYPE="hidden" NAME="field" VALUE="<?php echo $condition['header'];?>">
<INPUT TYPE="hidden" NAME="field_val" VALUE="<?php echo $condition['matchStr'];?>">
<?php elseif ($condition['type'] == 'size'):?>
<INPUT TYPE="hidden" NAME="gthan" VALUE="<?php echo (!empty($condition['gthan'])) ? 1 : 0;?>">
<INPUT TYPE="hidden" NAME="size" VALUE="<?php echo $condition['size'];?>">
<?php else:?>
<INPUT TYPE="hidden" NAME="<?php echo $condition['type'];?>" VALUE="<?php echo $condition['matchStr'];?>">
<?php endif;?>
<?php $i++; endforeach;?>
<INPUT TYPE="hidden" NAME="action" VALUE="custom">
<INPUT TYPE="hidden" NAME="keep" VALUE="<?php echo ($display['keep'] == true) ? 'keep' : '';?>">
<INPUT TYPE="hidden" NAME="stop" VALUE="<?php echo ($display['stop'] == true) ? 'stop' : '';?>">
<INPUT TYPE="hidden" NAME="priority" VALUE="<?php echo $display['priority'];?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($display['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="flg" VALUE="<?php echo $display['flg'];?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>
