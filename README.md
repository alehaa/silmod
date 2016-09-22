# SilMod

[![](https://img.shields.io/github/issues-raw/mksec/silmod.svg?style=flat-square)](https://github.com/mksec/silmod/issues) [![GPL license](http://img.shields.io/badge/license-LGPL-blue.svg?style=flat-square)](http://www.gnu.org/licenses/)

Module proxy for [Silex](http://silex.sensiolabs.org/).


## About

To get an easy to configure and extensible interface for some modules, SilMod should help to be a proxy for all modules. Each module should be able to be developed independently but the result will be a uniform interface for all of your modules.

The motivation for this was to merge different administration applications in a central application with the ability to extend the code for the users needs and decrease the maintenance and developement overhead. [Silex](http://silex.sensiolabs.org/) and other [Symfony](https://symfony.com/) components provide a nice interface to decrease the development overhead, but whenever you'd like to add a second module, you have to integrate it by hand in your code. SilMod provides a tiny wrapper arround Silex to load and integrate a set of modules.


## Installation

Install SilMod via ``composer.json``:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mksec/silmod"
        }
    ],
    "require": {
        "mksec/silmod": "~1.0"
    }
}
```

## Usage

A new SilMod application can be built as easy as a Silex application:
```php
<?php

require_once "vendor/autoload.php";

$app = new SilMod\SilMod(
	array('twig.path' => __DIR__.'/templates'), // twig options as for Silex
	array(__DIR__.'/modules')                   // paths to your modules
);

// Need some gobal variables in your templates? Define them as in Silex:
$app->extend('twig', function($twig, $app) {
    $twig->addGlobal('title', "My fancy application");
    return $twig;
});

$app->run();

?>
```


## Modules

Each file called `autoload.php` in the module path or the first level subdirectories will be included. The `$app` variable will be provided to access the SilMod instance. Each module must call the `register_module` function of `$app` to register itself. An additional callback function may be defined, which will be called after all modules have been loaded. This may be used to call functions defined by other modules (e.g. A is a backend for B). The Silex routing can be defined directly via ``$app``.

```php
<?php

// Register module.
$app->register_module("test", function () {
	echo "Callback called\n";
});


// add routes
$routes = $app['controllers_factory'];

$routes->get("/", function () use ($app) {
		return $app['twig']->render('test.twig', array("A" => "B"));
	});
$routes->get("/world", function () use ($app) {
		return "hello world!\n";
	});

$app->mount("/hello", $routes);

?>
```


## Contribute

Anyone is welcome to contribute. Simply fork this repository, make your changes **in an own branch** and create a pull-request for your change. Please do only one change per pull-request.

You found a bug? Please fill out an [issue](https://github.com/mksec/silmod/issues) and include any data to reproduce the bug.

#### Contributors

[Alexander Haase](https://github.com/alehaa)


## License

SilMod is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

SilMod is distributed in the hope that it will be useful, but **WITHOUT ANY WARRANTY**; without even the implied warranty of **MERCHANTABILITY** or **FITNESS FOR A PARTICULAR PURPOSE**. A Copy of the GPL can be found in the [LICENSE](LICENSE) file.

Copyright (C) 2016 Alexander Haase
