
<FORM ACTION="<?php echo SmartSieve::setUrl('main.php');?>" METHOD="post" NAME="menu">

<TABLE WIDTH="100%" CELLPADDING="1" BORDER="0" CELLSPACING="0">
<TR CLASS="menuouter">
  <TD>
    <TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
      <TR CLASS="menu">
        <TD align="left">
          <TABLE CELLPADDING="2" BORDER="0" CELLSPACING="2">
            <tr>
              <td>
                <a href="<?php echo SmartSieve::setUrl('main.php');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/filter.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Filter Rules');?></a>
              </td>
<?php if ($_SESSION['scripts'][$_SESSION['smartsieve']['workingScript']]->mode != 'advanced'): ?>
<?php if (SmartSieve::getConf('use_whitelist', true)):?>
              <td>
                <A HREF="<?php echo SmartSieve::setUrl('rule.php?mode=whitelist');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/whitelist.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Whitelist');?></A>
              </td>
<?php endif;?>
<?php if (SmartSieve::getConf('use_forward_mail_interface', true)):?>
              <td>
                <A HREF="<?php echo SmartSieve::setUrl('rule.php?mode=forward');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/forward.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Forward Mail');?></A>
              </td>
<?php endif;?>
<?php if (SmartSieve::getConf('use_vacation_interface', true)):?>
              <td>
                <a href="<?php echo SmartSieve::setUrl('rule.php?mode=vacation');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/vacation.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Vacation Settings');?></a>
              </td>
<?php endif;?>
              <td>
                <a href="<?php echo SmartSieve::setUrl('rule.php');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/new.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('New Rule');?></a>
              </td>
<?php if (SmartSieve::getConf('spam_filter')):?>
              <td>
                <a href="<?php echo SmartSieve::setUrl('rule.php?mode=spam');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/spam.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Filter Spam');?></a>
              </td>
<?php endif;?>
<?php endif;?>
<?php if (SmartSieve::getConf('allow_multi_scripts') !== false): ?>
              <td>
                <A HREF="<?php echo SmartSieve::setUrl('scripts.php');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/scripts.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Manage Scripts');?></A>
              </td>
<?php endif; //$default->allow_multi_scripts ?>
<?php foreach (SmartSieve::getConf('menu_items', array()) as $item):?>
              <td>
                <a href="<?php echo $item['uri'];?>"<?php echo (isset($item['target'])) ? sprintf(' target="%s"', $item['target']) : '';?>><img src="<?php echo (isset($item['img'])) ? $item['img'] : SmartSieve::getConf('image_dir', 'images').'/new.gif';?>" alt="" title="" border="0"><br><?php echo $item['label'];?></a>
              </td>
<?php endforeach;?>
<?php if (!empty($help_url)): ?>
              <td>
                <a href="<?php echo $help_url; ?>" target="_blank"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/help.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Help');?></a>
              </td>
<?php endif; //$help_url ?>
            </tr>
          </table>
        </td>
        <td align="right">
          <TABLE CELLPADDING="2" BORDER="0" CELLSPACING="2">
            <tr>
              <td>
                <?php echo SmartSieve::text('Logged in as "%s"', array($_SESSION['smartsieve']['authz']));?>
              </td>
              <td>
                <a href="<?php echo SmartSieve::setUrl('login.php?reason=logout');?>"><img src="<?php echo SmartSieve::getConf('image_dir', 'images');?>/logout.gif" alt="" title="" border="0"><br><?php echo SmartSieve::text('Logout');?></a>
              </td>
            </tr>
          </table>
        </TD>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

</FORM>

