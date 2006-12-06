<FORM ACTION="<?php print SmartSieve::setUrl('vacation.php');?>" METHOD="post" NAME="thisVacation">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
    <?php if ($script->vacation) {
         echo SmartSieve::text('Edit vacation out-of-office message');
       }
       else echo SmartSieve::text('Set vacation out-of-office message').":";
    ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD CLASS="<?php echo ($vacation['status'] == 'on') ? 'enabled' : 'disabled';?>">
        <?php if ($script->vacation){
                  if ($vacation['status'] == 'on')
                      echo SmartSieve::text('ENABLED');
                  else echo SmartSieve::text('DISABLED');
              }
              else echo '&nbsp;'; ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="heading">
<?php echo SmartSieve::text('Vacation settings');?>:
      </TD>
      <TD CLASS="heading">&nbsp;</TD>
    </TR>
    <TR>
      <TD NOWRAP="nowrap">
<?php echo SmartSieve::text('Message');?>:
      </TD>
      <TD NOWRAP="nowrap">
        <TEXTAREA NAME="text" ROWS="3" COLS="<?php echo $wrap_width;?>" WRAP="hard" TABINDEX="1">
<?php if ($vacation['text']) print SmartSieve::utf8Decode($vacation['text']); ?>
</TEXTAREA>
      </TD>
    </TR>
    <TR>
      <TD>
<?php echo SmartSieve::text('Days between responses');?>:
      </TD>
      <TD>
        <SELECT NAME="days">
          <OPTION VALUE="0"<?php if (!$vacation['days']) echo ' SELECTED';?>></OPTION>
<?php
$max_days = !empty($default->max_vacation_days) ? $default->max_vacation_days : 30;
for ($i = 1; $i <= $max_days; $i++){
?>
          <OPTION VALUE="<?php echo $i;?>"<?php if ($vacation['days']==$i) echo ' SELECTED';?>><?php echo $i;?></OPTION>
<?php } ?>
        </SELECT>
      </TD>
    </TR>
    <TR>
      <TD>
<?php echo SmartSieve::text('Your e-mail addresses');?>:
      </TD>
      <TD>
        <INPUT TYPE="text" NAME="addresses" <?php
if (is_array($vacation['addresses'])) {
    print "VALUE=\"";
    $first = 1;
    foreach ($vacation['addresses'] as $address) {
      if (!$first) print ", ";
      print SmartSieve::utf8Decode($address);
      $first = 0;
    }
    print "\"";
}
?> SIZE="50">
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
    <TR>
      <TD CLASS="options" COLSPAN="2">
        <BR>
        <A CLASS="option" HREF="" onclick="Submit('save'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Save Changes');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Save Changes');?></a>
<?php if ($script->vacation) { ?>
         |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Enable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Enable');?></a>
         |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='<?php echo SmartSieve::text('Disable');?>'; return true;" onmouseout="window.status='';"><?php echo SmartSieve::text('Disable');?></a>
<?php } ?>
      </TD>
    </TR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="submitted" VALUE="1">
<INPUT TYPE="hidden" NAME="thisAction" VALUE="">
<INPUT TYPE="hidden" NAME="status" VALUE="<?php echo $vacation['status']; ?>">

</FORM>

</BODY>
</HTML>

