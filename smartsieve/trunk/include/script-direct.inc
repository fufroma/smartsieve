<FORM ACTION="<?php echo SmartSieve::setUrl('main.php');?>" METHOD="post" NAME="rules">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD>

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="breadcrumb">
        &nbsp;<a href="<?php echo SmartSieve::setUrl('scripts.php');?>"><?php echo SmartSieve::text('Scripts');?>&nbsp;&gt;</a>
        &nbsp;<?php echo SmartSieve::text('Filter rules for "%s"', array($script->getName()));?>
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

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="heading"><?php echo SmartSieve::text('Mail Filter Rules');?>:</TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD>

    <TEXTAREA NAME="content" ROWS="30" COLS="70" WRAP="off"><?php echo $content;?></TEXTAREA>

  </TD>
</TR>
<TR>
  <TD>

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
      <TR>
        <TD CLASS="options">
          <input type="button" name="save" value="<?php echo SmartSieve::text('Save Changes');?>" onClick="document.rules.action.value='<?php echo FORM_ACTION_SAVE;?>'; document.rules.submit(); return false;">
        </TD>
<?php if (SmartSieve::getConf('allow_change_mode')): ?>
        <TD ALIGN="right" CLASS="options">
          <input type="button" name="changeMode" value="<?php echo SmartSieve::text('GUI mode');?>" onClick="ChangeMode(); return false;">
        </TD>
<?php endif; ?>
      </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="rindex" VALUE="" >

</FORM>
