<FORM ACTION="<?php print SmartSieve::setUrl('main.php');?>" METHOD="post" NAME="rules">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
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
  <TD CLASS="main">

    <TEXTAREA NAME="text" ROWS="30" COLS="70" WRAP="off">
<?php echo $stext; ?>
</TEXTAREA>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
    <BR>
      <TD CLASS="options">
        <A CLASS="option" HREF="" onclick="Save(); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Save Changes');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Save Changes');?></a>
      </TD>
<?php if (SmartSieve::getConf('allow_change_mode')): ?>
      <TD ALIGN="right" CLASS="options">
        <A CLASS="option" HREF="" onclick="ChangeMode(); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Switch to GUI mode');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('GUI mode');?></A>
      </TD>
<?php endif; ?>
    </BR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="rindex" VALUE="" >

</FORM>

</BODY>
</HTML>

