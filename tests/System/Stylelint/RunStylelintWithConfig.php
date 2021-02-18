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

    protected function setUp(): void
    {
        $process = new Process([
            'npm',
            'install',
        ]);

        $process->start();
        $process->join();
    }

    /**
     * @test
     * @large
     * @coversNothing
     */
    public function findViolationsByEslintInLess(): ?Generator
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
        MatcherAssert::assertThat($output, H::containsString('Expected indentation of 4 spaces'));
    }
    /**
     * @test
     * @large
     * @coversNothing
     */
    public function findViolationsByEslintInScss(): ?Generator
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/stylelint',
                '--config=' . __DIR__ . '/../../../config/stylelint/.stylelintrc',
                __DIR__ . '/../fixtures/stylelint/BadCode.scss',
            ]
        );

        yield $process->start();
        $output = yield buffer($process->getStdout());
        $exitCode = yield $process->join();

        self::assertSame(2, $exitCode);
        MatcherAssert::assertThat($output, H::containsString('Expected nesting depth to be no more than 3'));
    }

    /**
     * @test
     * @large
     * @coversNothing
     */
    public function findViolationsByEslintInSass(): ?Generator
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/stylelint',
                '--config=' . __DIR__ . '/../../../config/stylelint/.stylelintrc',
                __DIR__ . '/../fixtures/stylelint/BadCode.sass',
            ]
        );

        yield $process->start();
        $output = yield buffer($process->getStdout());
        $exitCode = yield $process->join();

        self::assertSame(2, $exitCode);
        MatcherAssert::assertThat($output, H::containsString('Expected nesting depth to be no more than 3'));
    }

    /**
     * @test
     * @large
     * @coversNothing
     */
    public function findViolationsByEslintInCss(): ?Generator
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/stylelint',
                '--config=' . __DIR__ . '/../../../config/stylelint/.stylelintrc',
                __DIR__ . '/../fixtures/stylelint/BadCssCode.css',
            ]
        );

        yield $process->start();
        $output = yield buffer($process->getStdout());
        $exitCode = yield $process->join();

        self::assertSame(2, $exitCode);
        MatcherAssert::assertThat($output, H::containsString('Unexpected empty block'));
    }
}
