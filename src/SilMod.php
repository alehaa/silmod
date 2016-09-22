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


	function __construct($twig_options, $module_paths = null)
	{
		parent::__construct();

		/* Initialize Twig service. */
		$this->register(new Silex\Provider\TwigServiceProvider(),
		                $twig_options);

		$this->load_modules($module_paths);
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
}

?>
