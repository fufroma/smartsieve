<CENTER>

<FORM ACTION="<?php echo SmartSieve::setUrl('login.php'); ?>" METHOD="post" NAME="login">

<TABLE WIDTH="300" CELLPADDING="5" BORDER="0" CELLSPACING="0">
  <TR>
    <TD CLASS="welcome" ALIGN="center" COLSPAN="2">
      &nbsp;<?php echo SmartSieve::getConf('login_page_heading', SmartSieve::text('Welcome to SmartSieve'));?> 
    </TD>
  </TR>

  <TR>
    <TD ALIGN="center" COLSPAN="2">&nbsp;
<?php if ($reason == 'failure'): ?>
      <?php echo SmartSieve::text("Login failed! Please try again");?>.
<?php elseif ($reason == 'logout'): ?>
      <?php echo SmartSieve::text("You have been logged out");?>.
<?php endif; ?>
    </TD>
  </TR>

  <TR CLASS="menu">
    <TD ALIGN="right"> <?php echo SmartSieve::text("Sieve Username");?>:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="text" TABINDEX="<?php echo $tabindex++;?>" NAME="auth"<?php if (!empty($proxyusers)) echo " onBlur='checkUser();'";?>>
    </TD>
  </TR>
<?php if (!empty($proxyusers) || $proxyall): ?>
  <TR CLASS="menu" ID="authztr"<?php if (!$proxyall) echo ' STYLE="display: none;"';?>>
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

<?php if (SmartSieve::getConf('user_select_lang') !== false):?>
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
    <INPUT TYPE="hidden" NAME="lang" VALUE="<?php echo SmartSieve::getConf('language', 'en_GB');?>">
<?php endif; ?>
<?php if (SmartSieve::getConf('user_select_server') !== false):?>
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
<?php if (SmartSieve::getConf('user_supply_scriptfile') && SmartSieve::getConf('allow_multi_scripts') !== false):?>
  <TR CLASS="menu">
    <TD ALIGN="right"><?php echo SmartSieve::text("Script name");?>:
    </TD>
    <TD ALIGN="left">
      <INPUT TYPE="text" TABINDEX="<?php echo $tabindex++;?>" NAME="scriptfile" VALUE="<?php echo SmartSieve::getConf('scriptfile', '');?>">
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

<?php if (is_readable(SmartSieve::getConf('config_dir', 'conf') . '/motd.php')) include SmartSieve::getConf('config_dir', 'conf') . '/motd.php';?>

