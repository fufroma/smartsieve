<FORM ACTION="<?php echo SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD>

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="breadcrumb">
        &nbsp;<a href="<?php echo SmartSieve::setUrl('scripts.php');?>"><?php echo SmartSieve::text('Scripts');?>&nbsp;&gt;</a>
        &nbsp;<A HREF="<?php echo SmartSieve::setUrl('main.php');?>"><?php echo SmartSieve::text('Filter rules for "%s"', array($script->getName()));?>&nbsp;&gt;</A>
        &nbsp;<?php echo SmartSieve::text('Edit Whitelist');?> 
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
        <?php echo SmartSieve::text('Whitelist mail from the following addresses');?> 
      </TD>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
      <TD ALIGN="right">
        &nbsp;<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('This rule is enabled') : SmartSieve::text('This rule is disabled');?> 
        &nbsp;<a href="" onclick="document.thisRule.thisAction.value='<?php echo ($script->isRuleEnabled($ruleID)) ? FORM_ACTION_DISABLE : FORM_ACTION_ENABLE;?>'; document.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images'); echo ($script->isRuleEnabled($ruleID)) ? '/tick.gif' : '/disable.gif';?>" title="<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('Disable this rule') : SmartSieve::text('Enable this rule');?>" BORDER="0"></a>
<?php else: ?>
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
          <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
            <TR>
              <TH><?php echo SmartSieve::text('Addresses');?></TH>
              <TH>&nbsp;</TH>
            </TR>
          </TABLE>
        </TD>
      </TR>
      <TR>
        <TD>
          <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
<?php $i = 0; foreach ($rule['conditions'] as $condition):?>
<?php if ($condition['type'] != 'new'):?>
            <TR CLASS="active">
              <TD>
                &nbsp;
                <input type="hidden" name="condition<?php echo $i;?>" value="from">
                <input type="hidden" name="matchType<?php echo $i;?>" value="is">
                <input type="text" name="from<?php echo $i;?>" value="<?php echo (!empty($condition['matchStr'])) ? SmartSieve::utf8Decode($condition['matchStr']) : '';?>" SIZE="30">
                <input type="hidden" name="delete<?php echo $i;?>" value="">
                &nbsp;<A HREF="" onclick="document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>" TITLE="<?php echo SmartSieve::text('Delete condition');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';"></a>
              </TD>
            </TR>
<?php else:?>
            <TR CLASS="active">
              <TD>
                &nbsp;
                <input type="hidden" name="condition<?php echo $i;?>" value="new">
                <input type="hidden" name="matchType<?php echo $i;?>" value="is">
                <input type="hidden" name="from<?php echo $i;?>" value="<?php echo (!empty($condition['matchStr'])) ? SmartSieve::utf8Decode($condition['matchStr']) : '';?>" SIZE="30">
                <input type="hidden" name="delete<?php echo $i;?>" value="">
                <input type="button" name="new" value="New Address" onclick="document.thisRule.condition<?php echo $i;?>.value='from'; document.thisRule.submit(); return false;">
              </TD>
            </TR>
<?php endif;?>
<?php $i++; endforeach;?>
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


<input type="hidden" name="action0" value="<?php echo ACTION_STOP;?>">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($rule['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">
<INPUT TYPE="hidden" NAME="special" VALUE="<?php echo RULE_TAG_WHITELIST;?>">
<INPUT TYPE="hidden" NAME="control" VALUE="<?php echo CONTROL_IF;?>">
<INPUT TYPE="hidden" NAME="anyof" VALUE="<?php echo ANYOF_BIT;?>">
<INPUT TYPE="hidden" NAME="position" VALUE="0">

</FORM>
