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

use App\Rule\Typo;
use App\Tests\RstSample;

final class TypoTest extends \App\Tests\UnitTestCase
{
    /**
     * @test
     *
     * @dataProvider validProvider
     * @dataProvider invalidProvider
     */
    public function check(?string $expected, RstSample $sample): void
    {
        $configuredRules = [];
        foreach (Typo::getList() as $search => $message) {
            $configuredRules[] = (new Typo())->configure($search, $message);
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
            static::assertStringStartsWith($expected, $violations[0]);
        }
    }

    /**
     * @return \Generator<string, array{0: null, 1: RstSample}>
     */
    public function validProvider(): \Generator
    {
        yield 'empty string' => [null, new RstSample('')];

        $valids = [
            'Composer',
            'composer',
            'registerBundles()',
            'return',
            'Displays',
            'displays',
            'Maintains',
            'maintains',
            'Doctrine',
            'doctrine',
            'Address',
            'address',
            'argon2i',
            'Description',
            'description',
            'Recalculate',
            'recalculate',
            'achieved',
            'overridden',
            'Successfully',
            'successfully',
            'Optionally',
            'optionally',
        ];

        foreach ($valids as $valid) {
            yield $valid => [null, new RstSample($valid)];

            // add leading spaces
            yield sprintf('"%s" with leading spaces', $valid) => [null, new RstSample(sprintf('    %s', $valid))];
        }
    }

    /**
     * @return \Generator<string, array{0: string, 1: RstSample}>
     */
    public function invalidProvider(): \Generator
    {
        $invalids = [
            'Compsoer',
            'compsoer',
            'registerbundles()',
            'retun',
            'Displayes',
            'displayes',
            'Mantains',
            'mantains',
            'Doctine',
            'doctine',
            'Adress',
            'adress',
            'argon21',
            'Descritpion',
            'descritpion',
            'Recalcuate',
            'recalcuate',
            'achived',
            'overriden',
            'Succesfully',
            'succesfully',
            'Optionnally',
            'optionnally',
        ];

        foreach ($invalids as $invalid) {
            yield $invalid => [sprintf('Typo in word "%s"', $invalid), new RstSample($invalid)];

            // add leading spaces
            yield sprintf('"%s" with leading spaces', $invalid) => [sprintf('Typo in word "%s"', $invalid), new RstSample(sprintf('    %s', $invalid))];
        }
    }
}
