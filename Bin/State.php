<?php

declare(strict_types=1);

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
 * Class \Hoa\Devtools\Bin\State.
 *
 * Get state of a library.
 */
class State extends Console\Dispatcher\Kit
{
    /**
     * Options description.
     */
    protected $options = [
        ['help', Console\GetOption::NO_ARGUMENT, 'h'],
        ['help', Console\GetOption::NO_ARGUMENT, '?']
    ];



    /**
     * The entry method.
     */
    public function main(): int
    {
        $library = null;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {
                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);

                    break;

                case 'h':
                case '?':
                default:
                    $this->usage();

                    return 0;
            }
        }

        $this->parser->listInputs($library);

        if (empty($library)) {
            $this->usage();

            return 0;
        }

        $library = ucfirst(strtolower($library));
        $path    = 'hoa://Library/' . $library;

        if (false === file_exists($path)) {
            throw new Console\Exception(
                'The %s library does not exist.',
                0,
                $library
            );
        }

        $status  = 'beta';
        $path .= '/.State';

        if (true === file_exists($path)) {
            $status = trim(file_get_contents($path));
        }

        echo $status;

        return 0;
    }

    /**
     * The command usage.
     */
    public function usage(): void
    {
        echo
            'Usage   : devtools:state <options> library', "\n",
            'Options :', "\n",
            $this->makeUsageOptionsList([
                'help' => 'This help.'
            ]), "\n";
    }
}

__halt_compiler();
Get the state of a library.
