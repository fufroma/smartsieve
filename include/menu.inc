
<FORM ACTION="<?php print AppSession::setUrl('main.php');?>" METHOD="post" NAME="menu">

<TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="menuouter">
    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
      <TR>
        <TD CLASS="menu">
          &nbsp;
          <a href="<?php print AppSession::setUrl('login.php?reason=logout');?>">Logout</a> |
          <a href="<?php print AppSession::setUrl('main.php');?>">View All Rules</a> |
          <a href="<?php print AppSession::setUrl('vacation.php');?>">Vacation Settings</a> |
          <a href="<?php print AppSession::setUrl('rule.php');?>">New Filter Rule</a>
<?php if ($default->allow_multi_scripts) { ?>|
          <A HREF="<?php print AppSession::setUrl('scripts.php');?>">Manage Scripts</A>
<?php } ?>
<?php if ($default->main_help_url){ ?>|
          <a href="<?php print $default->main_help_url; ?>">Help</a>
<?php } ?>

        </TD>
<?php if ($default->allow_multi_scripts) { ?>
        <TD CLASS="menu" ALIGN="right">
          &nbsp;
          <SELECT NAME="script" onchange="document.menu.submit();">
<?php     foreach ($sieve->scriptlist as $s){
              $str = "\t\t<OPTION VALUE=\"$s\"";
              if ($s == $sieve->workingscript)
                  $str .= " SELECTED=\"selected\"";
              $str .= ">$s</OPTION>\n";
              print $str;
          } ?>
          </SELECT>
        </TD>
<?php } //end if ?>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

</FORM>

