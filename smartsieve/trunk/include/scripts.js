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

function viewScript(script)
{
    document.scripts.action.value = 'viewscript';
    document.scripts.viewscript.value = script;
    document.scripts.submit();
}

function setActive()
{
    if (numSelected() == 0){
        alert('Please select a script to activate');
        return false;
    }
    document.scripts.action.value = 'setactive';
    document.scripts.submit();
}

function createScript()
{
    var newscript = prompt('Please supply a name for your new script','');
    if (newscript != ''){
        document.scripts.action.value = 'createscript';
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
