
<FORM ACTION="<?php echo SmartSieve::setUrl('main.php');?>" METHOD="post" NAME="menu">

<TABLE WIDTH="100%" CELLPADDING="1" BORDER="0" CELLSPACING="0">
<TR CLASS="menuouter">
  <TD>
    <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
      <TR CLASS="menu">
        <TD>
          <div id="menu">
          <ul>
            <li class="left"><a href="<?php echo SmartSieve::setUrl('main.php');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/filter.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Filter Rules');?></a></li>
<?php if ($_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']]->mode != 'advanced'): ?>
<?php if (SmartSieve::getConf('use_forward_mail_interface')):?>
            <li class="left"><A HREF="<?php echo SmartSieve::setUrl('rule.php?mode=forward');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/forward.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Forward Mail');?></A></li>
<?php endif;?>
            <li class="left"><a href="<?php echo SmartSieve::setUrl('rule.php?mode=vacation');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/vacation.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Out-of-Office');?></a></li>
            <li class="left"><a href="<?php echo SmartSieve::setUrl('rule.php');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/new.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('New Rule');?></a></li>
<?php if (SmartSieve::getConf('spam_filter')):?>
            <li class="left"><a href="<?php echo SmartSieve::setUrl('rule.php?mode=spam');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/spam.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Filter Spam');?></a></li>
<?php endif;?>
<?php endif;?>
<?php if (SmartSieve::getConf('allow_multi_scripts') !== false): ?>
            <li class="left"><A HREF="<?php echo SmartSieve::setUrl('scripts.php');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/scripts.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Manage Scripts');?></A></li>
<?php endif; //$default->allow_multi_scripts ?>
<?php foreach (SmartSieve::getConf('menu_items', array()) as $item):?>
            <li class="left"><a href="<?php echo $item['uri'];?>"<?php echo (isset($item['target'])) ? sprintf(' target="%s"', $item['target']) : '';?>><?php echo $item['label'];?></a></li>
<?php endforeach;?>
<?php if (!empty($help_url)): ?>
            <li><a href="<?php echo $help_url; ?>" target="_blank"><?php echo SmartSieve::text('Help');?></a></li>
<?php endif; //$help_url ?>
            <li class="right"><a href="<?php echo SmartSieve::setUrl('login.php?reason=logout');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/logout.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Logout');?></a></li>
            <li class="right"><?php echo SmartSieve::text('Logged in as "%s"', array($_SESSION['smartsieve']['authz']));?></li>
          </ul>
          </div>
        </TD>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

</FORM>

