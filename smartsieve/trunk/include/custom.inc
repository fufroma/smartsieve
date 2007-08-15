<FORM ACTION="<?php echo SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD>

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="breadcrumb">
        &nbsp;<a href="<?php echo SmartSieve::setUrl('scripts.php');?>"><?php echo SmartSieve::text('Scripts');?>&nbsp;&gt;</a>
        &nbsp;<A HREF="<?php echo SmartSieve::setUrl('main.php');?>"><?php echo SmartSieve::text('Filter rules for "%s"', array($script->getName()));?>&nbsp;&gt;</A>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
        &nbsp;<?php echo SmartSieve::text('Edit filter rule');?>
<?php else: ?>
        &nbsp;<?php echo SmartSieve::text('New filter rule');?>
<?php endif;?>
      </TD>
    </TR>
    <TR CLASS="heading">
      <TD ALIGN="right">
<?php if ($_SESSION['smartsieve']['workingScript'] === SmartSieve::getActiveScript()): ?>
        &nbsp;<?php echo SmartSieve::text('Script "%s" is active', array($script->getName()));?>
<?php else:?>
        &nbsp;<?php echo SmartSieve::text('Script "%s" is not active', array($script->getName()));?>
<?php endif;?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
        <?php echo SmartSieve::text('Edit filter rule');?>
      </TD>
      <TD ALIGN="right">
        &nbsp;<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('This rule is enabled') : SmartSieve::text('This rule is disabled');?>
        &nbsp;<IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images'); echo ($script->isRuleEnabled($ruleID)) ? '/tick.gif' : '/disable.gif';?>" BORDER="0">
<?php else: ?>
        <?php echo SmartSieve::text('New filter rule');?>
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

    <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
       <TR>
        <TD>
          <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
            <TR>
              <TH><?php echo SmartSieve::text("Custom sieve rule");?></TH>
              <TH>&nbsp;</TH>
            </TR>
          </TABLE>
        </TD>
      </TR>
      <TR>
        <TD>
          <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
            <TR CLASS="active">
              <TD>
                <select name="control">
                  <option value="<?php echo CONTROL_IF;?>"<?php echo ($rule['control'] == CONTROL_IF) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('If');?></option>
                  <option value="<?php echo CONTROL_ELSEIF;?>"<?php echo ($rule['control'] == CONTROL_ELSEIF) ? ' selected="selected"' : '';?>><?php echo SmartSieve::text('Else If');?></option>
                </select>
              </TD>
            </TR>
            <TR CLASS="active">
              <TD>
                <TEXTAREA NAME="sieve" ROWS="3" COLS="40" WRAP="off"><?php echo $rule['actions'][0]['sieve'];?></TEXTAREA>
              </TD>
            </TR>
<?php if (!isset($ruleID) || $ruleID == 'new') :?>
            <TR CLASS="active">
              <TD>
                <?php echo SmartSieve::text('Save at position');?>
                <select name="position">
<?php for ($i=0; $i<count($script->rules); $i++):?>
                  <option value="<?php echo $i;?>"<?php echo ($ruleID === $i) ? ' selected="selected"' : '';?>><?php echo $i+1;?></option>
<?php endfor;?>
                  <option value="<?php echo count($script->rules);?>"<?php echo (!isset($ruleID) || $ruleID == 'new') ? ' selected="selected"' : '';?>><?php echo count($script->rules)+1;?></option>
                </select>
              </TD>
            </TR>
<?php endif;?>
          </TABLE>
        </TD>
      </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="heading">
    &nbsp;
  </TD>
</TR>
<TR>
  <TD>

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
      <TR>
        <TD CLASS="options">
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


<INPUT TYPE="hidden" NAME="anyof" VALUE="<?php echo (!empty($rule['matchAny'])) ? ANYOF_BIT : 0;?>">
<?php $i = 0; foreach ($rule['conditions'] as $condition):?>
<?php if ($condition['type'] == 'new'):?>
<?php elseif ($condition['type'] == TEST_HEADER && $condition['header'] !== 'subject'):?>
<INPUT TYPE="hidden" NAME="condition<?php echo $i;?>" VALUE="header">
<INPUT TYPE="hidden" NAME="header<?php echo $i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['header']);?>">
<INPUT TYPE="hidden" NAME="headerMatchStr<?php echo $i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['matchStr']);?>">
<?php elseif ($condition['type'] == TEST_SIZE):?>
<INPUT TYPE="hidden" NAME="condition<?php echo $i;?>" VALUE="size">
<INPUT TYPE="hidden" NAME="gthan" VALUE="<?php echo (!empty($condition['gthan'])) ? SIZE_BIT : 0;?>">
<INPUT TYPE="hidden" NAME="size" VALUE="<?php echo $condition['kbytes'];?>">
<?php else:?>
<INPUT TYPE="hidden" NAME="condition<?php echo $i;?>" VALUE="<?php echo $condition['header'];?>">
<INPUT TYPE="hidden" NAME="<?php echo $condition['header'].$i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['matchStr']);?>">
<?php endif;?>
<?php if ($condition['type'] != TEST_SIZE && $condition['type'] != 'new'):?>
<?php if ($condition['matchType'] == MATCH_IS):?>
<INPUT TYPE="hidden" NAME="matchType<?php echo $i;?>" VALUE="<?php echo (empty($condition['not'])) ? 'is' : 'notis';?>">
<?php elseif ($condition['matchType'] == MATCH_CONTAINS):?>
<INPUT TYPE="hidden" NAME="matchType<?php echo $i;?>" VALUE="<?php echo (empty($condition['not'])) ? 'contains' : 'notcontains';?>">
<?php elseif ($condition['matchType'] == MATCH_MATCHES):?>
<INPUT TYPE="hidden" NAME="matchType<?php echo $i;?>" VALUE="<?php echo (empty($condition['not'])) ? 'matches' : 'notmatches';?>">
<?php elseif ($condition['matchType'] == MATCH_REGEX):?>
<INPUT TYPE="hidden" NAME="matchType<?php echo $i;?>" VALUE="<?php echo (empty($condition['not'])) ? 'regex' : 'notregex';?>">
<?php endif;?>
<?php endif;?>
<?php $i++; endforeach;?>
<INPUT TYPE="hidden" NAME="action0" VALUE="<?php echo ACTION_CUSTOM;?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($rule['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>