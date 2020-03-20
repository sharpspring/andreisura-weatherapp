<?php
// this script is used to load classes

spl_autoload_register(function ($name) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $name) . '.php';
    // $file = __DIR__ . '/src/' . str_replace('\\', '/', $name) . '.php';

    if (!file_exists($file)) {
        // print("File does not exist: ". $file);
        return;
    }
    require $file;
});

