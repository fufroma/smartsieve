<FORM ACTION="<?php print AppSession::setUrl('vacation.php');?>" METHOD="post" NAME="thisVacation">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="1">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
    <TR CLASS="heading">
      <TD>
    <?php if ($script->vacation) {
         print "Edit Vacation Auto-respond settings";
       }
       else print "Create New Vacation Auto-respond settings:";
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
                      echo 'ENABLED';
                  else echo 'DISABLED';
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
Vacation:
      </TD>
      <TD CLASS="heading">&nbsp;</TD>
    </TR>
    <TR>
      <TD NOWRAP="nowrap">
Auto-respond text:
      </TD>
      <TD NOWRAP="nowrap">
        <TEXTAREA NAME="text" ROWS="3" COLS="40" WRAP="hard" TABINDEX="1">
<?php if ($vacation['text']) print $vacation['text']; ?>
</TEXTAREA>
      </TD>
    </TR>
    <TR>
      <TD>
Days:
      </TD>
      <TD>
        <SELECT NAME="days">
<?php
if (!$default->max_vacation_days) $default->max_vacation_days = 10;
for ($i = 0; $i <= $default->max_vacation_days; $i++){
    $opt = "\t\t<OPTION ";
    if ($vacation['days'] == $i) $opt .= "SELECTED ";
    $opt .= "VALUE=\"$i\">$i</OPTION>\n";
    print $opt;
}
?>
        </SELECT>
      </TD>
    </TR>
    <TR>
      <TD>
Addresses:
      </TD>
      <TD>
        <INPUT TYPE="text" NAME="addresses" <?php
if (is_array($vacation['addresses'])) {
    print "VALUE=\"";
    $first = 1;
    foreach ($vacation['addresses'] as $address) {
      if (!$first) print ", ";
      print $address;
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
        <A CLASS="option" HREF="" onclick="Submit('save'); return false;" onmouseover="window.status='Save Changes'; return true;" onmouseout="window.status='';">Save Changes</a>
<?php if ($script->vacation) { ?>
         |
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='Enable'; return true;" onmouseout="window.status='';">Enable</a>
         |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='Disable'; return true;" onmouseout="window.status='';">Disable</a>
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

