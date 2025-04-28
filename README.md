# Database of car brands with PHP and MySQL
Version: 0.4

__This second sample project with PHP is now finished. ✅__


Next steps: Use of the PHP framework for standard tasks

## Features
* Implementation of simple CRUD (Create, Read, Update, Delete) functionality of car brands
* Data is stored as in a MySQL database
* Website is repsonsive for smaller screens

## Project struture
```
/
├── css/
│   └── stylesheet.css
├── data/
│   ├── cars.json
│   └── formhandler.php
├── fonts/
│   ├── fonts/open-sans-v40-latin-regular.ttf
│   └── fonts/open-sans-v40-latin-regular.woff2
├── html_templates/
│   ├── add_frame.html
│   ├── delete_frame.html
│   ├── edit_frame.html
│   ├── footer.html
│   ├── header.html
│   ├── html_head.html
│   ├── main_template.html
│   ├── navigation.html
│   ├── show_frame.html
│   └── welcome.html
├── img/
│   ├── add.svg
│   ├── cancel.svg
│   ├── car.jpg
│   ├── delete.svg
│   ├── edit.svg
│   ├── home.svg
│   ├── list.svg
│   └── user.svg
├── src/
│   ├── ClassInfo.php
│   ├── Controller.php
│   ├── Logger.php
│   ├── Model.php
│   └── View.php
├── index.php
├── mylogging.txt
└── README.md
```
## Complete MVC-Pattern
Model, view and controller are instantiated and saved in the session variable $_SESSION in `index.php`. All other classes are included in this file. The entire application is only controlled via the _GET_ and _POST_ parameters in `index.php`.

All database actions are done
in `Model.php`. As PDO objects cannot be serialized (for storage in session variable), the database connection is first
initiated for every current SQL query.

`Controller.php`controls all user input as well as routing.

Details for View: see version 0.3 (of repo 'car_brands_json')

## Simple Logging
Usage:
```PHP
require_once 'Logger.php';
$logger = Logger::setLogger();  //Default log file 'mylogging.txt'
($logger)('This is a log');
```

## Automatic class information
Usage:
```PHP
require_once 'ClassInfo.php';
$params = [...]; // Parameters to instantiate the class
classInfo(__CLASS__, ...$params);
```
Each class can be called up directly and then displays following information:
* constructor parameters
* class constants
* methods
* variables
* static variables

## Box-Modell

```
/
├── head/
│   └── <link rel="stylesheet" href="./css/stylesheet.css">
└── body/
    ├── header
    ├── navigation
    ├── main {padding 16px;}
    │   └── <div class='content_box'> Here are the data presented in a rounded box
    |       |
    │       ├── <table> for "Welcome"
    │       │
    │       ├── <div class='scroll_box'> for "Show"
    │       │   ├── <div class='card'>
    │       │   ├── <div class='card'>
    │       │   ├── <div class='card'>
    │       │   ├── <div class='card'>
    │       │   └── ...
    │       │
    │       ├── <div class='card'> for "Delete"
    │       │
    │       └── <form> for "Add" and "Edit"
    │   
    └──  footer

```




 
