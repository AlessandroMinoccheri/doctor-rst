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

use App\Rule\AmericanEnglish;
use App\Tests\RstSample;

final class AmericanEnglishTest extends \App\Tests\UnitTestCase
{
    /**
     * @test
     *
     * @dataProvider checkProvider
     */
    public function check(?string $expected, RstSample $sample): void
    {
        $configuredRules = [];
        foreach (AmericanEnglish::getList() as $search => $message) {
            $configuredRules[] = (new AmericanEnglish())->configure($search, $message);
        }

        $violations = [];
        foreach ($configuredRules as $rule) {
            $violation = $rule->check($sample->lines(), $sample->lineNumber());
            if (null !== $violation) {
                $violations[] = $violation;
            }
        }

        if (null === $expected) {
            static::assertCount(0, $violations);
        } else {
            static::assertCount(1, $violations);
            static::assertSame($expected, $violations[0]);
        }
    }

    /**
     * @return \Generator<array{0: string|null, 1: RstSample}>
     */
    public function checkProvider(): \Generator
    {
        $valids = [
            'behavior',
            'behaviors',
            'initialize',
            'normalize',
            'organize',
            'recognize',
            'center',
            'color',
            'flavor',
            'license',
        ];

        foreach ($valids as $valid) {
            yield $valid => [null, new RstSample($valid)];

            $validUppercase = ucfirst($valid);
            yield $validUppercase => [null, new RstSample($validUppercase)];
        }

        $invalids = [
            'behaviour',
            'behaviours',
            'initialise',
            'normalise',
            'organise',
            'recognise',
            'centre',
            'colour',
            'flavour',
            'licence',
        ];

        foreach ($invalids as $invalid) {
            yield $invalid => [
                sprintf('Please use American English for: %s', $invalid),
                new RstSample($invalid),
            ];

            $invalidUppercase = ucfirst($invalid);
            yield $invalidUppercase => [
                sprintf('Please use American English for: %s', $invalidUppercase),
                new RstSample($invalidUppercase),
            ];
        }
    }
}
