
<BR>
<?php if ($errors || $msgs) {  ?>

<TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
<?php foreach ($errors as $err){ ?>
  <TR>
    <TD CLASS="errors">
      <?php print "$err\n"; ?>
    </TD>
  </TR>
<?php } ?>
<?php foreach ($msgs as $msg){ ?>
  <TR>
    <TD CLASS="messages">
      <?php echo "$msg\n"; ?>
    </TD>
  </TR>
<?php } ?>
</TABLE>

<BR>
<?php } //end if $errors ?>

<TABLE WIDTH="100%" CELLPADDING="1" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="statusouter">
    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
      <TR CLASS="status">
        <TD>
          &nbsp;<?php echo SmartSieve::text('User');?>: <?php print $sieve->user; ?> 
        </TD>
        <TD>
          &nbsp;<?php echo SmartSieve::text('Server');?>: <?php print $sieve->server; ?> 
        </TD>
        <TD>
          &nbsp;<?php echo SmartSieve::text('Script');?>: <?php print $sieve->workingscript; ?> 
        </TD>
<?php if (AppSession::isActiveScript($sieve->workingscript)) { ?>
        <TD CLASS="active">
          <?php echo SmartSieve::text('ACTIVE');?>
        </TD>
<?php } else { ?>
        <TD CLASS="inactive">
          <?php echo SmartSieve::text('NOT ACTIVE');?>
        </TD>
<?php } ?>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

<BR>

