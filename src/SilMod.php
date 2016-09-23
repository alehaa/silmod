<?php

/* This file is part of SilMod.
 *
 * SilMod is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * SilMod is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 *
 *  http://www.gnu.org/licenses/
 *
 *
 * Copyright (C)
 *  2016 Alexander Haase <ahaase@alexhaase.de>
 */

namespace SilMod;

use Silex;


class SilMod extends Silex\Application
{
	/** \brief Module array.
	 *
	 * \details This variable stores all module names and their registration
	 *  functions in a central array that may be used by all .
	 */
	private $modules = array();


	function __construct($options = array())
	{
		parent::__construct();


		/* Set default options. */
		if (!isset($options['theme']))
			$options['theme'] = 'default';

		if (!isset($options['modules.path']))
			$options['modules.path'] = array();
		else if (is_string($options['modules.path']))
			$options['modules.path'] = array($options['modules.path']);


		/* Initialize Twig service. */
		$this->register(new Silex\Provider\TwigServiceProvider(),
		                array('twig.path' => $this->twig_paths($options)));

		/* Load all modules. */
		$this->load_modules($options['modules.path']);
	}


	/** \brief Load all modules in \p paths.
	 *
	 * \details Load all files with .php file extension in paths defined by
	 *  \p paths as modules.
	 *
	 *
	 * \param paths String or array of strings pointing to paths containing all
	 *  required modules.
	 */
	private function load_modules($paths)
	{
		/* If paths is a single string, convert it to an array with only one
		 * element. This allows us to reuse the code below for any number of
		 * paths defined. */
		if (is_string($paths))
			$paths = [$paths];

		/* If paths is not an array, throw an invalid argument exception. */
		if (!is_array($paths))
			throw new \InvalidArgumentException('load_modules method only '.
				'accepts string and array of string as argument.');


		/* Load modules from all paths. The app variable will be used to provide
		 * an interface that can be used by the loaded modules. */
		$app = $this;
		foreach ($paths as $path) {
			if (!is_dir($path))
				throw new \LogicException('Path \'$path\' does not exist.');

			foreach (glob($path.'{/*,}/autoload.php', GLOB_BRACE) as $file)
				require_once $file;
		}

		/* Call the registration complete callbacks. */
		foreach ($this->modules as $module)
			if ($module["callback"] != null)
				$module["callback"]();
	}


	/** \brief Get all template paths for twig.
	 *
	 * \details This function gathers all paths to use for twig to find
	 *  templates. It will use the module paths and defined options from the
	 *  constructor and return an array of paths.
	 *
	 *
	 * \param options Options passed from the constructor.
	 *
	 * \return Array of paths.
	 */
	private function twig_paths($options)
	{
		/* Add the template path as first path, so every view in the template
		 * directory may override the module specific view. This may be used to
		 * enhance a view for some themes. */
		$paths = array('themes/'.$options['theme'].'/views');

		/* Iterate over all module paths to append the module specific view
		 * paths. */
		foreach ($options['modules.path'] as $path) {
			if (!is_dir($path))
				throw new \LogicException("Path \'$path\' does not exist.");

			foreach (glob($path.'{/*,}/views', GLOB_BRACE) as $p)
				$paths[] = $p;
		}

		return $paths;
	}


	/** \brief Add module to global module array.
	 *
	 *
	 * \param name Module name.
	 * \param callback Registration callback function. Will be called after all
	 *  modules have been loaded.
	 */
	public function register_module($name, $callback = null)
	{
		$this->modules[] = ["name" => $name, "callback" => $callback];
	}


	/** \brief Add routes to global silex routing.
	 *
	 * \details This function calls \p function, which will define routes. All
	 *  defined routes will be mounted to the \p name subdomain.
	 *
	 *
	 * \param name Module name.
	 * \param callback Function to be called to register the modules routes.
	 */
	public function register_routes($name, $callback)
	{
		$subapp = $this['controllers_factory'];
		$callback($subapp);
		$this->mount("/".$name, $subapp);
	}
}

?>
