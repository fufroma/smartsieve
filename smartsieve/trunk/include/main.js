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

function AnySelected()
{
    for (i = 0; i < document.rules.elements.length; i++) {
        if (document.rules.elements[i].checked) return true;
    }
    return false;
}

function Submit(a)
{
    if (AnySelected()) {
	if (a == 'delete'){
	    if (!confirm("Are you sure you want to delete this rule?")){
		return true;
	    }
	}
	document.rules.action.value = a;
        document.rules.submit();
    } else {
        window.alert('You must select at least one rule to do this.');
    }
}

function ChangeOrder(a,b)
{
    document.rules.action.value = a;
    document.rules.rindex.value = b;
    document.rules.submit();
}

//-->
</script>
