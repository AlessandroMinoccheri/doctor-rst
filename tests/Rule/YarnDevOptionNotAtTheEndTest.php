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

use App\Rule\YarnDevOptionNotAtTheEnd;
use App\Tests\RstSample;

final class YarnDevOptionNotAtTheEndTest extends \App\Tests\UnitTestCase
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
            (new YarnDevOptionNotAtTheEnd())->check($sample->lines(), $sample->lineNumber())
        );
    }

    /**
     * @return \Generator<array{0: string|null, 1: RstSample}>
     */
    public function checkProvider(): \Generator
    {
        yield [
            null,
            new RstSample('yarn add --dev jquery'),
        ];

        yield [
            'Please move "--dev" option before the package',
            new RstSample('yarn add jquery --dev'),
        ];
    }
}
