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
      <TD ALIGN="right" class="heading">
<?php if ($_SESSION['smartsieve']['workingScript'] === SmartSieve::getActiveScript()): ?>
        &nbsp;<?php echo SmartSieve::text('Script "%s" is active', array($script->getName()));?>
<?php else:?>
        &nbsp;<?php echo SmartSieve::text('Script "%s" is not active', array($script->getName()));?>
<?php endif;?>
        &nbsp;<a href="<?php echo SmartSieve::setUrl('scripts.php');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images'); echo ($_SESSION['smartsieve']['workingScript'] === SmartSieve::getActiveScript()) ? '/tick.gif' : '/disable.gif';?>" title="<?php echo SmartSieve::text('Manage Scripts');?>" border="0"></a>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD>
    &nbsp;
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
        &nbsp;<a href="" onclick="document.thisRule.thisAction.value='<?php echo ($script->isRuleEnabled($ruleID)) ? FORM_ACTION_DISABLE : FORM_ACTION_ENABLE;?>'; document.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images'); echo ($script->isRuleEnabled($ruleID)) ? '/tick.gif' : '/disable.gif';?>" title="<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('Disable this rule') : SmartSieve::text('Enable this rule');?>" BORDER="0"></a>
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
          <input type="button" name="save" value="<?php echo SmartSieve::text('Save Changes');?>" onClick="document.thisRule.thisAction.value='<?php echo FORM_ACTION_SAVE;?>'; document.thisRule.submit(); return false;">
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
          <input type="button" name="enable" value="<?php echo SmartSieve::text('Enable');?>" onClick="document.thisRule.thisAction.value='<?php echo FORM_ACTION_ENABLE;?>'; document.thisRule.submit(); return false;">
          <input type="button" name="disable" value="<?php echo SmartSieve::text('Disable');?>" onClick="document.thisRule.thisAction.value='<?php echo FORM_ACTION_DISABLE;?>'; document.thisRule.submit(); return false;">
          <input type="button" name="delete" value="<?php echo SmartSieve::text('Delete');?>" onClick="Delete(); return false;">
<?php endif; ?>
          <input type="button" name="return" value="<?php echo SmartSieve::text('Return to Filters');?>" onClick="window.location='<?php echo SmartSieve::setUrl('main.php');?>';">
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
