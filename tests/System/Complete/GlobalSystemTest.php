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

        $result = yield from $this->runTools($environmentDirectory);

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
        $badPhpSnifferFilePath = dirname(__DIR__, 2)
            . '/Functional/PHPCodesniffer/Standards/ZooRoyal/Sniffs/Commenting/'
            . 'Fixtures/FixtureIncorrectComments.php';

        $this->filesystem->mkdir($badCodeDirectory);

        $copyFiles = [
            [$fixtureDirectory . '/complete/GoodPhp.php', $environmentDirectory . '/GoodPhp.php'],
            [$fixtureDirectory . '/eslint/BadCode.ts', $badCodeDirectory . '/BadCode.ts'],
            [$fixtureDirectory . '/stylelint/BadCode.less', $badCodeDirectory . '/BadCode.less'],
            [$badPhpSnifferFilePath, $badCodeDirectory . '/BadSniffer.php'],
            [$fixtureDirectory . '/BadCopyPasteDetect.php', $badCodeDirectory . '/BadCopyPasteDetect1.php'],
            [$fixtureDirectory . '/BadCopyPasteDetect.php', $badCodeDirectory . '/BadCopyPasteDetect2.php'],
            [$fixtureDirectory . '/complete/BadStan.php', $badCodeDirectory . '/BadStan.php'],
            [$fixtureDirectory . '/complete/badLint.php', $badCodeDirectory . '/badLint.php'],
            [$fixtureDirectory . '/complete/BadMessDetect.php', $badCodeDirectory . '/BadMessDetect.php'],
        ];

        foreach ($copyFiles as $copyFile) {
            yield call([$this->filesystem, 'copy'], $copyFile[0], $copyFile[1]);
        }

        $result = yield from $this->runTools($environmentDirectory);

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
                . '/fixtures/complete/composer-template.json'
            )->installComposerInstance();
        }
        return $environment->getInstallationPath();
    }

    /**
     * Run all available coding-standard tools in $environmentDirectory and returns promises for use in Amp.
     *
     * @return Generator<Promise>
     */
    private function runTools(string $environmentDirectory): Generator
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

        $processes = [];
        foreach ($tools as $tool) {
            $processes[$tool] = new Process(
                [$environmentDirectory . '/vendor/bin/coding-standard', $tool],
                $environmentDirectory
            );
        }

        $startPromises = array_map(static fn(Process $process) => $process->start(), $processes);
        yield all($startPromises);

        $endPromises = array_map(static fn(Process $process) => $process->join(), $processes);
        $exitCodes = yield all($endPromises);

        return yield new Success($exitCodes);
    }
}
