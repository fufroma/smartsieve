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
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
<?php if (!empty($script->rules)):?>
      <TR>
        <TH WIDTH="3%">&nbsp;</TH>
        <TH WIDTH="3%">&nbsp;</TH>
        <TH WIDTH="5%"><?php echo SmartSieve::text('Status');?></TH>
        <TH WIDTH="82%"><?php echo SmartSieve::text('Rule');?></TH>
        <TH WIDTH="7%"><?php echo SmartSieve::text('Order');?></TH>
      </TR>
<?php foreach ($rows as $tr):?>
    <TR CLASS="<?php echo $tr['class'];?>" onmouseover="this.className='<?php echo $tr['class'];?>-mover';" onmouseout="this.className='<?php echo $tr['class'];?>';">
      <TD>
        <INPUT TYPE="checkbox" class="checkbox" NAME="ruleID[]" VALUE="<?php echo $tr['id'];?>">
      </TD>
      <TD>
        <?php echo $tr['position'];?> 
      </TD>
      <TD>
        <A HREF="" onclick="var ruleIDs=document.getElementsByName('ruleID[]'); ruleIDs[<?php echo $tr['id'];?>].checked=true; document.rules.action.value='<?php echo $tr['statusChangeAction'];?>'; document.rules.submit(); return false;"><IMG SRC="<?php echo $tr['statusImage'];?>" ALT="<?php echo $tr['status'];?>" TITLE="<?php echo $tr['statusChangeText'];?>" BORDER="0"></a>
        <A HREF="" onclick="deleteRule(<?php echo $tr['id'];?>); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/delete.gif" ALT="<?php echo SmartSieve::text('Delete');?>" TITLE="<?php echo SmartSieve::text('Delete this rule');?>" BORDER="0"></a>
      </TD>
      <TD>
        <A CLASS="summary" HREF="<?php echo $tr['link'];?>" TITLE="<?php echo SmartSieve::text('Edit this rule');?>" onmouseover="window.status='<?php echo SmartSieve::text('Edit this rule');?>'; return true;" onmouseout="window.status='';"><?php echo $tr['summary'];?></A>
      </TD>
      <TD NOWRAP="nowrap">
        <A HREF="" onclick="ChangeOrder(<?php echo $tr['id'];?>, <?php echo $tr['id'];?>); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/up.gif" ALT="Move rule up" TITLE="<?php echo SmartSieve::text('Move rule up');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Move rule up');?>'; return true;" onmouseout="window.status='';"></A>
        <A HREF="" onclick="ChangeOrder(<?php echo $tr['id'];?>, <?php echo $tr['id']+2;?>); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/down.gif" ALT="Move rule down" TITLE="<?php echo SmartSieve::text('Move rule down');?>" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Move rule down');?>'; return true;" onmouseout="window.status='';"></A>
        <?php echo SmartSieve::text('To');?>:<INPUT TYPE="text" NAME="toPosition<?php echo $tr['id'];?>" VALUE="" SIZE="2" CLASS="position" TITLE="<?php echo SmartSieve::text('Move rule position');?>" onChange="ChangeOrder(<?php echo $tr['id'];?>, document.forms.rules.toPosition<?php echo $tr['id'];?>.value); return false;">
      </TD>
    </TR>
<?php endforeach;?>
<?php else:?>
    <TR CLASS="active">
      <TD COLSPAN="4">[<?php echo SmartSieve::text('No rules found');?>]</TD>
    </TR>
<?php endif;?>
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
      </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="rindex" VALUE="" >
<INPUT TYPE="hidden" NAME="toPosition" VALUE="" >

</FORM>
