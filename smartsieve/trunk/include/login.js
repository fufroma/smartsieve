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

//-->
</script>
