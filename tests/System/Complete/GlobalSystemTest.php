<?php

namespace Zooroyal\CodingStandard\Tests\System\Complete;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\Tests\Tools\TestEnvironmentInstallation;

class GlobalSystemTest extends TestCase
{
    private Filesystem $filesystem;

    public function setUp(): void
    {
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
     * @runInSeparateProcessa
     * @preserveGlobalStatea  disabled
     */
    public function runCodingStandardToFindErrors(): void
    {
        $environmentDirectory = $this->prepareInstallationDirectory();

        $fixtureDirectory = dirname(__DIR__) . '/fixtures';
        $badCodeDirectory = $environmentDirectory . DIRECTORY_SEPARATOR . 'BadCode';
        $badPhpSnifferFilePath = dirname(__DIR__, 2)
            . '/Functional/PHPCodesniffer/Standards/ZooRoyal/Sniffs/Commenting/'
            . 'Fixtures/FixtureIncorrectComments.php';

        $this->filesystem->mkdir($badCodeDirectory);
        $this->filesystem->copy($fixtureDirectory . '/complete/GoodPhp.php', $environmentDirectory . '/GoodPhp.php');
        $this->filesystem->copy($fixtureDirectory . '/eslint/BadCode.ts', $badCodeDirectory . '/BadCode.ts');
        $this->filesystem->copy($fixtureDirectory . '/stylelint/BadCode.less', $badCodeDirectory . '/BadCode.less');
        $this->filesystem->copy($badPhpSnifferFilePath, $badCodeDirectory . '/BadSniffer.php');
        $this->filesystem->copy(__FILE__, $badCodeDirectory . '/BadCopyPasteDetect1.php');
        $this->filesystem->copy(__FILE__, $badCodeDirectory . '/BadCopyPasteDetect2.php');
        $this->filesystem->copy($fixtureDirectory . '/complete/BadStan.php', $badCodeDirectory . '/BadStan.php');
        $this->filesystem->copy($fixtureDirectory . '/complete/badLint.php', $badCodeDirectory . '/badLint.php');
        $this->filesystem->copy(
            $fixtureDirectory . '/complete/BadMessDectect.php',
            $badCodeDirectory . '/BadMessDetect.php'
        );

        $result = $this->runTools($environmentDirectory);

        MatcherAssert::assertThat('All tools are not satisfied', $result, H::not(H::hasItems(0)));
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
                . '/fixtures/complete/composer-template.json'
            )->installComposerInstance();
        }
        return $environment->getInstallationPath();
    }

    /**
     * Runs all coding-standard commands in test environment.
     *
     * @param string $environmentDirectory
     *
     * @return array<int|null>
     */
    protected function runTools(string $environmentDirectory): array
    {
        $phpCsExitCode = $this->runAndGetExitCode($environmentDirectory, 'sca:sniff');
        $phpMdExitCode = $this->runAndGetExitCode($environmentDirectory, 'sca:mess');
        $phpLintExitCode = $this->runAndGetExitCode($environmentDirectory, 'sca:para');
        $phpCpdExitCode = $this->runAndGetExitCode($environmentDirectory, 'sca:copy');
        $phpStanExitCode = $this->runAndGetExitCode($environmentDirectory, 'sca:stan');
        $lessStyleExitCode = $this->runAndGetExitCode($environmentDirectory, 'sca:style');
        $jsEsLintExitCode = $this->runAndGetExitCode($environmentDirectory, 'sca:eslint');
        return [
            'phpcs' => $phpCsExitCode,
            'phpmd' => $phpMdExitCode,
            'phpcpd' => $phpCpdExitCode,
            'phpstan' => $phpStanExitCode,
            'phplint' => $phpLintExitCode,
            'stylelint' => $lessStyleExitCode,
            'eslint' => $jsEsLintExitCode,
        ];
    }

    /**
     * Runs a coding-standard command in test environment
     *
     * @param string $environmentDirectory
     * @param string $command
     *
     * @return int|null
     */
    protected function runAndGetExitCode(string $environmentDirectory, string $command): ?int
    {
        $process = new Process(
            [$environmentDirectory . '/vendor/bin/coding-standard', $command],
            $environmentDirectory
        );
        $phpCsExitCode = $process->setTimeout(120)
            ->setIdleTimeout(60)
            ->run();
        return $phpCsExitCode;
    }

    /**
     * @test
     *
     * @large
     * @depends runCodingStandardToFindErrors
     */
    public function dontFilesMakeAllGood(): void
    {
        $environmentDirectory = $this->prepareInstallationDirectory();
        $badCodeDirectory = $environmentDirectory . DIRECTORY_SEPARATOR . 'BadCode';

        $this->filesystem->dumpFile($badCodeDirectory . DIRECTORY_SEPARATOR . '.dontSniffPHP', '');
        $this->filesystem->dumpFile($badCodeDirectory . DIRECTORY_SEPARATOR . '.dontMessDetectPHP', '');
        $this->filesystem->dumpFile($badCodeDirectory . DIRECTORY_SEPARATOR . '.dontCopyPasteDetectPHP', '');
        $this->filesystem->dumpFile($badCodeDirectory . DIRECTORY_SEPARATOR . '.dontLintPHP', '');
        $this->filesystem->dumpFile($badCodeDirectory . DIRECTORY_SEPARATOR . '.dontSniffLESS', '');
        $this->filesystem->dumpFile($badCodeDirectory . DIRECTORY_SEPARATOR . '.dontSniffJS', '');
        $this->filesystem->dumpFile($badCodeDirectory . DIRECTORY_SEPARATOR . '.dontStanPHP', '');

        $result = $this->runTools($environmentDirectory);

        MatcherAssert::assertThat('All Tools are satisfied', $result, H::everyItem(H::is(0)));
    }
}
