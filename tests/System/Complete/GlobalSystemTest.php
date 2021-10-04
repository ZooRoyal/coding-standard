<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\System\Complete;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Process\Process;
use Closure;
use Generator;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Symfony\Component\Filesystem\Filesystem;
use Zooroyal\CodingStandard\Tests\Tools\TestEnvironmentInstallation;
use function Amp\call;

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
            [__FILE__, $badCodeDirectory . '/BadCopyPasteDetect1.php'],
            [__FILE__, $badCodeDirectory . '/BadCopyPasteDetect2.php'],
            [$fixtureDirectory . '/complete/BadStan.php', $badCodeDirectory . '/BadStan.php'],
            [$fixtureDirectory . '/complete/badLint.php', $badCodeDirectory . '/badLint.php'],
            [$fixtureDirectory . '/complete/BadMessDectect.php', $badCodeDirectory . '/BadMessDetect.php'],
        ];

        foreach ($copyFiles as $copyFile) {
            $copyPromises[] = call([$this->filesystem, 'copy'], $copyFile[0], $copyFile[1]);
        }

        yield $copyPromises;

        $result = yield call(Closure::fromCallable([$this, 'runTools']), $environmentDirectory);

        MatcherAssert::assertThat('All tools are not satisfied', $result, H::not(H::hasItems(0)));
    }

    /**
     * @test
     *
     * @large
     * @depends runCodingStandardToFindErrors
     *
     * @coversNothing
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
            $promises = call([$this->filesystem, 'dumpFile'], $badCodeDirectory . DIRECTORY_SEPARATOR . $dotFile, '');
        }

        yield $promises;

        $result = yield call(Closure::fromCallable([$this, 'runTools']), $environmentDirectory);

        MatcherAssert::assertThat('All Tools are satisfied.', $result, H::not(H::hasItems(H::greaterThan(0))));
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
     * Runs all coding-standard commands in test environment.
     *
     * @return Generator|array<int|null>
     */
    // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
    private function runTools(string $environmentDirectory): iterable
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

        foreach ($tools as $tool) {
            $promises[$tool] = call(Closure::fromCallable([$this, 'runAndGetExitCode']), $environmentDirectory, $tool);
        }

        $exitCodes = yield $promises;

        $result = [];
        foreach ($exitCodes as $tool => $ExitCode) {
            $result[$tool] = $ExitCode;
        }

        return $result;
    }

    /**
     * Runs a coding-standard command in test environment
     *
     * @return Generator|int
     */
    // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
    private function runAndGetExitCode(string $environmentDirectory, string $command)
    {
        $process = new Process(
            [$environmentDirectory . '/vendor/bin/coding-standard', $command],
            $environmentDirectory
        );
        yield $process->start();

        return yield $process->join();
    }
}
