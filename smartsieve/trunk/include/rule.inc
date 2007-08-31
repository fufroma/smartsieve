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
        <?php echo SmartSieve::text('Edit filter rule');?> 
      </TD>
      <TD ALIGN="right">
        &nbsp;<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('This rule is enabled') : SmartSieve::text('This rule is disabled');?> 
        &nbsp;<a href="" onclick="document.thisRule.thisAction.value='<?php echo ($script->isRuleEnabled($ruleID)) ? FORM_ACTION_DISABLE : FORM_ACTION_ENABLE;?>'; document.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images'); echo ($script->isRuleEnabled($ruleID)) ? '/tick.gif' : '/disable.gif';?>" title="<?php echo ($script->isRuleEnabled($ruleID)) ? SmartSieve::text('Disable this rule') : SmartSieve::text('Enable this rule');?>" BORDER="0"></a>
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
          <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
            <TR>
              <TH><?php echo SmartSieve::text('Conditions');?></TH>
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
                &nbsp;
                <SELECT NAME="anyof">
                  <OPTION VALUE="0"<?php echo (empty($rule['matchAny'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('all of');?></OPTION>
                  <OPTION VALUE="<?php echo ANYOF_BIT;?>"<?php echo (!empty($rule['matchAny'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('any of');?></OPTION>
                </SELECT>
                &nbsp;<?php echo SmartSieve::text('the following matches');?> 
              </TD>
              <TD>
                &nbsp;
              </TD>
            </TR>
<?php $i = 0; foreach ($rule['conditions'] as $condition):?>
            <TR CLASS="active">
              <TD>
                &nbsp;
                <SELECT NAME="condition<?php echo $i;?>" onchange="document.thisRule.submit();return true;">
<?php if ($condition['type'] == 'new'):?>
                  <OPTION VALUE="new" SELECTED="selected"><?php echo SmartSieve::text('Add condition');?></OPTION>
<?php endif;?>
                  <OPTION VALUE="from"<?php echo ($condition['type'] == TEST_ADDRESS && $condition['header'] == 'from') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'From:'");?></OPTION>
                  <OPTION VALUE="to"<?php echo ($condition['type'] == TEST_ADDRESS && $condition['header'] == 'to') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'To:'");?></OPTION>
                  <OPTION VALUE="tocc"<?php echo ($condition['type'] == TEST_ADDRESS && $condition['header'] == array('to', 'cc')) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'To:' or 'Cc:'");?></OPTION>
                  <OPTION VALUE="subject"<?php echo ($condition['type'] == TEST_HEADER && $condition['header'] == 'subject') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message 'Subject:'");?></OPTION>
                  <OPTION VALUE="size"<?php echo ($condition['type'] == TEST_SIZE) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message size is");?></OPTION>
                  <OPTION VALUE="header"<?php echo ($condition['type'] == TEST_HEADER && $condition['header'] !== 'subject') ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If mail header");?></OPTION>
<?php if ($managesieve->hasExtension('body')):?>
                  <OPTION VALUE="body"<?php echo ($condition['type'] == TEST_BODY) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("If message body");?></OPTION>
<?php endif;?>
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
                &nbsp;<A HREF="" onclick="document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>" TITLE="<?php echo SmartSieve::text('Delete condition');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';"></a>
<?php elseif ($condition['type'] == TEST_SIZE):?>
                <SELECT NAME="gthan<?php echo $i;?>">
                  <OPTION VALUE="0"<?php echo (empty($condition['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("less than");?></OPTION>
                  <OPTION VALUE="<?php echo SIZE_BIT;?>"<?php echo (!empty($condition['gthan'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("greater than");?></OPTION>
                </SELECT>
                <INPUT TYPE="text" NAME="size<?php echo $i;?>" VALUE="<?php echo $condition['kbytes'];?>" SIZE="5"> <?php echo SmartSieve::text("KiloBytes");?> 
                <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
                &nbsp;<A HREF="" onclick="document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>" TITLE="<?php echo SmartSieve::text('Delete condition');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';"></a>
<?php elseif ($condition['type'] == TEST_BODY):?>
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
                <INPUT TYPE="text" NAME="bodyMatchStr<?php echo $i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['matchStr']);?>" SIZE="50">
                <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
                &nbsp;<A HREF="" onclick="document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>" TITLE="<?php echo SmartSieve::text('Delete condition');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';"></a>
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
                <INPUT TYPE="text" NAME="matchStr<?php echo $i;?>" VALUE="<?php echo SmartSieve::utf8Decode($condition['matchStr']);?>" SIZE="50">
                <INPUT TYPE="hidden" NAME="delete<?php echo $i;?>" VALUE="">
                &nbsp;<A HREF="" onclick="document.forms.thisRule.delete<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete condition');?>" TITLE="<?php echo SmartSieve::text('Delete condition');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete condition');?>'; return true;" onmouseout="window.status='';"></a>
<?php endif;?>
              </TD>
            </TR>
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
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
      <TR>
        <TD>
          <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
            <TR>
              <TH><?php echo SmartSieve::text('Actions');?></TH>
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
                  <OPTION VALUE="new" SELECTED="selected"><?php echo SmartSieve::text('Add action');?></OPTION>
<?php endif;?>
                  <OPTION VALUE="<?php echo ACTION_FILEINTO;?>"<?php echo ($action['type'] == ACTION_FILEINTO) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("File Into");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_REDIRECT;?>"<?php echo ($action['type'] == ACTION_REDIRECT) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Forward to address");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_REJECT;?>"<?php echo ($action['type'] == ACTION_REJECT) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Send a reject message");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_DISCARD;?>"<?php echo ($action['type'] == ACTION_DISCARD) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text("Discard the message");?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_VACATION;?>"<?php echo ($action['type'] == ACTION_VACATION) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Send vacation message');?></OPTION>
<?php if ($GLOBALS['managesieve']->hasExtension('imapflags') && !empty($imapFlags)):?>
                  <OPTION VALUE="<?php echo ACTION_ADDFLAG;?>"<?php echo ($action['type'] == ACTION_ADDFLAG) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Set message flag');?></OPTION>
<?php endif;?>
<?php if ($GLOBALS['managesieve']->hasExtension('notify') && !empty($notifyMethods)):?>
                  <OPTION VALUE="<?php echo ACTION_NOTIFY;?>"<?php echo ($action['type'] == ACTION_NOTIFY) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Send notification message');?></OPTION>
<?php endif;?>
                  <OPTION VALUE="<?php echo ACTION_KEEP;?>"<?php echo ($action['type'] == ACTION_KEEP) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Keep a copy in your Inbox');?></OPTION>
                  <OPTION VALUE="<?php echo ACTION_STOP;?>"<?php echo ($action['type'] == ACTION_STOP) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('Stop processing filter rules');?></OPTION>
                </SELECT>
              </TD>
              <TD>
<?php if ($action['type'] == ACTION_FILEINTO):?>
<?php if ($_SESSION['smartsieve']['auth'] == $_SESSION['smartsieve']['authz']):?>
                <SELECT NAME="<?php echo ACTION_FILEINTO . $i;?>">
<?php foreach ($mailboxes as $mbox): ?>
                  <OPTION VALUE="<?php echo $mbox;?>"<?php echo ($action['folder'] == $mbox) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::mutf7Decode($mbox);?></OPTION>
<?php endforeach; ?>
<?php if (!empty($action['folder']) && !in_array($action['folder'], $mailboxes)):?>
                  <OPTION VALUE="<?php echo $action['folder'];?>" SELECTED="selected"><?php echo SmartSieve::mutf7Decode($action['folder']);?></OPTION>
<?php endif;?>
                </SELECT>
<?php else:?>
                <INPUT TYPE="text" NAME="<?php echo ACTION_FILEINTO . $i;?>" VALUE="<?php echo $action['folder'];?>">
<?php endif;?>
<?php elseif ($action['type'] == ACTION_REDIRECT):?>
                <INPUT TYPE="text" NAME="<?php echo ACTION_REDIRECT . $i;?>" VALUE="<?php echo (!empty($action['address'])) ? SmartSieve::utf8Decode($action['address']) : '';?>" SIZE="40">
<?php elseif ($action['type'] == ACTION_REJECT):?>
                <TEXTAREA NAME="<?php echo ACTION_REJECT . $i;?>" ROWS="10" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php echo SmartSieve::utf8Decode($action['message']);?></TEXTAREA>
<?php elseif ($action['type'] == ACTION_VACATION):?>
                <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
                  <TR>
                    <TD>
                      &nbsp;
                    </TD>
                    <TD>
                      <TEXTAREA NAME="message<?php echo $i;?>" ROWS="10" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php echo (!empty($action['message'])) ? SmartSieve::utf8Decode($action['message']) : '';?></TEXTAREA>
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
<?php elseif ($action['type'] == ACTION_ADDFLAG):?>
                <SELECT NAME="<?php echo ACTION_ADDFLAG . $i;?>">
<?php foreach ($imapFlags as $flag): ?>
                  <OPTION VALUE="<?php echo $flag;?>"<?php echo ($action['flag'] == $flag) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::utf8Decode($flag);?></OPTION>
<?php endforeach; ?>
                </SELECT>
<?php elseif ($action['type'] == ACTION_NOTIFY):?>
                <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
                  <TR>
                    <TD>
                      <SELECT NAME="notify_method<?php echo $i;?>" onchange="document.thisRule.submit(); return true;">
<?php if (in_array('mailto', $notifyMethods)):?>
                        <OPTION VALUE="<?php echo 'mailto';?>"<?php echo ($action['method'] == 'mailto' || empty($action['method'])) ? ' SELECTED="selected"' : '';?>><?php echo SmartSieve::text('by email');?></OPTION>
<?php endif;?>
<?php if (in_array('sms', $notifyMethods)):?>
                        <OPTION VALUE="<?php echo 'sms';?>"<?php echo ($action['method'] == 'sms') ? ' SELECTED' : '';?>><?php echo SmartSieve::text('by SMS');?></OPTION>
<?php endif;?>
                      </SELECT>
<?php if ($action['method'] == 'mailto' || empty($action['method'])):?>
                      &nbsp;<?php echo SmartSieve::text('to email address');?> 
                      &nbsp;<input type="text" name="notify_options<?php echo $i;?>" value="<?php echo SmartSieve::utf8Decode($action['options']);?>" size="50">
<?php elseif ($action['method'] == 'sms'):?>
                      &nbsp;<?php echo SmartSieve::text('to number');?> 
                      &nbsp;<input type="text" name="notify_options<?php echo $i;?>" value="<?php echo SmartSieve::utf8Decode($action['options']);?>" size="50">
<?php else:?>
                      &nbsp;
<?php endif;?>
                    </TD>
                  </TR>
                  <TR>
                    <TD>
                      <TEXTAREA NAME="message<?php echo $i;?>" ROWS="10" COLS="<?php echo $wrap_width;?>" WRAP="hard"><?php echo (!empty($action['message'])) ? SmartSieve::utf8Decode($action['message']) : '';?></TEXTAREA>
                    </TD>
                  </TR>
                </TABLE>
<?php elseif ($action['type'] == ACTION_DISCARD || $action['type'] == ACTION_KEEP || $action['type'] == ACTION_STOP):?>
                &nbsp;
<?php endif;?>
<?php if ($action['type'] != 'new'):?>
                <INPUT TYPE="hidden" NAME="deleteAction<?php echo $i;?>" VALUE="">
                &nbsp;<A HREF="" onclick="document.forms.thisRule.deleteAction<?php echo $i;?>.value='1'; document.forms.thisRule.submit(); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete action');?>" TITLE="<?php echo SmartSieve::text('Delete action');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Delete action');?>'; return true;" onmouseout="window.status='';"></a>
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
                  <option value="<?php echo $i;?>"<?php echo ($ruleID === $i) ? ' selected="selected"' : '';?>><?php echo $i+1;?></option>
<?php endfor;?>
                  <option value="<?php echo count($script->rules);?>"<?php echo (!isset($ruleID) || $ruleID == 'new') ? ' selected="selected"' : '';?>><?php echo count($script->rules)+1;?></option>
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


<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo ($rule['status'] == 'ENABLED') ? 'ENABLED' : 'DISABLED';?>">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="ruleID" VALUE="<?php echo (isset($ruleID)) ? $ruleID : 'new';?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="<?php echo $mode;?>">

</FORM>
