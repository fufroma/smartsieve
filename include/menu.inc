
<FORM ACTION="<?php print SmartSieve::setUrl('main.php');?>" METHOD="post" NAME="menu">

<TABLE WIDTH="100%" CELLPADDING="1" BORDER="0" CELLSPACING="0">
<TR CLASS="menuouter">
  <TD>
    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
      <TR CLASS="menu">
        <TD>
          &nbsp;
          <a href="<?php print SmartSieve::setUrl('main.php');?>"><?php echo SmartSieve::text('View All Rules');?></a>
<?php if ($_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']]->mode != 'advanced'): ?>
<?php if (SmartSieve::getConf('use_forward_mail_interface')):?>
        | <A HREF="<?php print SmartSieve::setUrl('rule.php?mode=forward');?>"><?php echo SmartSieve::text('Forward Mail');?></A>
<?php endif;?>
        | <a href="<?php print SmartSieve::setUrl('rule.php?mode=vacation');?>"><?php echo SmartSieve::text('Vacation Settings');?></a>
        | <a href="<?php print SmartSieve::setUrl('rule.php');?>"><?php echo SmartSieve::text('New Filter Rule');?></a>
<?php if (SmartSieve::getConf('spam_filter')):?>
        | <a href="<?php print SmartSieve::setUrl('rule.php?mode=spam');?>"><?php echo SmartSieve::text('Filter Spam');?></a>
<?php endif;?>
<?php endif;?>
<?php if (SmartSieve::getConf('allow_multi_scripts') !== false): ?>
        | <A HREF="<?php print SmartSieve::setUrl('scripts.php');?>"><?php echo SmartSieve::text('Manage Scripts');?></A>
<?php endif; //$default->allow_multi_scripts ?>
<?php foreach (SmartSieve::getConf('menu_items', array()) as $item):?>
        | <a href="<?php echo $item['uri'];?>"<?php echo (isset($item['target'])) ? sprintf(' target="%s"', $item['target']) : '';?>><?php echo $item['label'];?></a>
<?php endforeach;?>
<?php if (!empty($help_url)): ?>
        | <a href="<?php echo $help_url; ?>" target="_blank"><?php echo SmartSieve::text('Help');?></a>
<?php endif; //$help_url ?>
        </TD>
        <TD ALIGN="right">
          &nbsp;<?php echo SmartSieve::text('Logged in as "%s"', array($_SESSION['smartsieve']['authz']));?> 
          &nbsp;<a href="<?php echo SmartSieve::setUrl('login.php?reason=logout');?>"><?php echo SmartSieve::text('Logout');?></a>
        </TD>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

</FORM>

