<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\System\Complete;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Process\Process;
use Amp\Promise;
use Amp\Success;
use Generator;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Symfony\Component\Filesystem\Filesystem;
use Zooroyal\CodingStandard\Tests\Tools\TestEnvironmentInstallation;

use function Amp\call;
use function Amp\Promise\all;
use function Amp\Promise\timeout;

class GlobalSystemTest extends AsyncTestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem();
    }

    public static function tearDownAfterClass(): void
    {
        TestEnvironmentInstallation::getInstance()->removeInstallation();
    }

    /**
     * @test
     *
     * @large
     * @coversNothing
     *
     * @depends runCodingStandardToFindErrors
     *
     * @return iterable<Promise>
     */
    public function dontFilesMakeAllGood(): iterable
    {
        $environmentDirectory = $this->prepareInstallationDirectory();
        $badCodeDirectory = $environmentDirectory . DIRECTORY_SEPARATOR . 'BadCode';

        $dotFiles = [
            '.dontSniffPHP',
            '.dontMessDetectPHP',
            '.dontCopyPasteDetectPHP',
            '.dontLintPHP',
            '.dontSniffLESS',
            '.dontSniffJS',
            '.dontStanPHP',
        ];

        foreach ($dotFiles as $dotFile) {
            yield call([$this->filesystem, 'dumpFile'], $badCodeDirectory . DIRECTORY_SEPARATOR . $dotFile, '');
        }

        $result = yield from $this->runTools($environmentDirectory, false);

        MatcherAssert::assertThat('All Tools are satisfied.', $result, H::not(H::hasItems(H::greaterThan(0))));
    }

    /**
     * @test
     *
     * @large
     * @coversNothing
     *
     * @return iterable<Promise<int>>
     */
    public function runCodingStandardToFindErrors(): iterable
    {
        $environmentDirectory = $this->prepareInstallationDirectory();

        $fixtureDirectory = dirname(__DIR__) . '/fixtures';
        $badCodeDirectory = $environmentDirectory . DIRECTORY_SEPARATOR . 'BadCode';
        $mockedPluginDirectory = $environmentDirectory . '/custom/plugins';
        $badPhpSnifferFilePath = dirname(__DIR__, 2)
            . '/Functional/Sniffs/PHPCodesniffer/Standards/ZooRoyal/Sniffs/Commenting/'
            . 'Fixtures/FixtureIncorrectComments.php';

        $this->filesystem->mkdir($badCodeDirectory);

        $copyFiles = [
            [
                $fixtureDirectory . '/complete/GoodPhp.php',
                $environmentDirectory . '/custom/plugins/blubblub/Installer.php',
            ],
            [
                $fixtureDirectory . '/complete/GoodPhp.php',
                $environmentDirectory . '/custom/plugins/blabla/Installer.php',
            ],
            [$fixtureDirectory . '/complete/GoodPhp.php', $environmentDirectory . '/GoodPhp.php'],
            [$fixtureDirectory . '/complete/GoodPhp2.php', $environmentDirectory . '/GoodPhp2.php'],
            [$fixtureDirectory . '/eslint/BadCode.ts', $badCodeDirectory . '/BadCode.ts'],
            [$fixtureDirectory . '/stylelint/BadCode.less', $badCodeDirectory . '/BadCode.less'],
            [$badPhpSnifferFilePath, $badCodeDirectory . '/BadSniffer.php'],
            [__FILE__, $badCodeDirectory . '/BadCopyPasteDetect1.php'],
            [__FILE__, $badCodeDirectory . '/BadCopyPasteDetect2.php'],
            [$fixtureDirectory . '/complete/Installer.php', $mockedPluginDirectory . '/a/Installer.php'],
            [$fixtureDirectory . '/complete/Installer2.php', $mockedPluginDirectory . '/b/Installer.php'],
            [$fixtureDirectory . '/complete/BadStan.php', $badCodeDirectory . '/BadStan.php'],
            [$fixtureDirectory . '/complete/badLint.php', $badCodeDirectory . '/badLint.php'],
            [$fixtureDirectory . '/complete/BadMessDetect.php', $badCodeDirectory . '/BadMessDetect.php'],
        ];

        foreach ($copyFiles as $copyFile) {
            yield call([$this->filesystem, 'copy'], $copyFile[0], $copyFile[1]);
        }

        $result = yield from $this->runTools($environmentDirectory, true);

        MatcherAssert::assertThat('All tools are not satisfied', $result, H::not(H::hasItems(0)));
    }

    /**
     * Provides an composer environment to run tests on.
     */
    private function prepareInstallationDirectory(): string
    {
        $environment = TestEnvironmentInstallation::getInstance();
        if ($environment->isInstalled() === false) {
            $environment->addComposerJson(
                dirname(__DIR__)
                . '/fixtures/complete/composer-template.json',
            )->installComposerInstance();
        }
        return $environment->getInstallationPath();
    }

    /**
     * Run all available coding-standard tools in $environmentDirectory and returns promises for use in Amp.
     *
     * @return Generator<Promise>
     */
    private function runTools(string $environmentDirectory, bool $errorsAreGood = false): Generator
    {
        $tools = [
            'sca:sniff',
            'sca:mess',
            'sca:para',
            'sca:copy',
            'sca:stan',
            'sca:style',
            'sca:eslint',
        ];

        /** @var array<Process> $processes */
        $processes = [];
        foreach ($tools as $tool) {
            $processes[$tool] = new Process(
                [$environmentDirectory . '/vendor/bin/coding-standard', $tool],
                $environmentDirectory,
            );
        }

        $startPromises = array_map(static fn(Process $process) => $process->start(), $processes);
        yield all($startPromises);

        $endPromises = array_map(static fn(Process $process) => $process->join(), $processes);
        $exitCodes = yield timeout(all($endPromises), 30000);

        foreach ($exitCodes as $tool => $exitCode) {
            if (($exitCode === 0) === $errorsAreGood) {
                $process = $processes[$tool];

                yield from $this->echoStream('Stderr', $process);
                yield from $this->echoStream('Stdout', $process);
            }
        }

        return yield new Success($exitCodes);
    }

    /**
     * Writes unexpected tool output to test log.
     */
    private function echoStream(string $streamName, Process $process): Generator
    {
        echo PHP_EOL . PHP_EOL . 'UNEXPECTED TOOL ' . TestEnvironmentInstallation::getInstance()->getInstallationPath()
            . ':' . $streamName . ':' . PHP_EOL;
        $buffer = '';
        $streamMethod = 'get' . $streamName;
        while (($chunk = yield $process->$streamMethod()->read()) !== null) {
            $buffer .= $chunk;
        }
        echo $buffer;
    }
}
