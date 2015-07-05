<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2015, Hoa community. All rights reserved.
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
use Hoa\Core;
use Hoa\File;

/**
 * Class \Hoa\Devtools\Bin\Stub
 *
 * This command resolve the class_alias function in destination to IDE.
 *
 * @copyright  Copyright © 2007-2015 Hoa community
 * @license    New BSD License
 */
class Stub extends Console\Dispatcher\Kit
{
    /**
     * Options description.
     *
     * @var array
     */
    protected $options = [
        ['dry-run', Console\GetOption::NO_ARGUMENT      , 'd'],
        ['stub',    Console\GetOption::REQUIRED_ARGUMENT, 's'],
        ['verbose', Console\GetOption::NO_ARGUMENT      , 'V'],
        ['help',    Console\GetOption::NO_ARGUMENT      , 'h'],
        ['help',    Console\GetOption::NO_ARGUMENT      , '?']
    ];



    /**
     * The entry method.
     *
     * @return  void
     */
    public function main()
    {
        $dryRun  = false;
        $stub    = null;
        $verbose = false;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {
                case 'd':
                    $dryRun = true;

                  break;

                case 's':
                    $stub = $v;

                    break;

                case 'V':
                    $verbose = true;

                    break;

                case 'h':
                case '?':
                    return $this->usage();

                  break;

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);

                  break;
            }
        }

        if (null === $stub) {
            return $this->usage();
        }

        $hoaPath = 'hoa://Library/';
        $aliases = [];
        $finder  = new File\Finder();
        $finder
            ->in(resolve($hoaPath, true, true))
            ->name('#\.php$#')
            ->maxDepth(100)
            ->files();

        // READ
        foreach ($finder as $file) {
            $pathName  = $file->getPathName();
            $raw       = file_get_contents($pathName);

            if (!preg_match('#flexEntity\(\'([^\']+)#', $raw, $class)) {
                preg_match('#\nclass_alias\(\'([^\']+)#', $raw, $class);
            }

            if (empty($class)) {
                continue;
            }

            $FQCN      = $class[1]; // Fully-Qualified Class Name
            $alias     = Core\Consistency::getEntityShortestName($FQCN);
            $className = substr($alias, strrpos($alias, '\\') + 1);

            preg_match(
                '#((?:(?:abstract|final)\s)?' .
                'class|interface|trait)\s+' . $className . '\s#',
                $raw,
                $keyword
            );

            $aliases[] = [
                'FQCN'      => $FQCN,
                'alias'     => $alias,
                'keyword'   => $keyword[1],
                'className' => $className
            ];

            if (true === $verbose) {
                echo
                    $keyword[1] . ': ' .
                    $FQCN . ' > ' .
                    $alias . "\n";
            }
        }

        // WRITE
        if (true === $dryRun) {
            return;
        }

        $out = '<?php ' . "\n";
        foreach ($aliases as $class) {
            $ns = substr($class['alias'], 0, strrpos($class['alias'], '\\'));

            $out .= 'namespace ' . $ns . ' {' . "\n";
            $out .= $class['keyword'] . ' ' . $class['className'];
            $out .= ' extends \\' . $class['FQCN'] . ' {}' . "\n";
            $out .= '}' . "\n";
        }

        file_put_contents($stub, $out);

        return;
    }

    /**
     * The command usage.
     *
     * @return  void
     */
    public function usage()
    {
        echo 'Usage   : devtools:stub <options>', "\n",
             'Options :', "\n",
             $this->makeUsageOptionsList([
                 'dry-run' => 'No written operation',
                 'verbose' => 'Echo all information',
                 'stub'    => 'Path to stub file',
                 'help'    => 'This help.'
             ]), "\n";

        return;
    }
}

__halt_compiler();
Resolve class_alias function for the IDE.
