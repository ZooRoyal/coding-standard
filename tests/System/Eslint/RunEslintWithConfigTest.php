<?php

namespace Zooroyal\CodingStandard\Tests\System\Eslint;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class RunEslintWithConfigTest extends TestCase
{
    const EXPECTED_TS_PROBLEMS = '179 problems';
    const EXPECTED_JS_PROBLEMS = '178 problems';
    const ESLINT_COMMAND = 'npx --no-install eslint --config ';
    const ESLINT_CONFIG_FILE = 'vendor/zooroyal/coding-standard/config/eslint/.eslintrc.js ';

    /** @var string */
    private static $tempDir;

    /** @var Filesystem */
    private static $fileSystem;

    public static function setUpBeforeClass()
    {
        self::$fileSystem = new Filesystem();

        $dirname = random_int(74, 93485798397);
        self::$tempDir = sys_get_temp_dir() .DIRECTORY_SEPARATOR . $dirname;

        self::$fileSystem->mkdir(self::$tempDir);
        $composerPath = dirname(__DIR__, 3);
        $composerTemplateFile = dirname(__DIR__, 1) . '/fixtures/eslint/composer-template.json';
        $composerTemplate = json_decode(file_get_contents($composerTemplateFile), true);
        $composerTemplate['repositories'][0]['url'] = $composerPath;
        $renderedComposerFile = json_encode($composerTemplate);
        file_put_contents(self::$tempDir . DIRECTORY_SEPARATOR . 'composer.json', $renderedComposerFile);

        (new Process('composer install', self::$tempDir))->mustRun();
        self::$fileSystem->remove(self::$tempDir . '/vendor/zooroyal/coding-standard/node_modules');
        (new Process('npm install vendor/zooroyal/coding-standard', self::$tempDir))->mustRun();
    }

    public static function tearDownAfterClass()
    {
        self::$fileSystem->remove(self::$tempDir);
    }

    /**
     * @test
     */
    public function runEslintForJSInCleanInstall()
    {
        $command = $this->getEslintCommand('vendor/zooroyal/coding-standard/tests/System/fixtures/eslint/BadCode.js');

        $process = new Process($command, self::$tempDir);

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

        $process = new Process($command, self::$tempDir);

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

        $process = new Process($command, self::$tempDir);

        $process->run();

        $exitCode = $process->getExitCode();

        self::assertSame(0, $exitCode);
    }

    private function getEslintCommand($fileToCheck): string
    {
        return self::ESLINT_COMMAND
            .self::$tempDir . DIRECTORY_SEPARATOR
            .self::ESLINT_CONFIG_FILE
            .self::$tempDir . DIRECTORY_SEPARATOR
            .$fileToCheck;
    }
}
