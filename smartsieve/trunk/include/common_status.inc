
<BR>
<?php if (!empty($_SESSION['errors']) || !empty($_SESSION['notices'])): ?>

<TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
<?php foreach (SmartSieve::setError() as $err): ?>
  <TR>
    <TD CLASS="errors">
      <?php echo $err; ?> 
    </TD>
  </TR>
<?php endforeach; ?>
<?php foreach (SmartSieve::setNotice() as $notice): ?>
  <TR>
    <TD CLASS="messages">
      <?php echo $notice; ?> 
    </TD>
  </TR>
<?php endforeach; ?>
</TABLE>

<BR>
<?php endif; ?>

<TABLE WIDTH="100%" CELLPADDING="1" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="statusouter">
    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
      <TR CLASS="status">
        <TD>
          &nbsp;<?php echo SmartSieve::text('User');?>: <?php echo $_SESSION['smartsieve']['authz']; ?> 
        </TD>
        <TD>
          &nbsp;<?php echo SmartSieve::text('Server');?>: <?php echo $_SESSION['smartsieve']['server']; ?> 
        </TD>
        <TD>
          &nbsp;<?php echo SmartSieve::text('Script');?>: <?php echo $_SESSION['smartsieve']['workingScript']; ?> 
        </TD>
<?php if ($_SESSION['smartsieve']['workingScript'] === SmartSieve::getActiveScript()): ?>
        <TD CLASS="active">
          <?php echo SmartSieve::text('ACTIVE');?>
        </TD>
<?php else: ?>
        <TD CLASS="inactive">
          <?php echo SmartSieve::text('NOT ACTIVE');?>
        </TD>
<?php endif; ?>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

<BR>

