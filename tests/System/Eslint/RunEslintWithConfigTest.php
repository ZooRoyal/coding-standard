<?php

namespace Zooroyal\CodingStandard\Tests\System\Eslint;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\Tests\Tools\TestEnvironmentInstallation;

class RunEslintWithConfigTest extends TestCase
{
    private const EXPECTED_TS_PROBLEMS = '179 problems';
    private const EXPECTED_JS_PROBLEMS = '178 problems';
    private const ESLINT_COMMAND = 'npx --no-install eslint --config ';
    private const ESLINT_CONFIG_FILE = 'vendor/zooroyal/coding-standard/config/eslint/.eslintrc.js ';
    private $testInstancePath;

    public static function setUpBeforeClass(): void
    {
        $environment = TestEnvironmentInstallation::getInstance();
        if ($environment->isInstalled() === false) {
            $environment->addComposerJson(
                dirname(__DIR__)
                . '/fixtures/eslint/composer-template.json'
            )->installComposerInstance();
        }
    }

    public static function tearDownAfterClass(): void
    {
        TestEnvironmentInstallation::getInstance()->removeInstallation();
    }

    public function setUp(): void
    {
        $this->testInstancePath = TestEnvironmentInstallation::getInstance()->getInstallationPath();
    }

    /**
     * @test
     */
    public function runEslintForJSInCleanInstall()
    {
        $command = $this->getEslintCommand('vendor/zooroyal/coding-standard/tests/System/fixtures/eslint/BadCode.js');
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $this->testInstancePath);

        $process->run();

        $exitCode = $process->getExitCode();
        $output = $process->getOutput();

        self::assertSame(1, $exitCode, $process->getErrorOutput());

        MatcherAssert::assertThat($output, H::containsString(self::EXPECTED_JS_PROBLEMS));
    }

    /**
     * @test
     */
    public function runEslintForTSInCleanInstall()
    {
        $command = $this->getEslintCommand('vendor/zooroyal/coding-standard/tests/System/fixtures/eslint/BadCode.ts');
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $this->testInstancePath);

        $process->run();

        $exitCode = $process->getExitCode();
        $output = $process->getOutput();

        self::assertSame(1, $exitCode, $process->getErrorOutput());

        MatcherAssert::assertThat($output, H::containsString(self::EXPECTED_TS_PROBLEMS));
    }

    /**
     * @test
     */
    public function runStylelintInCleanInstall()
    {
        $command = 'vendor/bin/coding-standard sca:stylelint';
        $commandArray = explode(' ', $command);
        $process = new Process($commandArray, $this->testInstancePath);

        $process->run();

        $exitCode = $process->getExitCode();

        self::assertSame(0, $exitCode);
    }

    private function getEslintCommand($fileToCheck): string
    {
        return self::ESLINT_COMMAND
            . $this->testInstancePath . DIRECTORY_SEPARATOR
            . self::ESLINT_CONFIG_FILE
            . $this->testInstancePath . DIRECTORY_SEPARATOR
            . $fileToCheck;
    }
}
