<?php

namespace Zooroyal\CodingStandard\Tests\System\Eslint;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Process\Process;
use Generator;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Zooroyal\CodingStandard\Tests\Tools\TestEnvironmentInstallation;
use function Amp\ByteStream\buffer;

class RunEslintWithConfigTest extends AsyncTestCase
{
    private const EXPECTED_TS_PROBLEMS = '179 problems';
    private const EXPECTED_JS_PROBLEMS = '178 problems';
    private const ESLINT_COMMAND = 'npx --no-install eslint --config ';
    private const ESLINT_CONFIG_FILE = 'vendor/zooroyal/coding-standard/config/eslint/.eslintrc.js ';

    public static function tearDownAfterClass(): void
    {
        TestEnvironmentInstallation::getInstance()->removeInstallation();
    }

    /**
     * @test
     * @large
     */
    public function runEslintForJSInCleanInstall(): ?Generator
    {
        $testInstancePath = $this->prepareInstallationDirectory();

        $command = $this->getEslintCommand(
            'vendor/zooroyal/coding-standard/tests/System/fixtures/eslint/BadCode.js',
            $testInstancePath
        );
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $testInstancePath);

        yield $process->start();

        $output = yield buffer($process->getStdout());
        $errorOutput = yield buffer($process->getStdout());
        $exitCode = yield $process->join();

        self::assertSame(1, $exitCode, $errorOutput);

        MatcherAssert::assertThat($output, H::containsString(self::EXPECTED_JS_PROBLEMS));
    }

    /**
     * @test
     * @large
     */
    public function runEslintForTSInCleanInstall(): ?Generator
    {
        $testInstancePath = $this->prepareInstallationDirectory();

        $command = $this->getEslintCommand(
            'vendor/zooroyal/coding-standard/tests/System/fixtures/eslint/BadCode.ts',
            $testInstancePath
        );
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $testInstancePath);

        yield $process->start();

        $output = yield buffer($process->getStdout());
        $errorOutput = yield buffer($process->getStdout());
        $exitCode = yield $process->join();

        self::assertSame(1, $exitCode, $errorOutput);

        MatcherAssert::assertThat($output, H::containsString(self::EXPECTED_TS_PROBLEMS));
    }

    /**
     * @test
     * @large
     */
    public function runStylelintInCleanInstall(): ?Generator
    {
        $testInstancePath = $this->prepareInstallationDirectory();

        $command = 'vendor/bin/coding-standard sca:stylelint';
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $testInstancePath);

        yield $process->start();

        $exitCode = yield $process->join();

        self::assertSame(0, $exitCode);
    }

    /**
     * Provides an composer environment to run tests on.
     *
     * @return string
     */
    private function prepareInstallationDirectory(): string
    {
        $environment = TestEnvironmentInstallation::getInstance();
        if ($environment->isInstalled() === false) {
            $environment->addComposerJson(
                dirname(__DIR__)
                . '/fixtures/eslint/composer-template.json'
            )->installComposerInstance();
        }
        return $environment->getInstallationPath();
    }

    private function getEslintCommand($fileToCheck, $testInstancePath): string
    {
        return self::ESLINT_COMMAND
            . $testInstancePath . DIRECTORY_SEPARATOR
            . self::ESLINT_CONFIG_FILE
            . $testInstancePath . DIRECTORY_SEPARATOR
            . $fileToCheck;
    }
}
