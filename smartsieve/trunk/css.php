<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


require './conf/config.php';
include "$default->config_dir/style.php";


header('Content-type: text/css');
header('Cache-Control: must-revalidate');

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


