<FORM ACTION="<?php print SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
        <?php echo (!empty($script->vacation)) ? SmartSieve::text('Edit vacation out-of-office message') : SmartSieve::text('Set vacation out-of-office message');?> 
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD CLASS="<?php echo (!empty($rule) && $rule['status'] == 'ENABLED') ? 'enabled' : 'disabled';?>">
        <?php echo (!empty($rule)) ? (($rule['status'] == 'on') ? SmartSieve::text('ENABLED') : SmartSieve::text('DISABLED')) : '&nbsp;';?> 
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
<?php echo SmartSieve::text('Vacation settings');?>:
      </TD>
      <TD CLASS="heading">&nbsp;</TD>
    </TR>
    <TR>
      <TD NOWRAP="nowrap">
<?php echo SmartSieve::text('Message');?>:
      </TD>
      <TD NOWRAP="nowrap">
        <INPUT TYPE="hidden" NAME="action0" VALUE="<?php echo ACTION_VACATION;?>">
        <TEXTAREA NAME="message0" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard" TABINDEX="1"><?php if (!empty($rule['actions'][0]['message'])) echo SmartSieve::utf8Decode($rule['actions'][0]['message']); ?></TEXTAREA>
      </TD>
    </TR>
    <TR>
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
    <TR>
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
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="options" COLSPAN="2">
        <BR>
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
