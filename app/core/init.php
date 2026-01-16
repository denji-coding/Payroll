<?php

require '../app/core/database.php';
require '../app/core/function.php';
require '../app/core/model.php';

function my_function($classname)
{
    $filename = "../app/model/" . ucfirst($classname) . ".php";
    if (file_exists($filename)) {
        require $filename;
    }
}

// Register the autoloader
spl_autoload_register('my_function');