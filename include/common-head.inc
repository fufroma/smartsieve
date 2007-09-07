<?php header('Content-type: text/html; charset=' . SmartSieve::getCharset());?>
<HTML>
<HEAD><TITLE><?php echo SmartSieve::getConf('page_title', 'SmartSieve'); ?></TITLE>
<LINK HREF="<?php echo SmartSieve::setUrl('css.php'); ?>" REL="stylesheet" TYPE="text/css">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo SmartSieve::getCharset();?>">
<?php if (!empty($jsfile)) include SmartSieve::getConf('include_dir', 'include') . '/' . $jsfile;?>

</HEAD>

<BODY<?php if (!empty($jsonload)) echo ' onload="' . $jsonload . '"';?>>

