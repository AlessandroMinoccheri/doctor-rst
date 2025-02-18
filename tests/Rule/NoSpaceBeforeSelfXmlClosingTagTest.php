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

namespace App\Tests\Rule;

use App\Rule\NoSpaceBeforeSelfXmlClosingTag;
use App\Tests\RstSample;

final class NoSpaceBeforeSelfXmlClosingTagTest extends \App\Tests\UnitTestCase
{
    /**
     * @test
     *
     * @dataProvider checkProvider
     */
    public function check(?string $expected, RstSample $sample): void
    {
        static::assertSame(
            $expected,
            (new NoSpaceBeforeSelfXmlClosingTag())->check($sample->lines(), $sample->lineNumber())
        );
    }

    public function checkProvider(): array
    {
        return [
            [
                'Please remove space before "/>"',
                new RstSample('<argument type="service" id="sonata.admin.search.handler" />'),
            ],
            [
                'Please remove space before "/>"',
                new RstSample('<argument />'),
            ],
            [
                null,
                new RstSample('/>'),
            ],
            [
                null,
                new RstSample('<argument type="service" id="sonata.admin.search.handler"/>'),
            ],
            [
                null,
                new RstSample('<br/>'),
            ],
        ];
    }
}
