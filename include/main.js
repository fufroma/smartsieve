<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */
?>
<script language="JavaScript" type="text/javascript">
<!--

function AnySelected()
{
    for (i = 0; i < document.rules.elements.length; i++) {
        if (document.rules.elements[i].name == 'ruleID[]' &&
            document.rules.elements[i].checked) {
            return true;
        }
    }
    return false;
}

function Submit(a)
{
    if (AnySelected()) {
	if (a == 'delete'){
	    if (!confirm("<?php echo SmartSieve::text('Are you sure you want to delete this rule?');?>")){
		return true;
	    }
	}
	document.rules.action.value = a;
        document.rules.submit();
    } else {
        window.alert('<?php echo SmartSieve::text('You must select at least one rule to do this.');?>');
    }
}

function ChangeOrder(a,b)
{
    document.rules.action.value = 'changeOrder';
    document.rules.rindex.value = a;
    document.rules.toPosition.value = b;
    document.rules.submit();
}

function ChangeMode()
{
    if (!confirm("<?php echo SmartSieve::text('If you edit this script directly, any changes you make will be lost if you revert to GUI later.\nDo you still want to continue?');?>")) {
        return true;
    }
    document.rules.action.value = 'direct';
    document.rules.submit();
}

//-->
</script>
