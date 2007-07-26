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
        <TH WIDTH="5%">&nbsp;</TH>
        <TH WIDTH="10%"><?php echo SmartSieve::text('Status');?></TH>
        <TH WIDTH="80%"><?php echo SmartSieve::text('Rule');?></TH>
        <TH WIDTH="5%"><?php echo SmartSieve::text('Order');?></TH>
      </TR>
<?php foreach ($rows as $tr):?>
    <TR CLASS="<?php echo $tr['class']; ?>" onmouseover="javascript:style.backgroundColor='<?php echo $tr['onmouseover'];?>'" onmouseout="javascript:style.backgroundColor='<?php echo $tr['onmouseout'];?>'">
      <TD>
        <INPUT TYPE="checkbox" NAME="ruleID[]" VALUE="<?php echo $tr['id'];?>">
      </TD>
      <TD CLASS="<?php echo $tr['eclass'];?>">
        <?php echo $tr['status'];?> 
      </TD>
      <TD>
        <A CLASS="rule" HREF="<?php echo SmartSieve::setUrl("rule.php?ruleID=" . $tr['id']); ?>" onmouseover="window.status='<?php echo SmartSieve::text('Edit this rule');?>'; return true;" onmouseout="window.status='';"><?php echo $tr['summary'];?></A>
      </TD>
      <TD NOWRAP="nowrap">
        <A HREF="" onclick="ChangeOrder('increase',<?php echo $tr['id'];?>); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/up.gif" ALT="Move rule up" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Move rule up');?>'; return true;" onmouseout="window.status='';"></A>
        <A HREF="" onclick="ChangeOrder('decrease',<?php echo $tr['id'];?>); return false;"><IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/down.gif" ALT="Move rule down" BORDER="0" onmouseover="window.status='<?php echo SmartSieve::text('Move rule down');?>'; return true;" onmouseout="window.status='';"></A>
      </TD>
    </TR>
<?php endforeach;?>
<?php else:?>
    <TR CLASS="enabledrule">
      <TD COLSPAN="4">[<?php echo SmartSieve::text('No rules found');?>]</TD>
    </TR>
<?php endif;?>

<?php if (!empty($script->vacation)):?>
    <TR>
      <TD CLASS="heading" COLSPAN="4"><?php echo SmartSieve::text('Vacation Message Settings');?>:</TD>
    </TR>
    <TR CLASS="<?php echo $vRow['class'];?>" onmouseover="javascript:style.backgroundColor='<?php echo $vRow['onmouseover'];?>'" onmouseout="javascript:style.backgroundColor='<?php echo $vRow['onmouseout'];?>'">
      <TD>
        <INPUT TYPE="checkbox" NAME="ruleID[]" VALUE="vacation">
      </TD>
      <TD CLASS="<?php echo $vRow['eclass'];?>">
        <?php echo $vRow['status'];?> 
      </TD>
      <TD COLSPAN="2">
        <A CLASS="rule" HREF="<?php echo SmartSieve::setUrl('rule.php?mode=vacation'); ?>" onmouseover="window.status='<?php echo SmartSieve::text('Edit vacation settings');?>'; return true;" onmouseout="window.status='';"><?php echo $vRow['summary'];?></A>
      </TD>
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
        <A CLASS="option" HREF="" onclick="window.open('<?php echo SmartSieve::setUrl('main.php?action=view_source');?>','','menubar=yes,toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('View script source');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('View Source');?></A>
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
