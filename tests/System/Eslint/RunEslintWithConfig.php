<?php

namespace Zooroyal\CodingStandard\Tests\System\Eslint;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class RunEslintWithConfig extends TestCase
{
    /**
     * @test
     */
    public function findViolationsByEslint()
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/eslint',
                '--config=' . __DIR__ . '/../../../config/eslint/.eslintrc.js',
                __DIR__ . '/../fixtures/eslint/BadCode.js',
            ]
        );

        $process->run();
        $process->wait();
        $errorCode = $process->getExitCode();
        $output = $process->getOutput();

        self::assertSame(1, $errorCode);
        MatcherAssert::assertThat($output, H::containsString('134 problems'));
    }
}
