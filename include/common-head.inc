<HTML>
<HEAD><TITLE><?php print $default->page_title; ?></TITLE>
<LINK HREF="<?php print AppSession::setUrl('css.php'); ?>" REL="stylesheet" TYPE="text/css">
<?php

if (!empty($jsfile))
    include $default->include_dir . '/' . $jsfile;

?>

</HEAD>

<BODY<?php if (!empty($jsonload)) echo ' onload="' . $jsonload . '"';?>>

