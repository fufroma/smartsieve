<CENTER>

<FORM ACTION="<?php echo AppSession::setUrl('login.php'); ?>" METHOD="post" NAME="login">

<TABLE WIDTH="300" CELLPADDING="5" BORDER="0" CELLSPACING="0">
  <TR>
    <TD CLASS="welcome" ALIGN="center" COLSPAN="2">
      <?php echo $default->login_page_heading; ?> 
    </TD>
  </TR>

  <TR>
    <TD ALIGN="center" COLSPAN="2">&nbsp;
<?php if (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'failure') { ?>
      Login failed! Please try again.
<?php }
      elseif (isset($HTTP_GET_VARS['reason']) && $HTTP_GET_VARS['reason'] == 'logout'){ ?>
      You have been logged out.
<?php }
      $tabindex = 1;
?>
    </TD>
  </TR>

  <TR CLASS="menu">
    <TD ALIGN="right"> Sieve Username:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="text" TABINDEX="<?php echo $tabindex++;?>" NAME="sieveuid">
    </TD>
  </TR>
  <TR CLASS="menu">
    <TD ALIGN="right"> Password:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="password" TABINDEX="<?php echo $tabindex++;?>" NAME="passwd">
    </TD>
  </TR>

<?php if ($default->user_select_server){ ?>
  <TR CLASS="menu">
    <TD ALIGN="right">Server:
    </TD>
    <TD ALIGN="left">
      <SELECT NAME="server" TABINDEX="<?php echo $tabindex++;?>">
<?php
      $sel = true;
      foreach ($servers as $key => $val){ ?>
        <OPTION VALUE="<?php echo $key; ?>"<?php if ($sel){ ?> SELECTED<?php $sel=false;} ?>><?php echo $val['display']; ?></OPTION>
<?php } ?>
      </SELECT>
    </TD>
  </TR>
<?php } 
      else {
        // take 1st $servers entry as default
        $server_val = '';
        foreach ($servers as $key => $val){
            if (!$server_val) $server_val = $key;
            else break;
        } ?>
    <INPUT TYPE="hidden" NAME="server" VALUE="<?php echo $server_val; ?>">
<?php } 
       if ($default->user_supply_scriptfile && $default->allow_multi_scripts) { ?>
  <TR CLASS="menu">
    <TD ALIGN="right">Script name:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="text" TABINDEX="<?php echo $tabindex++;?>" NAME="scriptfile" VALUE="<?php echo $default->scriptfile;?>">
    </TD>
  </TR>
<?php }
      else { ?>
    <INPUT TYPE="hidden" NAME="scriptfile" VALUE="">
<?php } ?>

  <TR CLASS="menu">
    <TD ALIGN="center" COLSPAN="2">
      <INPUT TYPE="submit" NAME="submit" TABINDEX="<?php echo $tabindex++;?>" VALUE="Log In">
    </TD>
  <TR>
</TABLE>
</CENTER>

</FORM>

<?php

if (is_readable($default->config_dir . '/motd.php')){
    include $default->config_dir . '/motd.php';
}

?>


</BODY>
</HTML>

