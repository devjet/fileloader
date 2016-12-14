
## File Loader
###### A simple composer package example


Example of usage library


Installation

Inside your webroot init Composer:
``
composer init
``
* add package:
``
composer require "devjet/fileloader":"dev-master"
``

* create [webroot]/public/index.php file with following code example:
```php
<?php

/* 
in case you place index.php in PUBLIC directory inside webroot
otherwise, correct bootstrap path to autoload.php
*/
require_once __DIR__ . '/../vendor/autoload.php';


use devjet\fileloader\FileLoader;

try {

    $loaderObject = FileLoader::getLoader()
        ->setLoadURL('http://images.all-free-download.com/images/graphiclarge/cat_having_a_stretch_205116.jpg')
        ->setSavePath('folder', true) //Create "folder" directory and put file there
        ->addExtension('tiff', 'zip') //add additional extensions
        ->load();

    echo $loaderObject->getLoadedFilePath(); //print absolute path to file 

} catch (Exception $exception) {
    echo $exception->getMessage();
}

```
...and you have to get picture of starching cat.


To run tests
```
phpunit ./vendor/devjet --bootstrap vendor/autoload.php
```