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
        <?php echo SmartSieve::text('Edit mail forwarding');?> 
      </TD>
      <TD ALIGN="right">
        &nbsp;<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('This rule is enabled') : SmartSieve::text('This rule is disabled');?>
        &nbsp;<IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images'); echo ($script->isRuleEnabled($ruleID)) ? '/tick.gif' : '/delete.gif';?>" BORDER="0">
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

    <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
      <TR>
        <TD>
          <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
            <TR>
              <TH><?php echo SmartSieve::text('Mail forwarding');?></TH>
            </TR>
          </TABLE>
        </TD>
      </TR>
      <TR>
        <TD>
          <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
            <TR CLASS="active">
              <TD>
                <?php echo SmartSieve::text('Forward mail to address:');?></TH>
              </TD>
              <TD>
                <INPUT TYPE="text" NAME="<?php echo ACTION_REDIRECT;?>0" VALUE="<?php echo $rule['actions'][0]['address'];?>" SIZE="40">
              </TD>
            </TR>
            <TR CLASS="active">
              <TD>
                <?php echo SmartSieve::text('Keep a copy in Inbox:');?> 
              </TD>
              <TD>
                <INPUT TYPE="checkbox" NAME="action1" VALUE="<?php echo ACTION_KEEP;?>"<?php echo (!empty($rule['actions'][1]) && $rule['actions'][1]['type'] == ACTION_KEEP) ? ' CHECKED="checked"' : '';?>>
              </TD>
            </TR>
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


<INPUT TYPE="hidden" NAME="anyof" VALUE="0">
<INPUT TYPE="hidden" NAME="control" VALUE="<?php echo CONTROL_ELSEIF;?>">
<INPUT TYPE="hidden" NAME="action0" VALUE="<?php echo ACTION_REDIRECT;?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($rule['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">
<INPUT TYPE="hidden" NAME="special" VALUE="<?php echo RULE_TAG_FORWARD;?>">

</FORM>
