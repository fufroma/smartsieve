<?php
/*
 * $Id$
 *
 * Copyright 2002-2006 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
include "$default->lib_dir/SmartSieve.lib";
require SmartSieve::getConf('config_dir', 'conf') . '/style.php';


header('Content-type: text/css');
if (SmartSieve::getConf('cache_css') !== false) {
    $mtime = filemtime(SmartSieve::getConf('config_dir', 'conf') . '/style.php');
    $last_mod = sprintf('%s GMT', gmdate('D, d M Y H:i:s', $mtime));
    header('Last-Modified: ' . $last_mod);
    header('Cache-Control: max-age=86400, public, must-revalidate');
} else {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Expires: -1');
}

if (is_array($css)){
    foreach ($css as $class => $attributes){
        echo $class . "{\n";
        if (is_array($attributes)){
            foreach ($attributes as $name => $val){
                echo "\t$name: $val;\n";
            }
        }
        echo "}\n\n";
    }
}


