<FORM ACTION="<?php print AppSession::setUrl('main.php');?>" METHOD="post" NAME="rules">

<TABLE WIDTH="100%" CELLPADDING="0" BORDER="0" CELLSPACING="0">
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="0">
    <TR>
      <TD CLASS="heading">Mail Filter Rules:</TD>
    </TR>
    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
<?php

if ($script->rules){ ?>
      <TR>
        <TH WIDTH="5%">&nbsp;</TH>
        <TH WIDTH="10%">Status</TH>
        <TH WIDTH="80%">Rule</TH>
        <TH WIDTH="5%">Order</TH>
      </TR>
<?php

    $i = 0;
    foreach ($script->rules as $rule){
        $complete = buildRule($rule);
        $class = 'disabledrule';
        $eclass = 'disabled';
        $onmouseover = $css['.disabledrule-over']['background-color'];
        $onmouseout = $css['.disabledrule']['background-color'];
        if ($rule['status'] == 'ENABLED'){
            $class = 'enabledrule';
            $eclass = 'enabled';
            $onmouseover = $css['.enabledrule-over']['background-color'];
            $onmouseout = $css['.enabledrule']['background-color'];
        }
?>
    <TR CLASS="<?php echo $class; ?>" onmouseover="javascript:style.backgroundColor='<?php echo $onmouseover;?>'" onmouseout="javascript:style.backgroundColor='<?php echo $onmouseout;?>'">
      <TD>
        <INPUT TYPE="checkbox" NAME="ruleID[]" VALUE="<?php print $i; ?>">
      </TD>
      <TD CLASS="<?php echo $eclass; ?>">
        <?php echo $rule['status']; ?>
      </TD>
      <TD>
        <A CLASS="rule" HREF="<?php print AppSession::setUrl("rule.php?ruleID=$i"); ?>" onmouseover="window.status='Edit This Rule'; return true;" onmouseout="window.status='';"><?php print $complete; ?></A>
      </TD>
      <TD NOWRAP="nowrap">
        <A HREF="" onclick="ChangeOrder('increase',<?php print $i; ?>); return false;"><IMG SRC="<?php print $default->image_dir; ?>/up.gif" ALT="Move rule up" BORDER="0" onmouseover="window.status='Move rule up'; return true;" onmouseout="window.status='';"></A>
        <A HREF="" onclick="ChangeOrder('decrease',<?php print $i; ?>); return false;"><IMG SRC="<?php print $default->image_dir; ?>/down.gif" ALT="Move rule down" BORDER="0" onmouseover="window.status='Move rule down'; return true;" onmouseout="window.status='';"></A>
      </TD>
    </TR>
<?php
        $i++;
    }
}
else { ?>
    <TR CLASS="enabledrule">
      <TD COLSPAN="4">[No rules found]</TD>
    </TR>
<?php
}

if ($script->vacation){
    $class = 'disabledrule';
    $eclass = 'disabled';
    $onmouseover = $css['.disabledrule-over']['background-color'];
    $onmouseout = $css['.disabledrule']['background-color'];
    $status = 'DISABLED';
    if ($script->vacation['status'] == 'on'){
        $class = 'enabledrule';
        $eclass = 'enabled';
        $onmouseover = $css['.enabledrule-over']['background-color'];
        $onmouseout = $css['.enabledrule']['background-color'];
        $status = 'ENABLED';
    }
?>
    <TR>
      <TD CLASS="heading" COLSPAN="4">Vacation Message Settings:</TD>
    </TR>
    <TR CLASS="<?php echo $class; ?>" onmouseover="javascript:style.backgroundColor='<?php echo $onmouseover;?>'" onmouseout="javascript:style.backgroundColor='<?php echo $onmouseout; ?>'">
      <TD>
        &nbsp;
      </TD>
      <TD CLASS="<?php echo $eclass; ?>">
        <?php echo $status; ?>
      </TD>
      <TD COLSPAN="2">
        <A CLASS="rule" HREF="<?php print AppSession::setUrl('vacation.php'); ?>">days: <?php print $script->vacation['days']; ?> addresses: <?php

        $first = 1;
        foreach ($script->vacation['addresses'] as $address) {
            if (!$first) print ", ";
            print "\"$address\"";
            $first = 0;
        }
        print " text: " . $script->vacation['text']; ?></A>
      </TD>
    </TR>
<?php
} // end if $vacation.
?>

    </TABLE>

  </TD>
</TR>
<TR>
  <TD CLASS="main">

    <TABLE WIDTH="100%" BORDER="0" CELLPADDING="2" CELLSPACING="1">
    <BR>
      <TD CLASS="options">
        <A CLASS="option" HREF="" onclick="Submit('enable'); return false;" onmouseover="window.status='Enable'; return true;" onmouseout="window.status='';">Enable</a>
         |
        <A CLASS="option" HREF="" onclick="Submit('disable'); return false;" onmouseover="window.status='Disable'; return true;" onmouseout="window.status='';">Disable</a>
         |
        <A CLASS="option" HREF="" onclick="Submit('delete'); return false;" onmouseover="window.status='Delete'; return true;" onmouseout="window.status='';">Delete</a>
      </TD>
    </BR>
    </TABLE>

  </TD>
</TR>
</TABLE>

<INPUT TYPE="hidden" NAME="action" VALUE="" >
<INPUT TYPE="hidden" NAME="rindex" VALUE="" >

</FORM>

