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
	if (!confirm("<?php echo SmartSieve::text('Are you sure you want to delete this rule?');?>")){
                return true;
        }
    }
<?php if ($default->websieve_auto_matches){ ?>
    for (i = 0; i < document.thisRule.elements.length; i++) {
        if (window.document.thisRule.elements[i].value.indexOf('*') != -1 ||
            window.document.thisRule.elements[i].value.indexOf('?') != -1){
            if (!confirm("<?php echo SmartSieve::text("Your match string contains a '*' or a '?' character.\\nThese will be interpreted as wildcard characters rather than literals.\\nIs this OK? If not, click Cancel and use the regex option.");?>"))
                return false;
        }
    }
<?php } ?>
    document.thisRule.thisAction.value = a;
    document.thisRule.submit();
}

//-->
</script>
