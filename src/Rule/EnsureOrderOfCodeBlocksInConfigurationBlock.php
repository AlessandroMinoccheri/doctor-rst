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
use App\Value\Lines;
use App\Value\RuleGroup;
use Webmozart\Assert\Assert;

class EnsureOrderOfCodeBlocksInConfigurationBlock extends AbstractRule implements LineContentRule
{
    public static function getGroups(): array
    {
        return [
            RuleGroup::Sonata(),
            RuleGroup::Symfony(),
        ];
    }

    public function check(Lines $lines, int $number): ?string
    {
        $lines->seek($number);
        $line = $lines->current();

        if (!RstParser::directiveIs($line, RstParser::DIRECTIVE_CONFIGURATION_BLOCK)) {
            return null;
        }

        $indention = $line->indention();

        $lines->next();

        $validOrder = self::validOrder();
        $validXliffOrder = self::validOrderIncludingXliff();

        $xliff = false;

        $codeBlocks = [];
        while ($lines->valid() && ($indention < $lines->current()->indention() || $lines->current()->isBlank())) {
            if (RstParser::directiveIs($lines->current(), RstParser::DIRECTIVE_CODE_BLOCK)) {
                $codeBlocks[] = $lines->current()->clean()->toString();

                // if its an xml code-block, check if it contains xliff
                // @todo refactor in extra method: getDirectiveContent
                if (RstParser::codeBlockDirectiveIsTypeOf($lines->current(), RstParser::CODE_BLOCK_XML)) {
                    $content = clone $lines;
                    $content->seek($lines->key() + 1);

                    while ($content->valid() && ($content->current()->isBlank() || $lines->current()->indention() < $content->current()->indention())) {
                        if (false !== strpos($content->current()->raw()->toString(), 'xliff')) {
                            $xliff = true;

                            break;
                        }

                        $content->next();
                    }
                }
            }

            $lines->next();
        }

        foreach ($codeBlocks as $key => $codeBlock) {
            if (!\in_array($codeBlock, $validOrder, true)) {
                unset($codeBlocks[$key]);
            }
        }

        foreach ($validOrder as $key => $order) {
            if (!\in_array($order, $codeBlocks, true)) {
                unset($validOrder[$key]);
            }
        }

        // no xliff
        if (!$xliff && !$this->equal($codeBlocks, $validOrder) && 1 !== \count($validOrder)) {
            return sprintf(
                'Please use the following order for your code blocks: "%s"',
                str_replace('.. code-block:: ', '', implode(', ', $validOrder))
            );
        }

        // xliff
        foreach ($validXliffOrder as $key => $order) {
            if (!\in_array($order, $codeBlocks, true)) {
                unset($validXliffOrder[$key]);
            }
        }

        if ($xliff && !$this->equal($codeBlocks, $validXliffOrder) && !$this->equal($codeBlocks, $validOrder)) {
            return sprintf(
                'Please use the following order for your code blocks: "%s"',
                str_replace('.. code-block:: ', '', implode(', ', $validXliffOrder))
            );
        }

        return null;
    }

    public function equal(array $codeBlocks, array $validOrder): bool
    {
        try {
            Assert::eq(array_values($codeBlocks), array_values($validOrder));

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    private static function validOrder(): array
    {
        return [
            '.. code-block:: php-symfony',
            '.. code-block:: php-standalone',
            '.. code-block:: php-annotations',
            '.. code-block:: php-attributes',
            '.. code-block:: yaml',
            '.. code-block:: xml',
            '.. code-block:: php',
        ];
    }

    private static function validOrderIncludingXliff(): array
    {
        return [
            '.. code-block:: xml',
            '.. code-block:: php-annotations',
            '.. code-block:: php-attributes',
            '.. code-block:: yaml',
            '.. code-block:: php',
        ];
    }
}
