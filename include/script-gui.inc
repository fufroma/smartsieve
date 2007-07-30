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

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
<?php if (!empty($script->rules)):?>
      <TR>
        <TH WIDTH="3%">&nbsp;</TH>
        <TH WIDTH="2%">&nbsp;</TH>
        <TH WIDTH="10%"><?php echo SmartSieve::text('Status');?></TH>
        <TH WIDTH="80%"><?php echo SmartSieve::text('Rule');?></TH>
        <TH WIDTH="5%"><?php echo SmartSieve::text('Order');?></TH>
      </TR>
<?php foreach ($rows as $tr):?>
    <TR CLASS="<?php echo $tr['class']; ?>" onmouseover="javascript:style.backgroundColor='<?php echo $tr['onmouseover'];?>'" onmouseout="javascript:style.backgroundColor='<?php echo $tr['onmouseout'];?>'">
      <TD>
        <INPUT TYPE="checkbox" NAME="ruleID[]" VALUE="<?php echo $tr['id'];?>">
      </TD>
      <TD>
        <?php echo $tr['position'];?> 
      </TD>
      <TD CLASS="<?php echo $tr['eclass'];?>">
        <?php echo $tr['status'];?> 
      </TD>
      <TD>
        <A CLASS="rule" HREF="<?php echo $tr['link'];?>" onmouseover="window.status='<?php echo SmartSieve::text('Edit this rule');?>'; return true;" onmouseout="window.status='';"><?php echo $tr['summary'];?></A>
      </TD>
      <TD NOWRAP="nowrap">
        <A HREF="" onclick="ChangeOrder(<?php echo $tr['id'];?>, <?php echo $tr['id'];?>); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/up.gif" ALT="Move rule up" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Move rule up');?>'; return true;" onmouseout="window.status='';"></A>
        <A HREF="" onclick="ChangeOrder(<?php echo $tr['id'];?>, <?php echo $tr['id']+2;?>); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/down.gif" ALT="Move rule down" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Move rule down');?>'; return true;" onmouseout="window.status='';"></A>
        <?php echo SmartSieve::text('To');?>:<INPUT TYPE="text" NAME="toPosition<?php echo $tr['id'];?>" VALUE="" SIZE="2" onChange="ChangeOrder(<?php echo $tr['id'];?>, document.forms.rules.toPosition<?php echo $tr['id'];?>.value); return false;">
      </TD>
    </TR>
<?php endforeach;?>
<?php else:?>
    <TR CLASS="enabledrule">
      <TD COLSPAN="4">[<?php echo SmartSieve::text('No rules found');?>]</TD>
    </TR>
<?php endif;?>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
    <BR>
      <TD CLASS="options">
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Enable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Enable');?></a>
         |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Disable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Disable');?></a>
         |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Delete');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Delete');?></a>
      </TD>
<?php if (SmartSieve::getConf('allow_change_mode')): ?>
      <TD ALIGN="right" CLASS="options">
        <A CLASS="option" HREF="" onclick="ChangeMode(); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Switch to direct edit mode');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Direct edit mode');?></A>
      </TD>
<?php endif; ?>
<?php if (SmartSieve::getConf('allow_view_source', true) !== false): ?>
      <TD ALIGN="right" CLASS="options">
        <A CLASS="option" HREF="" onclick="window.open('<?php echo SmartSieve::setUrl('main.php?action=viewSource');?>','','menubar=yes,toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('View script source');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('View Source');?></A>
      </TD>
<?php endif; ?>
    </BR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="rindex" VALUE="" >
<INPUT TYPE="hidden" NAME="toPosition" VALUE="" >

</FORM>
