<?php
// Strict requirement: declaration of definitive types
declare(strict_types=1);

// Show all errors with next two lines of code:
error_reporting(E_ALL);  // Report all errors
ini_set('display_errors', true);  // Show errors on webpage, change to false for web-facing servers as a security measure.

//////////////////////////////////////////////////////////////////////
function classInfo($classname, ...$params) {
    echo '<hr>';
    echo '<h1>Class: ' . $classname . '</h1>';
    echo '<hr>';

    $reflectionClass = new ReflectionClass($classname);
    $constructor = $reflectionClass->getConstructor();
    if ($constructor) {
        echo '<h3>Constructor parameter</h3>';
        echo '<pre>';
        $constructorParams = $constructor->getParameters();
        foreach ($constructorParams as $name) {
            echo "$name\n";
        }        
        echo '</pre>';
    }
    else {
        echo "<h3>No constructor in this class</h3>";
    }

    echo '<h3>Constants</h3>';
    echo '<pre>';
    $contants = $reflectionClass->getConstants();
    if (isset($constants)) {
        foreach ($contants as $name => $value) {
            echo "$name: $value\n";
        }
    } else {
        echo 'None.';
    }
    echo '</pre>';

    echo '<h3>Methods</h3>';
    echo '<pre>';
    $class_methods = get_class_methods($classname);
    foreach ($class_methods as $method_name) {
        echo "$method_name()\n";
    }
    echo '</pre><hr>';

    if ($constructor) {
        echo "<h2>Creating the object</h2>";
        $object = new ($classname)(...$params);

        echo '<h3>Variables</h3>';
        echo '<pre>';
        //all non-static variables are found 'controller'-array 
        print_r(get_defined_vars()['object']);
        echo '</pre>';
    }     

    echo '<h3>Static variables</h3>';
    echo '<pre>';
    $staticProperties = $reflectionClass->getStaticProperties();
    foreach ($staticProperties as $name => $value) {
        echo "$name: $value\n";
    }
    echo '</pre><hr>';
}

