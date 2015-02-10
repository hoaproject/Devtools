<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2015, Ivan Enderlin. All rights reserved.
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
 * Class \Hoa\Devtools\Bin\Stub
 *
 * This command resolve the class_alias function in destination to IDE.
 *
 * @author     Julien CLAUZEL <julien.clauzel@hoa-project.net>
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
 * @license    New BSD License
 */

class Stub extends \Hoa\Console\Dispatcher\Kit {

    /**
     * Options description.
     *
     * @var \Hoa\Devtools\Bin\Stub array
     */
    protected $options = [
        ['dry-run',      Console\GetOption::NO_ARGUMENT      , 'd'],
        ['verbose',      Console\GetOption::NO_ARGUMENT      , 'V'],
        ['stub',         Console\GetOption::REQUIRED_ARGUMENT, 's'],
        ['help',         Console\GetOption::NO_ARGUMENT      , 'h'],
        ['help',         Console\GetOption::NO_ARGUMENT      , '?']
    ];



    /**
     * The entry method.
     *
     * @access  public
     * @return  int
     */
    public function main ( ) {

        $porcelaine = false;
        $stub       = null;
        $verbose    = false;

        while(false !== $c = $this->getOption($v)) switch($c) {

            case 'V':
                $verbose = true;
                break;

            case 'd':
                $porcelaine = true;
              break;
            
            case 's':
                $stub = $v;
                break;

            case 'h':
            case '?':
                return $this->usage();
              break;

            case '__ambiguous':
                $this->resolveOptionAmbiguity($v);
              break;
        }

        $hoaPath = 'hoa://Library/';

        if($stub === null)
            return $this->usage();

        $finder = new \Hoa\File\Finder();
        $finder
            ->in($hoaPath)
            ->name('#\.php$#')
            ->maxDepth(100)
            ->files();

        $f = array();

        // READ
        foreach ($finder as $key => $value) {
            $pathname = $value->getPathName();
            $name     = $value->getBasename();

            preg_match('#Library([^\.]*)\.php$#', $pathname, $m);

            if(isset($m[1]) === true)
            {
                $name = 'Hoa'.$m[1];

                $e = [];
                foreach (explode('/', $name) as $value) {
                    $e[] = ucfirst($value);
                }


                $name = implode('/', $e);
                $end  = '';

                $raw = file_get_contents($pathname);

                 preg_match('#\nclass_alias\(([^,]*),(.*)\)#', $raw, $classAlias);

                if (isset($classAlias[1]) and $classAlias[1] !== '') {
                    $name  = substr(trim($classAlias[1]), 1, -1);
                    $end   = substr(trim($classAlias[2]), 1, -1);
                }
                 
                preg_match('#flexEntity\(\'(.*)\'#', $raw, $flexEntity);

                if (count($flexEntity) > 0) {
                    $c   = $flexEntity[1];
                    $end = \Hoa\Core\Consistency\Consistency::getEntityShortestName($c);
                }

                if ($end !== '') {
                    $f[] = array('class' => str_replace('/', '\\', $name), 'alias' => $end);
                }
            }
        }



        // WRITE
        $out = '<?php '."\n";
        foreach ($f as $class) {
            $ns = substr($class['alias'], 0, strrpos($class['alias'], '\\'));
            $c  = substr($class['alias'], strrpos($class['alias'], '\\')+1);

            $out .= 'namespace '.$ns.' {'."\n";
            $out .= 'class '.$c.' extends \\'.$class['class'].' {}'."\n";
            $out .= '}'."\n";

            if($verbose === true) {
                echo $class['class'].' > '.$class['alias']."\n";
            }

        }

        if($porcelaine === false) {

            file_put_contents($stub, $out);
        }

        return;
    }

    /**
     * The command usage.
     *
     * @access  public
     * @return  int
     */
    public function usage ( ) {

        echo 'Usage   : devtools:stub <options>', "\n",
             'Options :', "\n",
             $this->makeUsageOptionsList([
                 'dry-run' => 'No written operation',
                 'verbose' => 'Echo all information',
                 'stub' => 'Path to stub file',
                 'help' => 'This help.'
             ]), "\n";

        return;
    }
}

__halt_compiler();
Resolve class_alias function for the IDE.
