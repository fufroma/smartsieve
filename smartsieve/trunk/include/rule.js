<?php
/*
 * $Id$
 *
 * Copyright 2002-2006 Stephen Grier <stephengrier@users.sourceforge.net>
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
<?php if (SmartSieve::getConf('websieve_auto_matches') === true):?>
    for (i = 0; i < document.thisRule.elements.length; i++) {
        if (window.document.thisRule.elements[i].value.indexOf('*') != -1 ||
            window.document.thisRule.elements[i].value.indexOf('?') != -1){
            if (!confirm("<?php echo SmartSieve::text("Your match string contains a '*' or a '?' character.\\nThese will be interpreted as wildcard characters rather than literals.\\nIs this OK? If not, click Cancel and use the regex option.");?>"))
                return false;
        }
    }
<?php endif;?>
    document.thisRule.thisAction.value = a;
    document.thisRule.submit();
}

//-->
</script>
