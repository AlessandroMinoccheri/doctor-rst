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

use App\Rule\ExtensionXlfInsteadOfXliff;
use App\Tests\RstSample;

final class ExtensionXlfInsteadOfXliffTest extends \App\Tests\UnitTestCase
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
            (new ExtensionXlfInsteadOfXliff())->check($sample->lines(), $sample->lineNumber())
        );
    }

    /**
     * @return array<array{0: string|null, 1: RstSample}>
     */
    public function checkProvider(): array
    {
        return [
            [
                'Please use ".xlf" extension instead of ".xliff"',
                new RstSample('messages.xliff'),
            ],
            [
                null,
                new RstSample('messages.xlf'),
            ],
        ];
    }
}
