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

namespace Hoa\Devtools\Resource\PHPCSFixer\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

/**
 * Class \Hoa\Devtools\Resource\PHPCSFixer\Fixer\PhpdocVar.
 *
 * Transform the old `@var` syntax to the standard one, i.e.:
 *
 *     @var $classOwner $type
 *     @var $classType  object
 *
 * becomes:
 *
 *     @var type
 *     @var $classType
 */
class PhpdocVar extends AbstractFixer
{
    protected function applyfix(SplFileInfo $file, Tokens $tokens)
    {
        foreach ($tokens->findGivenKind(T_DOC_COMMENT) as $token) {
            $docBlock    = new DocBlock($token->getContent());
            $annotations = $docBlock->getAnnotationsOfType('var');

            if (empty($annotations)) {
                continue;
            }

            foreach ($annotations as $annotation) {
                $line        = $docBlock->getLine($annotation->getStart());
                $lineContent = $line->getContent();

                if (0 !== preg_match('/^(?<before>.*?@var )(?<one>[^\s]+) (?<two>\w+)/', $lineContent, $matches)) {
                    if ('object' === $matches['two']) {
                        $line->setContent($matches['before'] . $matches['one'] . "\n");
                    } else {
                        $line->setContent($matches['before'] . $matches['two'] . "\n");
                    }
                }
            }

            $token->setContent($docBlock->getContent());
        }

        return $tokens->generateCode();
    }

    public function getDefinition()
    {
        return new FixerDefinition(
            '`@var` must contain one element.'
        );
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }

    public function getName()
    {
        return 'Hoa/phpdoc_var';
    }
}
