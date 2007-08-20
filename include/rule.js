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

function Submit(a)
{
    if (a == 'delete'){
	if (!confirm("<?php echo SmartSieve::text('Are you sure you want to delete this rule?');?>")){
                return true;
        }
    }
    document.thisRule.thisAction.value = a;
    document.thisRule.submit();
}

//-->
</script>
