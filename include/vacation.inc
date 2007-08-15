<FORM ACTION="<?php echo SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="breadcrumb">
        &nbsp;<a href="<?php echo SmartSieve::setUrl('scripts.php');?>"><?php echo SmartSieve::text('Scripts');?>&nbsp;&gt;</a>
        &nbsp;<A HREF="<?php echo SmartSieve::setUrl('main.php');?>"><?php echo SmartSieve::text('Filter rules for "%s"', array($script->getName()));?>&nbsp;&gt;</A>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
        &nbsp;<?php echo SmartSieve::text('Edit vacation message');?>
<?php else: ?>
        &nbsp;<?php echo SmartSieve::text('Set vacation message');?>
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
              <TH><?php echo SmartSieve::text('Vacation settings');?></TH>
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
                <?php echo SmartSieve::text('Message');?>:
              </TD>
              <TD>
                <INPUT TYPE="hidden" NAME="action0" VALUE="<?php echo ACTION_VACATION;?>">
                <TEXTAREA NAME="message0" COLS="<?php echo $wrap_width;?>" WRAP="hard" TABINDEX="1"><?php if (!empty($rule['actions'][0]['message'])) echo SmartSieve::utf8Decode($rule['actions'][0]['message']); ?></TEXTAREA>
              </TD>
            </TR>
            <TR CLASS="active">
              <TD>
                <?php echo SmartSieve::text('Days between responses');?>:
              </TD>
              <TD>
                <SELECT NAME="days0">
<?php for ($j=1; $j <= SmartSieve::getConf('max_vacation_days', 30); $j++):?>
                  <OPTION VALUE="<?php echo $j;?>"<?php echo ((empty($rule['actions'][0]['days']) && SmartSieve::getConf('vacation_days', 7) == $j) || $rule['actions'][0]['days']==$j) ? ' SELECTED' : '';?>><?php echo $j;?></OPTION>
<?php endfor;?>
                </SELECT>
              </TD>
            </TR>
            <TR CLASS="active">
              <TD>
                <?php echo SmartSieve::text('Your e-mail addresses');?>:
              </TD>
              <TD>
                <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
<?php foreach (getAllAddresses($rule['actions'][0]['addresses']) as $addr=>$checked):?>
                  <TR>
                    <TD>
                      <INPUT TYPE="checkbox" NAME="address0[]" VALUE="<?php echo $addr;?>"<?php echo ($checked === true) ? ' CHECKED="checked"' : '';?>>&nbsp;<?php echo $addr;?>
                    </TD>
                  </TR>
<?php endforeach;?>
                  <TR>
                    <TD>
                      <INPUT TYPE="text" NAME="newaddresses0" VALUE="" SIZE="50">
                    </TD>
                  </TR>
                </TABLE>
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
<?php if (!empty($rule)):?>
            |
          <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Enable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Enable');?></a>
            |
          <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Disable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Disable');?></a>
<?php endif;?>
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
<INPUT TYPE="hidden" NAME="special" VALUE="<?php echo RULE_TAG_VACATION;?>">

</FORM>
