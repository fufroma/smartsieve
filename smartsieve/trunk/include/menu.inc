
<FORM ACTION="<?php print SmartSieve::setUrl('main.php');?>" METHOD="post" NAME="menu">

<TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="menuouter">
    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
      <TR>
        <TD CLASS="menu">
          &nbsp;
          <a href="<?php print SmartSieve::setUrl('login.php?reason=logout');?>"><?php echo SmartSieve::text('Logout');?></a>
        | <a href="<?php print SmartSieve::setUrl('main.php');?>"><?php echo SmartSieve::text('View All Rules');?></a>
<?php if ($_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']]->mode != 'advanced'): ?>
<?php     if (SmartSieve::getConf('use_forward_mail_interface')): ?>
        | <A HREF="<?php print SmartSieve::setUrl('forward.php');?>"><?php echo SmartSieve::text('Forward Mail');?></A>
<?php     endif; //use_forward_mail_interface ?>
        | <a href="<?php print SmartSieve::setUrl('vacation.php');?>"><?php echo SmartSieve::text('Vacation Settings');?></a>
        | <a href="<?php print SmartSieve::setUrl('rule.php');?>"><?php echo SmartSieve::text('New Filter Rule');?></a>
<?php endif; //$script->mode = 'advanced' ?>
<?php if (SmartSieve::getConf('allow_multi_scripts') !== false): ?>
        | <A HREF="<?php print SmartSieve::setUrl('scripts.php');?>"><?php echo SmartSieve::text('Manage Scripts');?></A>
<?php endif; //$default->allow_multi_scripts ?>
<?php if (!empty($help_url)): ?>
        | <a href="<?php echo $help_url; ?>"><?php echo SmartSieve::text('Help');?></a>
<?php endif; //$help_url ?>

        </TD>
<?php if (SmartSieve::getConf('allow_multi_scripts') !== false): ?>
        <TD CLASS="menu" ALIGN="right">
          &nbsp;
          <SELECT NAME="script" onchange="document.menu.submit();">
<?php     foreach (SmartSieve::getScriptList() as $s => $active): ?>
              <OPTION VALUE="<?php echo $s; ?>"<?php if ($s == $_SESSION['smartsieve']['workingScript']): ?> SELECTED="selected"<?php endif; ?>><?php echo $s;?></OPTION>
<?php     endforeach; ?>
          </SELECT>
        </TD>
<?php endif; //$default->allow_multi_scripts ?>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

</FORM>
