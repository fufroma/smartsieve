<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */
?>
<script language="JavaScript" type="text/javascript">
<!--

function Submit(a)
{
    if (a == 'delete'){
	if (!confirm("Are you sure you want to delete this rule?")){
                return true;
        }
    }
    document.thisRule.thisAction.value = a;
    document.thisRule.submit();
}

function Activate()
{
    var n=0;
    n = NumSelected();
    alert(document.scripts.elements.length);
    document.scripts.action.value = 'activate';
    document.scripts.submit();
}

function NumSelected()
{
    var n = 0;
    for (i = 0; i < document.scripts.elements.length; i++) {
        if (document.scripts.elements[i].checked) n++;
    }
    return n;
}

//-->
</script>
