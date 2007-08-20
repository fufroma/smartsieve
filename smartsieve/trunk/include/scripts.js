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

function edit(script)
{
    document.slist.action.value = '<?php echo FORM_ACTION_EDIT;?>';
    document.slist.viewscript.value = script;
    document.slist.submit();
}

function Deactivate()
{
    document.slist.action.value = '<?php echo FORM_ACTION_DEACTIVATE;?>';
    document.slist.submit();
}

function setScriptActive()
{
    if (numSelected() == 0){
        alert('<?php echo SmartSieve::text('Please select a script to activate');?>');
        return false;
    }
    document.slist.action.value = '<?php echo FORM_ACTION_ACTIVATE;?>';
    document.slist.submit();
}

function createScript()
{
    var newscript = prompt('<?php echo SmartSieve::text('Please supply a name for your new script');?>','');
    if (newscript){
        document.slist.action.value = '<?php echo FORM_ACTION_CREATE;?>';
        document.slist.newscript.value = newscript;
        document.slist.submit();
    }
}

function deleteScript()
{
    if (numSelected() == 0){
        alert('<?php echo SmartSieve::text('Please select a script to delete');?>');
        return false;
    }
    if (!confirm("<?php echo SmartSieve::text('You are about to permanently remove the selected scripts.\nAre you sure you want to do this?');?>")){
        return true;
    }
    document.slist.action.value = '<?php echo FORM_ACTION_DELETE;?>';
    document.slist.submit();
}

function renameScript()
{
    if (numSelected() == 0){
        alert('<?php echo SmartSieve::text('Please select the script to rename');?>');
        return false;
    }
    var newscript = prompt('<?php echo SmartSieve::text('Please supply the new name for this script');?>','');
    if (newscript){
        document.slist.action.value = '<?php echo FORM_ACTION_RENAME;?>';
        document.slist.newscript.value = newscript;
        document.slist.submit();
    }
}

function numSelected()
{
    num = 0;
    for (i = 0; i < document.slist.elements.length; i++) {
        if (document.slist.elements[i].name == 'scriptID[]' &&
            document.slist.elements[i].checked == true){
            num++;
        }
    }
    return num;
}


//-->
</script>
