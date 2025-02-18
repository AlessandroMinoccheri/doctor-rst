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

use App\Annotations\Rule\Description;
use App\Annotations\Rule\InvalidExample;
use App\Annotations\Rule\ValidExample;
use App\Value\Lines;
use App\Value\RuleGroup;

/**
 * @Description("Ensure exactly one space before directive type.")
 * @InvalidExample("..  code-block:: php")
 * @ValidExample(".. code-block:: php")
 */
class EnsureExactlyOneSpaceBeforeDirectiveType extends AbstractRule implements LineContentRule
{
    public static function getGroups(): array
    {
        return [
            RuleGroup::Symfony(),
        ];
    }

    public function check(Lines $lines, int $number): ?string
    {
        $lines->seek($number);
        $line = $lines->current();

        if (!$line->clean()->match('/\.\.\s*[a-z\-]+::/')) {
            return null;
        }

        if (!$line->clean()->match('/\.\.\ [a-z\-]+::/')) {
            return 'Please use only one whitespace between ".." and the directive type.';
        }

        return null;
    }
}
