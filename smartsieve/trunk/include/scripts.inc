<FORM ACTION="<?php echo SmartSieve::setUrl('scripts.php');?>" METHOD="post" NAME="slist">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD>

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="breadcrumb">
        &nbsp;<?php echo SmartSieve::text('Scripts');?>&nbsp;&gt;
      </TD>
    </TR>
    <TR CLASS="heading">
      <TD ALIGN="right">
        &nbsp;
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="heading"><?php echo SmartSieve::text('Sieve Scripts');?></TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
<?php if (!empty($slist)):?>
      <TR>
        <TH WIDTH="10%">&nbsp;</TH>
        <TH WIDTH="70%"><?php echo SmartSieve::text("Script");?></TH>
        <TH WIDTH="10%"><?php echo SmartSieve::text("Status");?></TH>
        <TH WIDTH="10%"><?php echo SmartSieve::text("Size");?></TH>
      </TR>
<?php foreach ($slist as $s => $active): ?>
    <TR CLASS="<?php echo ($active===true) ? 'active' : 'inactive';?>" onmouseover="this.className='<?php echo ($active===true) ? 'active' : 'inactive';?>-mover';" onmouseout="this.className='<?php echo ($active===true) ? 'active' : 'inactive';?>';">
      <TD>
        <INPUT TYPE="checkbox" NAME="scriptID[]" VALUE="<?php echo $i++;?>">
      </TD>
      <TD>
        <A CLASS="summary" HREF="" TITLE="<?php echo SmartSieve::text('Edit this script');?>" onclick="edit('<?php echo $s;?>'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Edit this script');?>'; return true;" onmouseout="window.status='';"><?php echo $s;?></A>
      </TD>
      <TD>
        <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/<?php echo ($active===true) ? 'tick.gif' : 'disable.gif';?>" BORDER="0">
        <?php echo ($active===true) ? SmartSieve::text('active') : SmartSieve::text('not active');?> 
      </TD>
      <TD>
        &nbsp;<?php echo (isset($scripts[$s])) ? $scripts[$s]->size . SmartSieve::text('bytes') : '';?> 
      </TD>
    </TR>
<?php endforeach; ?>
<?php else: ?>
    <TR CLASS="active">
      <TD COLSPAN="4">[<?php echo SmartSieve::text("No existing scripts");?>]</TD>
    </TR>
<?php endif; ?>
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

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
      <TR>
        <TD CLASS="options">
          <input type="button" name="activate" value="<?php echo SmartSieve::text('Activate');?>" onClick="setScriptActive(); return false;">
          <input type="button" name="deactivate" value="<?php echo SmartSieve::text('Deactivate');?>" onClick="Deactivate(); return false;">
          <input type="button" name="create" value="<?php echo SmartSieve::text('Create new script');?>" onClick="createScript(); return false;">
          <input type="button" name="delete" value="<?php echo SmartSieve::text('Delete');?>" onClick="deleteScript(); return false;">
          <input type="button" name="rename" value="<?php echo SmartSieve::text('Rename script');?>" onClick="renameScript(); return false;">
        </TD>
      </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="viewscript" VALUE="" >
<INPUT TYPE="hidden" NAME="newscript" VALUE="" >

</FORM>
