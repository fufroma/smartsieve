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

function viewScript(script)
{
    document.scripts.action.value = 'viewscript';
    document.scripts.viewscript.value = script;
    document.scripts.submit();
}

function deactivate()
{
    document.scripts.action.value = 'deactivate';
    document.scripts.submit();
}

function setScriptActive()
{
    if (numSelected() == 0){
        alert('<?php echo SmartSieve::text('Please select a script to activate');?>');
        return false;
    }
    document.scripts.action.value = 'setactive';
    document.scripts.submit();
}

function createScript()
{
    var newscript = prompt('<?php echo SmartSieve::text('Please supply a name for your new script');?>','');
    if (newscript){
        document.scripts.action.value = 'createscript';
        document.scripts.newscript.value = newscript;
        document.scripts.submit();
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
    document.scripts.action.value = 'delete';
    document.scripts.submit();
}

function renameScript()
{
    if (numSelected() == 0){
        alert('<?php echo SmartSieve::text('Please select the script to rename');?>');
        return false;
    }
    var newscript = prompt('<?php echo SmartSieve::text('Please supply the new name for this script');?>','');
    if (newscript){
        document.scripts.action.value = 'rename';
        document.scripts.newscript.value = newscript;
        document.scripts.submit();
    }

}

function numSelected()
{
    num = 0;
    for (i = 0; i < document.scripts.elements.length; i++) {
        if (document.scripts.elements[i].name == 'scriptID[]' &&
            document.scripts.elements[i].checked == true){
            num++;
        }
    }
    return num;
}


//-->
</script>