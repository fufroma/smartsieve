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

function Save()
{
    document.rules.action.value = 'save';
    document.rules.submit();
}

function ChangeMode()
{
    if (!confirm("<?php echo SmartSieve::text('If you switch to GUI mode you will lose any changes you have made in direct edit mode.\nAre you sure you want to continue?');?>")) {
        return true;
    }
    document.rules.action.value = 'gui';
    document.rules.submit();
}

//-->
</script>
