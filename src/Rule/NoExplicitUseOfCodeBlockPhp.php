<?php

declare(strict_types=1);

/*
 * This file is part of DOCtor-RST.
 *
 * (c) Oskar Stark <oskarstark@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Rule;

use App\Rst\RstParser;
use App\Traits\DirectiveTrait;
use App\Value\Lines;
use App\Value\RuleGroup;

class NoExplicitUseOfCodeBlockPhp extends AbstractRule implements LineContentRule
{
    use DirectiveTrait;

    /**
     * @var string[]
     */
    public const ALLOWED_PREVIOUS_DIRECTIVES = [
        RstParser::DIRECTIVE_CAUTION,
        RstParser::DIRECTIVE_CONFIGURATION_BLOCK,
        RstParser::DIRECTIVE_DEPRECATED,
        RstParser::DIRECTIVE_NOTE,
        RstParser::DIRECTIVE_NOTICE,
        RstParser::DIRECTIVE_SEEALSO,
        RstParser::DIRECTIVE_VERSIONADDED,
        RstParser::DIRECTIVE_VERSIONCHANGED,
        RstParser::DIRECTIVE_WARNING,
        RstParser::DIRECTIVE_IMAGE,
    ];

    public static function getGroups(): array
    {
        return [RuleGroup::Symfony()];
    }

    public function check(Lines $lines, int $number): ?string
    {
        $lines->seek($number);

        // only interesting if a PHP code block
        if (!RstParser::codeBlockDirectiveIsTypeOf($lines->current(), RstParser::CODE_BLOCK_PHP, true)) {
            return null;
        }

        // :: is a php code block, but its ok
        if (preg_match('/\:\:$/', $lines->current()->clean()->toString())) {
            return null;
        }

        // it has no indention, check if it comes after a headline, in this case its ok
        if (!preg_match('/^[\s]+/', $lines->current()->raw()->toString(), $matches)) {
            if ($this->directAfterHeadline($lines, $number)
                || $this->directAfterTable($lines, $number)
                || $this->previousParagraphEndsWithQuestionMark($lines, $number)
            ) {
                return null;
            }
        }

        // check if the code block is not on the first level, in this case
        // it could not be in a configuration block which would be ok
        if (preg_match('/^[\s]+/', $lines->current()->raw()->toString(), $matches)
            && RstParser::codeBlockDirectiveIsTypeOf($lines->current(), RstParser::CODE_BLOCK_PHP)
            && $number > 0
        ) {
            if ($this->in(RstParser::DIRECTIVE_CONFIGURATION_BLOCK, $lines, $number)) {
                return null;
            }

            if ($this->in(RstParser::DIRECTIVE_CODE_BLOCK, $lines, $number, [RstParser::CODE_BLOCK_TEXT, RstParser::CODE_BLOCK_RST])) {
                return null;
            }
        }

        $previousAllowedDirectiveTypes = [
            RstParser::CODE_BLOCK_PHP,
            RstParser::CODE_BLOCK_YAML,
            RstParser::CODE_BLOCK_TERMINAL,
        ];

        // check if the previous code block is php, yaml or terminal code block
        if ($this->previousDirectiveIs(RstParser::DIRECTIVE_CODE_BLOCK, $lines, $number, $previousAllowedDirectiveTypes)) {
            return null;
        }

        foreach (self::ALLOWED_PREVIOUS_DIRECTIVES as $previousDirective) {
            // check if the previous directive is ...
            if ($this->previousDirectiveIs($previousDirective, $lines, $number)) {
                return null;
            }
        }

        $lines->next();
        if ($lines->valid() && RstParser::isOption($lines->current())) {
            return null;
        }

        return 'Please do not use ".. code-block:: php", use "::" instead.';
    }

    private function directAfterHeadline(Lines $lines, int $number): bool
    {
        $lines->seek($number);

        $i = $number;
        while ($i >= 1) {
            --$i;

            $lines->seek($i);

            if ($lines->current()->isBlank()) {
                continue;
            }

            if ($lines->current()->isHeadline()) {
                return true;
            }

            return false;
        }

        return false;
    }

    private function directAfterTable(Lines $lines, int $number): bool
    {
        $lines->seek($number);

        $i = $number;
        while ($i >= 1) {
            --$i;

            $lines->seek($i);

            if ($lines->current()->isBlank()) {
                continue;
            }

            if (RstParser::isTable($lines->current())) {
                return true;
            }

            return false;
        }

        return false;
    }

    private function previousParagraphEndsWithQuestionMark(Lines $lines, int $number): bool
    {
        $lines->seek($number);

        $i = $number;
        while ($i >= 1) {
            --$i;

            $lines->seek($i);

            if ($lines->current()->isBlank()) {
                continue;
            }

            if (preg_match('/\?$/', $lines->current()->clean()->toString())) {
                return true;
            }

            return false;
        }

        return false;
    }
}
