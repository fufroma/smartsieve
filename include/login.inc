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
<?php if ($reason == 'failure') { ?>
      <?php echo SmartSieve::text("Login failed! Please try again");?>.
<?php }
      elseif ($reason == 'logout'){ ?>
      <?php echo SmartSieve::text("You have been logged out");?>.
<?php }
      $tabindex = 1;
?>
    </TD>
  </TR>

  <TR CLASS="menu">
    <TD ALIGN="right"> <?php echo SmartSieve::text("Sieve Username");?>:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="text" TABINDEX="<?php echo $tabindex++;?>" NAME="auth">
    </TD>
  </TR>
<?php if (SmartSieve::getConf('allow_proxy_authz')): ?>
  <TR CLASS="menu">
    <TD ALIGN="right"> <?php echo SmartSieve::text("Proxy user");?>:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="text" TABINDEX="<?php echo $tabindex++;?>" NAME="authz">
    </TD>
  </TR>
<?php else: ?>
    <INPUT TYPE="hidden" NAME="authz" VALUE="">
<?php endif; ?>
  <TR CLASS="menu">
    <TD ALIGN="right"> <?php echo SmartSieve::text("Password");?>:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="password" TABINDEX="<?php echo $tabindex++;?>" NAME="passwd">
    </TD>
  </TR>

<?php if (!empty($default->user_select_lang)): ?>
  <TR CLASS="menu">
    <TD ALIGN="right"> <?php echo SmartSieve::text("Language");?>:
    </TD>
    <TD ALIGN="left">
      <SELECT TABINDEX="<?php echo $tabindex++;?>" NAME="lang" onchange="changeLang();">
<?php foreach ($locales as $value => $locale): ?>
        <OPTION VALUE="<?php echo $value;?>"<?php echo ($value == SmartSieve::getLang()) ? ' SELECTED' : '';?>><?php echo $locale['display'];?></OPTION>
<?php endforeach; ?>
      </SELECT>
    </TD>
  </TR>

<?php else: ?>
    <INPUT TYPE="hidden" NAME="lang" VALUE="<?php echo $default->language; ?>">
<?php endif; ?>
<?php if ($default->user_select_server): ?>
  <TR CLASS="menu">
    <TD ALIGN="right"><?php echo SmartSieve::text("Server");?>:
    </TD>
    <TD ALIGN="left">
      <SELECT NAME="server" TABINDEX="<?php echo $tabindex++;?>">
<?php foreach ($servers as $key => $val): ?>
        <OPTION VALUE="<?php echo $key; ?>"<?php echo ($key == $fsrv) ? ' SELECTED' : '';?>><?php echo $val['display']; ?></OPTION>
<?php endforeach; ?>
      </SELECT>
    </TD>
  </TR>
<?php else: ?>
    <INPUT TYPE="hidden" NAME="server" VALUE="<?php echo $fsrv;?>">
<?php endif; ?>
<?php if ($default->user_supply_scriptfile && $default->allow_multi_scripts): ?>
  <TR CLASS="menu">
    <TD ALIGN="right"><?php echo SmartSieve::text("Script name");?>:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="text" TABINDEX="<?php echo $tabindex++;?>" NAME="scriptfile" VALUE="<?php echo $default->scriptfile;?>">
    </TD>
  </TR>
<?php else: ?>
    <INPUT TYPE="hidden" NAME="scriptfile" VALUE="">
<?php endif; ?>

  <TR CLASS="menu">
    <TD ALIGN="center" COLSPAN="2">
      <INPUT TYPE="submit" NAME="submit" TABINDEX="<?php echo $tabindex++;?>" VALUE="<?php echo SmartSieve::text("Log In");?>">
    </TD>
  <TR>
</TABLE>
</CENTER>

</FORM>

<?php if (is_readable($default->config_dir . '/motd.php')) include $default->config_dir . '/motd.php';?>


</BODY>
</HTML>

