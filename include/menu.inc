
<FORM ACTION="<?php print AppSession::setUrl('main.php');?>" METHOD="post" NAME="menu">

<TABLE WIDTH="100%" CELLPADDING="2" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="menuouter">
    <TABLE WIDTH="100%" CELLPADDING="5" BORDER="0" CELLSPACING="0">
      <TR>
        <TD CLASS="menu">
          &nbsp;
          <a href="<?php print AppSession::setUrl('login.php?reason=logout');?>">Logout</a>
        | <a href="<?php print AppSession::setUrl('main.php');?>">View All Rules</a>
<?php if ($scripts[$sieve->workingscript]->mode != 'advanced'){ ?>
<?php     if ($default->use_forward_mail_interface){ ?>
        | <A HREF="<?php print AppSession::setUrl('forward.php');?>">Forward Mail</A>
<?php     } ?>
        | <a href="<?php print AppSession::setUrl('vacation.php');?>">Vacation Settings</a>
        | <a href="<?php print AppSession::setUrl('rule.php');?>">New Filter Rule</a>
<?php } //end if $script->mode = 'advanced'
      if ($default->allow_multi_scripts) { ?>
        | <A HREF="<?php print AppSession::setUrl('scripts.php');?>">Manage Scripts</A>
<?php } //end if $default->allow_multi_scripts
      if (!empty($help_url)){ ?>
        | <a href="<?php echo $help_url; ?>">Help</a>
<?php } //end if $help_url ?>

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
<?php } //end if $default->allow_multi_scripts ?>
      </TR>
    </TABLE>
  </TD>
</TR>
</TABLE>

</FORM>

