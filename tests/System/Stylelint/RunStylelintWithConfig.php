<?php

namespace Zooroyal\CodingStandard\Tests\System\Stylelint;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Process\Process;
use Generator;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use function Amp\ByteStream\buffer;

class RunStylelintWithConfig extends AsyncTestCase
{
    /**
     * @test
     * @large
     */
    public function findViolationsByEslint(): ?Generator
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/stylelint',
                '--config=' . __DIR__ . '/../../../config/stylelint/.stylelintrc',
                __DIR__ . '/../fixtures/stylelint/BadCode.less',
            ]
        );
        yield $process->start();
        $output = yield buffer($process->getStdout());
        $exitCode = yield $process->join();

        self::assertSame(2, $exitCode);
        MatcherAssert::assertThat($output, H::containsString('Expected no more than 1 empty line'));
    }
}
