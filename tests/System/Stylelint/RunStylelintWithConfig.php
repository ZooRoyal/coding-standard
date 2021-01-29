<?php

namespace Zooroyal\CodingStandard\Tests\System\Stylelint;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Amp\PHPUnit\AsyncTestCase;
use Symfony\Component\Process\Process;

class RunStylelintWithConfig extends AsyncTestCase
{
    /**
     * @test
     * @large
     */
    public function findViolationsByEslint()
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/stylelint',
                '--config=' . __DIR__ . '/../../../config/stylelint/.stylelintrc',
                __DIR__ . '/../fixtures/stylelint/BadCode.less',
            ]
        );

        $process->run();
        $process->wait();
        $errorCode = $process->getExitCode();
        $output = $process->getOutput();

        self::assertSame(2, $errorCode);
        MatcherAssert::assertThat($output, H::containsString('Expected no more than 1 empty line'));
    }
}
