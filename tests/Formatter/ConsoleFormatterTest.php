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

namespace App\Tests\Formatter;

use App\Formatter\ConsoleFormatter;
use App\Value\AnalyzerResult;
use App\Value\ExcludedViolationList;
use App\Value\FileResult;
use App\Value\Violation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleFormatterTest extends \App\Tests\UnitTestCase
{
    public function testFormat(): void
    {
        $analyzeDir = \dirname(__DIR__, 2).'/dummy';

        $bufferedOutput = new BufferedOutput();
        $style = new SymfonyStyle($this->createMock(InputInterface::class), $bufferedOutput);

        $fileResultWithViolations = new FileResult(
            new \SplFileInfo($analyzeDir.'/docs/index.rst'),
            new ExcludedViolationList(
                [],
                [Violation::from('violation message', $analyzeDir.'/docs/index.rst', 2, 'dummy text')]
            )
        );
        $validFileResult = new FileResult(
            new \SplFileInfo($analyzeDir.'/docs/tutorial/introduction_one.rst'),
            new ExcludedViolationList([], [])
        );

        $analyzerResult = new AnalyzerResult([$fileResultWithViolations, $validFileResult]);

        (new ConsoleFormatter())->format($style, $analyzerResult, $analyzeDir, true);

        $expected = <<<OUTPUT
docs/index.rst ✘
    2: violation message
   ->  dummy text

docs/tutorial/introduction_one.rst ✔

 [WARNING] Found "1" invalid file!                                              


OUTPUT;

        static::assertSame($expected, $bufferedOutput->fetch());
    }
}
