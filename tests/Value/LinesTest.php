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

namespace App\Tests\Value;

use App\Value\Lines;

final class LinesTest extends \App\Tests\UnitTestCase
{
    public function testCurrentThrowsOutOfBoundsExceptionWhenLinesIsInvalid(): void
    {
        $lines = Lines::fromArray([]);

        static::assertFalse($lines->valid());

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Line "0" does not exists.');

        $lines->current();
    }

    public function testKeyThrowsOutOfBoundsExceptionWhenLinesIsInvalid(): void
    {
        $lines = Lines::fromArray([]);

        static::assertFalse($lines->valid());

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Line "0" does not exists.');

        $lines->key();
    }

    public function testSeekRestoresCurrentPositionWhenTheGivenPositionIsInvalid(): void
    {
        $lines = Lines::fromArray([
            "hello\n",
            "world\n",
        ]);

        $lines->seek(1);

        $exception = null;
        try {
            $lines->seek(54);
        } catch (\OutOfBoundsException $exception) {
            static::assertSame('Line "54" does not exists.', $exception->getMessage());
        }

        static::assertNotNull($exception, sprintf('Expected "%s" exception to be thrown.', \OutOfBoundsException::class));
        static::assertSame(1, $lines->key());
    }
}
