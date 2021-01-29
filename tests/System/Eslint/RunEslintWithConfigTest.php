<?php

namespace Zooroyal\CodingStandard\Tests\System\Eslint;

use Amp\Success;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Amp\PHPUnit\AsyncTestCase;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\Tests\Tools\TestEnvironmentInstallation;
use function Amp\call;

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
    public function runEslintForJSInCleanInstall()
    {
        $testInstancePath = $this->prepareInstallationDirectory();

        $command = $this->getEslintCommand(
            'vendor/zooroyal/coding-standard/tests/System/fixtures/eslint/BadCode.js',
            $testInstancePath
        );
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $testInstancePath);

        yield call([$process, 'run']);

        $exitCode = $process->getExitCode();
        $output = $process->getOutput();

        self::assertSame(1, $exitCode, $process->getErrorOutput());

        MatcherAssert::assertThat($output, H::containsString(self::EXPECTED_JS_PROBLEMS));
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

    /**
     * @test
     * @large
     */
    public function runEslintForTSInCleanInstall()
    {
        $testInstancePath = $this->prepareInstallationDirectory();

        $command = $this->getEslintCommand(
            'vendor/zooroyal/coding-standard/tests/System/fixtures/eslint/BadCode.ts',
            $testInstancePath
        );
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $testInstancePath);

        yield call([$process, 'run']);

        $exitCode = $process->getExitCode();
        $output = $process->getOutput();

        self::assertSame(1, $exitCode, $process->getErrorOutput());

        MatcherAssert::assertThat($output, H::containsString(self::EXPECTED_TS_PROBLEMS));
    }

    /**
     * @test
     * @large
     */
    public function runStylelintInCleanInstall()
    {
        $testInstancePath = $this->prepareInstallationDirectory();

        $command = 'vendor/bin/coding-standard sca:stylelint';
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $testInstancePath);

        yield call([$process, 'run']);

        $exitCode = $process->getExitCode();

        self::assertSame(0, $exitCode);
    }
}
