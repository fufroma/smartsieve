<FORM ACTION="<?php echo SmartSieve::setUrl('rule.php');?>" METHOD="post" NAME="thisRule">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD>

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="breadcrumb">
        &nbsp;<a href="<?php echo SmartSieve::setUrl('scripts.php');?>"><?php echo SmartSieve::text('Scripts');?>&nbsp;&gt;</a>
        &nbsp;<A HREF="<?php echo SmartSieve::setUrl('main.php');?>"><?php echo SmartSieve::text('Filter rules for "%s"', array($script->getName()));?>&nbsp;&gt;</A>
        &nbsp;<?php echo SmartSieve::text('Filter spam');?>
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
        <?php echo SmartSieve::text('What do you want to do with spam?');?> 
      </TD>
<?php if (isset($ruleID) && $ruleID !== 'new'): ?>
      <TD ALIGN="right">
        &nbsp;<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('This rule is enabled') : SmartSieve::text('This rule is disabled');?>
        &nbsp;<IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images'); echo ($script->isRuleEnabled($ruleID)) ? '/tick.gif' : '/disable.gif';?>" BORDER="0">
      </TD>
<?php else: ?>
      <TD>
        &nbsp;
      </TD>
<?php endif; ?>
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
              <TH><?php echo SmartSieve::text("ACTIONS");?></TH>
              <TH>&nbsp;</TH>
            </TR>
          </TABLE>
        </TD>
      </TR>
      <TR>
        <TD>
          <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
<?php $i = 0; foreach ($rule['actions'] as $action):?>
            <TR CLASS="active">
              <TD>
                <SELECT NAME="action<?php echo $i;?>" onchange="document.thisRule.submit(); return true;">
<?php if ($action['type'] == 'new'):?>
                  <OPTION VALUE="new" SELECTED="selected">Add action</OPTION>
<?php endif;?>
                  <OPTION VALUE="<?php echo ACTION_FILEINTO;?>"<?php echo ($action['type'] == ACTION_FILEINTO) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("File Into");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_REDIRECT;?>"<?php echo ($action['type'] == ACTION_REDIRECT) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Forward to address");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_REJECT;?>"<?php echo ($action['type'] == ACTION_REJECT) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Send a reject message");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_DISCARD;?>"<?php echo ($action['type'] == ACTION_DISCARD) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Discard the message");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_VACATION;?>"<?php echo ($action['type'] == ACTION_VACATION) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Send vacation message');?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_KEEP;?>"<?php echo ($action['type'] == ACTION_KEEP) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Keep a copy in your Inbox');?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_STOP;?>"<?php echo ($action['type'] == ACTION_STOP) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Stop processing filter rules');?></OPTION>
                </SELECT>
              </TD>
              <TD>
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
<?php elseif ($action['type'] == ACTION_VACATION):?>
                <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
                  <TR>
                    <TD>
                      &nbsp;
                    </TD>
                    <TD>
                      <TEXTAREA NAME="message<?php echo $i;?>" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php echo (!empty($action['message'])) ? SmartSieve::utf8Decode($action['message']) : '';?></TEXTAREA>
                    </TD>
                  </TR>
                    <TR>
                    <TD>
                      <?php echo SmartSieve::text('Days between responses');?>
                    </TD>
                    <TD>
                      <SELECT NAME="days<?php echo $i;?>">
<?php for ($j=1; $j <= SmartSieve::getConf('max_vacation_days', 30); $j++):?>
                        <OPTION VALUE="<?php echo $j;?>"<?php echo ((empty($action['days']) && SmartSieve::getConf('vacation_days', 7) == $j) || $action['days']==$j) ? ' SELECTED' : '';?>><?php echo $j;?></OPTION>
<?php endfor;?>
                      </SELECT>
                    </TD>
                  </TR>
                  <TR>
                    <TD>
                      <?php echo SmartSieve::text('Your e-mail addresses');?>
                    </TD>
                    <TD>
                      <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
<?php foreach (getAllAddresses($action['addresses']) as $addr=>$checked):?>
                        <TR>
                          <TD>
                            <INPUT TYPE="checkbox" NAME="address<?php echo $i;?>[]" VALUE="<?php echo $addr;?>"<?php echo ($checked === true) ? ' CHECKED="checked"' : '';?>>&nbsp;<?php echo $addr;?>
                          </TD>
                        </TR>
<?php endforeach;?>
                        <TR>
                          <TD>
                            <INPUT TYPE="text" NAME="newaddresses<?php echo $i;?>" VALUE="" SIZE="50">
                          </TD>
                        </TR>
                      </TABLE>
                    </TD>
                  </TR>
                </TABLE>
<?php elseif ($action['type'] == ACTION_DISCARD || $action['type'] == ACTION_KEEP || $action['type'] == ACTION_STOP):?>
                &nbsp;
<?php endif;?>
              </TD>
            </TR>
<?php $i++; endforeach;?>
<?php if (!isset($ruleID) || $ruleID == 'new') :?>
            <TR CLASS="active">
              <TD>
                <?php echo SmartSieve::text('Save at position');?>
                <select name="position">
<?php for ($i=0; $i<count($script->rules); $i++):?>
                  <option value="<?php echo $i;?>"<?php echo ($i === 0) ? ' selected="selected"' : '';?>><?php echo $i+1;?></option>
<?php endfor;?>
                  <option value="<?php echo count($script->rules);?>"><?php echo count($script->rules)+1;?></option>
                </select>
              </TD>
              <TD>
                &nbsp;
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


<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($rule['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">
<INPUT TYPE="hidden" NAME="anyof" VALUE="0">
<INPUT TYPE="hidden" NAME="condition0" VALUE="header">
<INPUT TYPE="hidden" NAME="header0" VALUE="<?php echo $rule['conditions'][0]['header'];?>">
<INPUT TYPE="hidden" NAME="headerMatchStr0" VALUE="<?php echo $rule['conditions'][0]['matchStr'];?>">
<?php if ($rule['conditions'][0]['matchType'] == MATCH_IS):?>
<INPUT TYPE="hidden" NAME="matchType0" VALUE="<?php echo (empty($rule['conditions'][0]['not'])) ? 'is' : 'notis';?>">
<?php elseif ($rule['conditions'][0]['matchType'] == MATCH_CONTAINS):?>
<INPUT TYPE="hidden" NAME="matchType0" VALUE="<?php echo (empty($rule['conditions'][0]['not'])) ? 'contains' : 'notcontains';?>">
<?php elseif ($rule['conditions'][0]['matchType'] == MATCH_MATCHES):?>
<INPUT TYPE="hidden" NAME="matchType0" VALUE="<?php echo (empty($rule['conditions'][0]['not'])) ? 'matches' : 'notmatches';?>">
<?php elseif ($rule['conditions'][0]['matchType'] == MATCH_REGEX):?>
<INPUT TYPE="hidden" NAME="matchType0" VALUE="<?php echo (empty($rule['conditions'][0]['not'])) ? 'regex' : 'notregex';?>">
<INPUT TYPE="hidden" NAME="special" VALUE="<?php echo RULE_TAG_SPAM;?>">
<?php endif;?>

</FORM>