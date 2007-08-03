
<BR>
<?php if (!empty($_SESSION['errors']) || !empty($_SESSION['notices'])): ?>

<TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
<?php foreach (SmartSieve::setError() as $err): ?>
  <TR CLASS="errors">
    <TD>
      <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/error.gif" BORDER="0">
      <?php echo $err; ?> 
    </TD>
  </TR>
<?php endforeach; ?>
<?php foreach (SmartSieve::setNotice() as $notice): ?>
  <TR CLASS="messages">
    <TD>
      <IMG SRC="<?php echo SmartSieve::getConf('image_dir', 'images');?>/tick.gif" BORDER="0">
      <?php echo $notice; ?> 
    </TD>
  </TR>
<?php endforeach; ?>
</TABLE>

<BR>
<?php endif; ?>

