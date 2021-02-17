<?php

namespace Zooroyal\CodingStandard\Tests\System\Eslint;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class RunStylelintWithConfig extends TestCase
{

    protected function setUp()
    {
        $process = new Process([
            'npm',
            'install',
        ]);

        $process->mustRun();
    }
    /**
     * @test
     */
    public function findViolationsByEslintInLess()
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
        MatcherAssert::assertThat($output, H::containsString('Expected indentation of 4 spaces'));
    }

    /**
     * @test
     */
    public function findViolationsByEslintInScss()
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/stylelint',
                '--config=' . __DIR__ . '/../../../config/stylelint/.stylelintrc',
                __DIR__ . '/../fixtures/stylelint/BadCode.scss',
            ]
        );

        $process->run();
        $process->wait();
        $errorCode = $process->getExitCode();
        $output = $process->getOutput();

        self::assertSame(2, $errorCode);
        MatcherAssert::assertThat($output, H::containsString('Expected nesting depth to be no more than 3'));
    }
}
