<FORM ACTION="<?php print AppSession::setUrl('scripts.php');?>" METHOD="post" NAME="scripts">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="heading">Sieve Scripts:</TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
<?php

if ($sieve->scriptlist){ ?>
      <TR>
        <TH WIDTH="10%">&nbsp;</TH>
        <TH WIDTH="70%">Script</TH>
        <TH WIDTH="10%">Status</TH>
        <TH WIDTH="10%">Size</TH>
      </TR>
<?php

    $i = 0;
    foreach ($sieve->scriptlist as $script){
        $class = 'inactivescript';
        $eclass = 'inactive';
        $status='NOT ACTIVE';
        if (AppSession::isActiveScript($script)) {
            $class = 'activescript';
            $eclass = 'active';
            $status = 'ACTIVE';
        }
        $size = '';
        if (isset($scripts[$script]))
            $size = $scripts[$script]->size . 'bytes';
?>
    <TR CLASS="<?php echo $class;?>">
      <TD>
        <INPUT TYPE="checkbox" NAME="scriptID[]" VALUE="<?php print $i; ?>">
      </TD>
      <TD>
        <A CLASS="rule" HREF="" onclick="viewScript('<?php echo $script; ?>'); return false;" onmouseover="window.status='View This Script'; return true;" onmouseout="window.status='';"><?php echo $script; ?></A>
      </TD>
      <TD CLASS="<?php echo $eclass; ?>">
        <?php echo $status; ?>
      </TD>
      <TD>
        &nbsp;<?php echo $size; ?>
      </TD>
    </TR>
<?php
        $i++;
    }
}
else { ?>
    <TR CLASS="enabledrule">
      <TD COLSPAN="4">[No existing scripts]</TD>
    </TR>
<?php
}
?>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
    <BR>
      <TD CLASS="options">
        <A CLASS="option" HREF="" onclick="setScriptActive(); return false;" onmouseover="window.status='Activate Script'; return true;" onmouseout="window.status='';">Activate</a>
         |
        <A CLASS="option" HREF="" onclick="deactivate(); return false;" onmouseover="window.status='Deactivate All'; return true;" onmouseout="window.status='';">Deactivate</a>
         |
        <A CLASS="option" HREF="" onclick="createScript(); return false;" onmouseover="window.status='Create New Script'; return true;" onmouseout="window.status='';">Create</a>
         |
        <A CLASS="option" HREF="" onclick="deleteScript(); return false;" onmouseover="window.status='Delete Script'; return true;" onmouseout="window.status='';">Delete</a>
         |
        <A CLASS="option" HREF="" onclick="renameScript(); return false;" onmouseover="window.status='Rename Script'; return true;" onmouseout="window.status='';">Rename</A>
      </TD>
    </BR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="viewscript" VALUE="" >
<INPUT TYPE="hidden" NAME="newscript" VALUE="" >

</FORM>

</BODY>
</HTML>

