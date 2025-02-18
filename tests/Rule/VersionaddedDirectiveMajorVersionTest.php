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

use App\Rule\VersionaddedDirectiveMajorVersion;
use App\Tests\RstSample;
use Composer\Semver\VersionParser;

final class VersionaddedDirectiveMajorVersionTest extends \App\Tests\UnitTestCase
{
    /**
     * @test
     *
     * @dataProvider checkProvider
     */
    public function check(?string $expected, int $majorVersion, RstSample $sample): void
    {
        $rule = new VersionaddedDirectiveMajorVersion(new VersionParser());
        $rule->setOptions([
            'major_version' => $majorVersion,
        ]);

        static::assertSame(
            $expected,
            $rule->check($sample->lines(), $sample->lineNumber())
        );
    }

    /**
     * @return array<array{0: string|null, 1: int, 2: RstSample}>
     */
    public function checkProvider(): array
    {
        return [
            [
                null,
                3,
                new RstSample('.. versionadded:: 3'),
            ],
            [
                null,
                3,
                new RstSample('.. versionadded:: 3.4'),
            ],
            [
                null,
                3,
                new RstSample('.. versionadded:: 3.4.0'),
            ],
            [
                null,
                3,
                new RstSample('.. versionadded:: 3.4.0.0'),
            ],
            [
                null,
                3,
                new RstSample('.. versionadded:: 3.4   '),
            ],
            [
                'You are not allowed to use version "2.7". Only major version "3" is allowed.',
                3,
                new RstSample('.. versionadded:: 2.7'),
            ],
            [
                'You are not allowed to use version "4.0". Only major version "3" is allowed.',
                3,
                new RstSample('.. versionadded:: 4.0'),
            ],
            [
                'Please provide a numeric version behind ".. versionadded::" instead of "foo"',
                3,
                new RstSample('.. versionadded:: foo'),
            ],
        ];
    }
}
