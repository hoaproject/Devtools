<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2018, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Devtools\Bin;

use Hoa\Console;

/**
 * Class \Hoa\Devtools\Bin\Project.
 *
 * Paste something somewhere.
 *
 * @copyright  Copyright © 2007-2018 Hoa community
 * @license    New BSD License
 */
class Project extends Console\Dispatcher\Kit
{
	/**
	 * Options description.
	 *
	 * @var array
	 */
	protected $options = [
		['name', Console\GetOption::REQUIRED_ARGUMENT, 'n'],
		['help', Console\GetOption::NO_ARGUMENT, 'h'],
		['help', Console\GetOption::NO_ARGUMENT, '?']
	];

	/**
	 * The entry method.
	 */
	public function main()
	{
		$name = 'Foo';
		while (false !== $c = $this->getOption($v)) {
			switch ($c) {
				case 'n':
					$name = $v;

					break;

				case 'h':
				case '?':
					return $this->usage();

				case '__ambiguous':
					$this->resolveOptionAmbiguity($v);

					break;
			}
		}

		$this->filesCreate($name);

		return;
	}

	/**
	 * The command usage.
	 */
	public function usage()
	{
		echo
		'Usage   : devtools:project <options>', "\n",
		'Options :', "\n",
		$this->makeUsageOptionsList([
            'n'    => 'Name of the library.',
            'help' => 'This help.'
        ]), "\n";

		return;
	}

	protected function filesCreate($libName)
	{
		$iterator = new \GlobIterator(__DIR__ . '/../Resource/templates/*.*.php');
		foreach ($iterator as $fileinfo)
		{
			$filename = $fileinfo->getBasename('.php');
			if (file_exists($filename) && !$this->override($filename))
			{
				continue;
			}

			$template = file_get_contents($fileinfo->getPathname());

			$key = ['{LIB_NAME}', '{LIB_NAME_LOWER}'];
			$value = [$libName, strtolower($libName)];
			$content = str_replace($key, $value, $template);
			file_put_contents($filename, $content);
		}

		return;
	}

	protected function override($filename)
	{
		echo 'Override existing file: "', $filename , '" (y/n)?', "\n";
		$rl = new Console\Readline\Readline();
		$override = strtolower(trim($rl->readLine('> ')));
		return $override == 'y';
	}
}

__halt_compiler();
Initalize a project with standard files
