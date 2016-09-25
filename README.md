# SilMod

[![](https://img.shields.io/github/issues-raw/mksec/silmod.svg?style=flat-square)](https://github.com/mksec/silmod/issues) [![GPL license](http://img.shields.io/badge/license-LGPL-blue.svg?style=flat-square)](http://www.gnu.org/licenses/)

Module proxy for [Silex](http://silex.sensiolabs.org/).


## About

To get an easy to configure and extensible interface for some modules, SilMod should be a proxy for all of your defined modules. Each module should be able to be developed independently but the result will be a uniform interface for all of your modules.

The motivation for this was to merge different administration applications into a central application with the ability to extend the code for the users needs and decrease the maintenance and developement overhead. [Silex](http://silex.sensiolabs.org/) and other [Symfony](https://symfony.com/) components provide a nice interface to decrease the development overhead, but whenever you'd like to add a second module, you have to integrate it by hand in your code. SilMod provides a tiny wrapper around Silex to load and integrate a set of modules around a basic infrastructure.


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

$app = new SilMod\SilMod(array(
	'twig' => array(
		'cache' => __DIR__.'/cache', // Twig options.
		'debug' => true
	),
	'modules' => array(
		'path' => 'modules'  // Your module directory.
		// 'path' => array(  // May be defined as an array, too.
		//   'modules',
		//   'externals/3rdparty'
		// )
	)
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

Each file called `autoload.php` in the module path or any of its subdirectories will be included.

Modules are classes in form of [Silex Providers](http://silex.sensiolabs.org/doc/master/providers.html). The `$app` variable will be provided before including the module, so each module has access to the SilMod instance and should register itself via `$app->register()`.

Provide the `register` method in your class to register e.g. new routes and the `boot` method to execute code after all modules have been registered.

```php
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\BootableProviderInterface;


class sample implements ServiceProviderInterface, BootableProviderInterface
{
	public function register(Container $app)
	{
		$app->get('/hello', function () {
			return "world";
		});
	}

	public function boot(Application $app)
	{
		if (isset($app['foo']))
			$app['bar'] = 'Wello World';
	}
}


$app->register(new sample());
```


For additional functionality you may use additional methods of the SilMod class:

#### `register_twig_path($name, $path)`

If your module should use own [Twig](http://twig.sensiolabs.org/) templates, you should add the required paths with this function. The paths will be mounted in the namespace `$name`.

```php
$app->register_twig_path('test', __DIR__.'/views');
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
