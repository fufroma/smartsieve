<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */
?>
<script language="JavaScript" type="text/javascript">
<!--

function setFocus()
{
    document.login.auth.focus();
}

function changeLang ()
{
    if (document.login.auth.value == '' && document.login.passwd.value == ''){
        var $url = 'login.php?login_lang=' + document.login.lang.value;
        self.location = $url;
    }
}
<?php if (!empty($proxyusers)): ?>

function checkUser()
{
    u = document.login.auth.value;
    proxyusers = new Array();
<?php for ($i=0; $i<count($proxyusers); $i++): ?>
    proxyusers[<?php echo $i;?>] = '<?php echo $proxyusers[$i];?>';
<?php endfor; ?>
    for (i=0; i<proxyusers.length; i++) {
        if (u == proxyusers[i]) {
            if (document.layers) {  // NS4
                // Can't do this in NS4
            } else if (document.all && document.getElementById) {  // IE5+
                tr = document.getElementById('authztr').style.display = 'inline';
            } else if (document.getElementById) {
                tr = document.getElementById('authztr').style.display = 'table-row';
            }
        }
    }
}
<?php endif; ?>

//-->
</script>
