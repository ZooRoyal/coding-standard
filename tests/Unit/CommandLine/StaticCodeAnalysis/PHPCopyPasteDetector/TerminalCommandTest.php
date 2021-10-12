<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector;

use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector\TerminalCommand;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use Zooroyal\CodingStandard\Tests\Tools\TerminalCommandTestData;

class TerminalCommandTest extends TestCase
{
    private const FORGED_PACKAGE_DIRECTORY = '/packageDirectory';
    private const FORGED_RELATIV_ROOT = '.';
    private const FORGED_ABSOLUTE_ROOT = '/RootDirectory';
    private const FORGED_ABSOLUTE_VENDOR = '/vendor';
    private TerminalCommand $subject;
    /** @var MockInterface|Environment */
    private Environment $mockedEnvironment;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;
    /** @var MockInterface|ProcessRunner */
    private ProcessRunner $mockedProcessRunner;

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedProcessRunner = Mockery::mock(ProcessRunner::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEnvironment->shouldReceive('getPackageDirectory->getRealPath')
            ->andReturn(self::FORGED_PACKAGE_DIRECTORY);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRelativePathname')
            ->andReturn(self::FORGED_RELATIV_ROOT);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRealPath')
            ->andReturn(self::FORGED_ABSOLUTE_ROOT);
        $this->mockedEnvironment->shouldReceive('getVendorPath->getRealPath')
            ->andReturn(self::FORGED_ABSOLUTE_VENDOR);

        $this->subject = new TerminalCommand($this->mockedEnvironment, $this->mockedProcessRunner);
        $this->subject->injectDependenciesAbstractTerminalCommand($this->mockedOutput);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     * @dataProvider terminalCommandCompilationDataProvider
     */
    public function terminalCommandCompilation(TerminalCommandTestData $data): void
    {
        $this->mockedOutput->shouldReceive('writeln')->once()
            ->with(
                Matchers::startsWith(
                    '<info>Compiled TerminalCommand to following string</info>'
                    . PHP_EOL . $data->getExpectedCommand()
                ),
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with(
                'find ' . self::FORGED_RELATIV_ROOT . ' -path \'' . self::FORGED_RELATIV_ROOT
                . '/custom/plugins/*\' -name Installer.php -maxdepth 4'
            )
            ->andReturn('blabla/Installer.php' . PHP_EOL . 'blubblub/Installer.php' . PHP_EOL);

        $this->subject->addAllowedFileExtensions($data->getExtensions());
        $this->subject->addExclusions($data->getExcluded());

        $result = (string) $this->subject;
        $resultingArray = $this->subject->toArray();

        self::assertSame($data->getExpectedCommand(), $result);
        self::assertSame($result, implode(' ', $resultingArray));
    }

    /**
     * This data provider needs to be long because it contains all testing data.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array<string,array<int,TerminalCommandTestData>>
     */
    public function terminalCommandCompilationDataProvider(): array
    {
        return [
            'all' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcpd --fuzzy --suffix qweasd --suffix argh --exclude a/ --exclude b/ '
                            . '--exclude custom/plugins/ZRBannerSlider/ZRBannerSlider.php '
                            . '--exclude custom/plugins/ZRPreventShipping/ZRPreventShipping.php --exclude blabla/Installer.php '
                            . '--exclude blubblub/Installer.php .',
                        'excluded' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/a', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/b', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                        'extensions' => ['qweasd', 'argh'],
                    ]
                ),
            ],
            'empty optionals' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcpd --fuzzy --exclude custom/plugins/ZRBannerSlider/ZRBannerSlider.php '
                            . '--exclude custom/plugins/ZRPreventShipping/ZRPreventShipping.php '
                            . '--exclude blabla/Installer.php --exclude blubblub/Installer.php .',
                    ]
                ),
            ],
            'excluding' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcpd --fuzzy --exclude a/ --exclude b/ '
                            . '--exclude custom/plugins/ZRBannerSlider/ZRBannerSlider.php '
                            . '--exclude custom/plugins/ZRPreventShipping/ZRPreventShipping.php --exclude blabla/Installer.php '
                            . '--exclude blubblub/Installer.php .',
                        'excluded' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/a', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/b', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                    ]
                ),
            ],
            'extensions' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcpd --fuzzy --suffix argh --suffix wub '
                            . '--exclude custom/plugins/ZRBannerSlider/ZRBannerSlider.php '
                            . '--exclude custom/plugins/ZRPreventShipping/ZRPreventShipping.php --exclude blabla/Installer.php '
                            . '--exclude blubblub/Installer.php .',
                        'extensions' => ['argh', 'wub'],
                    ]
                ),
            ],
        ];
    }
}
