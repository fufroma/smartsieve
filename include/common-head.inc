<?php header('Content-type: text/html; charset=' . SmartSieve::getCharset());?>
<HTML>
<HEAD><TITLE><?php print SmartSieve::getConf('page_title'); ?></TITLE>
<LINK HREF="<?php print SmartSieve::setUrl('css.php'); ?>" REL="stylesheet" TYPE="text/css">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php print SmartSieve::getCharset();?>">
<?php if (!empty($jsfile)) include $default->include_dir . '/' . $jsfile;?>

</HEAD>

<BODY<?php if (!empty($jsonload)) echo ' onload="' . $jsonload . '"';?>>

