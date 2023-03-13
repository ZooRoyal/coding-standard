<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\System\Stylelint;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Process\Process;
use Amp\Promise;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;

use function Amp\ByteStream\buffer;

class RunStylelintWithConfigTest extends AsyncTestCase
{
    /**
     * @test
     * @large
     * @coversNothing
     *
     * @return array<int,Promise>
     */
    public function findViolationsByEslintInLess(): iterable
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
        MatcherAssert::assertThat($output, H::containsString('Unexpected empty block'));
    }

    /**
     * @test
     * @large
     * @coversNothing
     *
     * @return array<int,Promise>
     */
    public function findViolationsByEslintInScss(): iterable
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
        MatcherAssert::assertThat($output, H::containsString('Expected nesting depth to be no more than 5'));
    }

    /**
     * @test
     * @large
     * @coversNothing
     *
     * @return array<int,Promise>
     */
    public function makeSureGoodScssPasses(): iterable
    {
        $process = new Process(
            [
                __DIR__ . '/../../../node_modules/.bin/stylelint',
                '--config=' . __DIR__ . '/../../../config/stylelint/.stylelintrc',
                __DIR__ . '/../fixtures/stylelint/GoodCode.scss',
            ]
        );

        yield $process->start();
        $exitCode = yield $process->join();

        self::assertSame(0, $exitCode);
    }

    /**
     * @test
     * @large
     * @coversNothing
     *
     * @return array<int,Promise>
     */
    public function findViolationsByEslintInSass(): iterable
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
        MatcherAssert::assertThat($output, H::containsString('Expected nesting depth to be no more than 5'));
    }

    /**
     * @test
     * @large
     * @coversNothing
     *
     * @return array<int,Promise>
     */
    public function findViolationsByEslintInCss(): iterable
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
