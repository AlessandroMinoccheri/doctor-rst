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

use App\Rule\NoContraction;
use App\Tests\RstSample;

final class NoContractionTest extends \App\Tests\UnitTestCase
{
    /**
     * @test
     *
     * @dataProvider checkProvider
     */
    public function check(?string $expected, RstSample $sample): void
    {
        $configuredRules = [];
        foreach (NoContraction::getList() as $search => $message) {
            $configuredRules[] = (new NoContraction())->configure($search, $message);
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
            // am
            'i am',
            // are
            'you are',
            'we are',
            'they are',
            // is and hase
            'he is',
            'she is',
            'it is',
            // have
            'you have',
            'we have',
            'they have',
            // will
            'i will',
            'you will',
            'he will',
            'she will',
            'it will',
            'we will',
            'they will',
            // had and would
            'i had',
            'you had',
            'he had',
            'she had',
            'it had',
            'we had',
            'they had',
            'i would',
            'you would',
            'he would',
            'she would',
            'it would',
            'we would',
            'they would',
            // not
            'are not',
            'cannot',
            'could not',
            'did not',
            'has not',
            'have not',
            'is not',
            'must not',
            'shall not',
            'should not',
            'was not',
            'were not',
            'will not',
            'would not',
            // valid usages
            "use PHPUnit's",
        ];

        foreach ($valids as $valid) {
            yield $valid => [null, new RstSample($valid)];

            $validUppercase = ucfirst($valid);
            yield $validUppercase => [null, new RstSample($validUppercase)];
        }

        $invalids = [
            // am
            "i'm" => null,
            // are
            "you're" => null,
            "we're" => null,
            "they're" => null,
            // is and hase
            "he's" => null,
            "she's" => null,
            "it's" => null,
            // have
            "you've" => null,
            "we've" => null,
            "they've" => null,
            // will
            "i'll" => null,
            "you'll" => null,
            "he'll" => null,
            "she'll" => null,
            "it'll" => null,
            "we'll" => null,
            "they'll" => null,
            // had and would
            "i'd" => null,
            "you'd" => null,
            "he'd" => null,
            "she'd" => null,
            "it'd" => null,
            "we'd" => null,
            "they'd" => null,
            // not
            "aren't" => null,
            "can't" => null,
            "couldn't" => null,
            "didn't" => null,
            "hasn't" => null,
            "haven't" => null,
            "isn't" => null,
            "mustn't" => null,
            "shan't" => null,
            "shouldn't" => null,
            "wasn't" => null,
            "weren't" => null,
            "won't" => null,
            "wouldn't" => null,
            // invalid usages
            "foobar it's" => "it's",
            "(it's" => "it's",
            " it's" => "it's",
        ];

        foreach ($invalids as $invalid => $matched) {
            yield $invalid => [
                sprintf('Please do not use contraction for: %s', $matched ?? $invalid),
                new RstSample($invalid),
            ];

            $invalidUppercase = ucfirst($invalid);

            if ($invalidUppercase !== $invalid) {
                yield $invalidUppercase => [
                    sprintf('Please do not use contraction for: %s', $matched ?? $invalidUppercase),
                    new RstSample($invalidUppercase),
                ];
            }
        }
    }
}
