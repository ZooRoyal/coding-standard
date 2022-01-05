<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPStan;

use Hamcrest\Matchers;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\NoUsefulCommandFoundException;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan\PHPStanConfigGenerator;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Tools\TerminalCommandTestData;

class TerminalCommandTest extends TestCase
{
    private const FORGED_PACKAGE_DIRECTORY = '/packageDirectory';
    private const FORGED_RELATIV_ROOT = '.';
    private const FORGED_ABSOLUTE_ROOT = '/RootDirectory';
    private const FORGED_ABSOLUTE_VENDOR = '/vendor';
    private const FORGED_ABSOLUTE_CONFIG = '/configpath';
    private TerminalCommand $subject;
    /** @var MockInterface|Environment */
    private Environment $mockedEnvironment;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;
    /** @var MockInterface|PHPStanConfigGenerator */
    private $mockedConfigGenereator;

    protected function setUp(): void
    {
        $this->mockedConfigGenereator = Mockery::mock(PHPStanConfigGenerator::class);
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedConfigGenereator->shouldReceive('getConfigPath')->andReturn(self::FORGED_ABSOLUTE_CONFIG);

        $this->mockedEnvironment->shouldReceive('getPackageDirectory->getRealPath')
            ->andReturn(self::FORGED_PACKAGE_DIRECTORY);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRelativePathname')
            ->andReturn(self::FORGED_RELATIV_ROOT);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRealPath')
            ->andReturn(self::FORGED_ABSOLUTE_ROOT);
        $this->mockedEnvironment->shouldReceive('getVendorPath->getRealPath')
            ->andReturn(self::FORGED_ABSOLUTE_VENDOR);

        $this->subject = new TerminalCommand($this->mockedEnvironment, $this->mockedConfigGenereator);
        $this->subject->injectDependenciesAbstractTerminalCommand($this->mockedOutput);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }


    /**
     * @test
     */
    public function addInvalidVerbosityLevelThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1617802684);
        $this->expectExceptionMessage('Only verbosity settings from OutputInterface constants are allowed');
        $this->subject->addVerbosityLevel(99999);
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

        $this->mockedConfigGenereator->shouldReceive('writeConfigFile')->once()
            ->with($this->mockedOutput, $data->getExcluded());

        $this->subject->addExclusions($data->getExcluded());
        $this->subject->addVerbosityLevel($data->getVerbosityLevel());
        if ($data->getTargets() !== null) {
            $this->subject->addTargets($data->getTargets());
        }

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
        $mockedEnhancedFileInfoExcluded1 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfoExcluded1->shouldReceive('getRealPath')
            ->andReturnValues(['a', 'b']);
        $mockedEnhancedFileInfoExcluded2 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfoExcluded2->shouldReceive('getRealPath')
            ->andReturnValues(['a', 'b']);
        $mockedEnhancedFileInfoTarget1 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfoTarget1->shouldReceive('getRealPath')
            ->andReturnValues(['c', 'd']);
        $mockedEnhancedFileInfoTarget2 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfoTarget2->shouldReceive('getRealPath')
            ->andReturnValues(['c', 'd']);

        return [
            'all' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse -vv --no-progress --error-format=github -c /configpath c d',
                        'excluded' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/a', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/b', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                        'targets' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/c', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/d', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                        'verbosityLevel' => OutputInterface::VERBOSITY_VERY_VERBOSE,
                    ]
                ),
            ],
            'empty optionals' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse --no-progress --error-format=github -c /configpath .',
                    ]
                ),
            ],
            'excluding' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse --no-progress --error-format=github -c /configpath .',
                        'excluded' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/a', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/b', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                    ]
                ),
            ],
            'targeted' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse --no-progress --error-format=github -c /configpath c d',
                        'targets' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/c', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/d', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                    ]
                ),
            ],

            'verbosity quiet' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse -q --no-progress --error-format=github -c /configpath .',
                        'verbosityLevel' => OutputInterface::VERBOSITY_QUIET,
                    ]
                ),
            ],
            'verbosity verbose' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse -v --no-progress --error-format=github -c /configpath .',
                        'verbosityLevel' => OutputInterface::VERBOSITY_VERBOSE,
                    ]
                ),
            ],
            'verbosity very verbose' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse -vv --no-progress --error-format=github -c /configpath .',
                        'fixingMode' => false,
                        'verbosityLevel' => OutputInterface::VERBOSITY_VERY_VERBOSE,
                    ]
                ),
            ],
            'verbosity debug verbose' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpstan analyse -vvv --no-progress --error-format=github -c /configpath .',
                        'verbosityLevel' => OutputInterface::VERBOSITY_DEBUG,
                    ]
                ),
            ],
        ];
    }

    /**
     * @test
     */
    public function terminalCommandCompilationThrowsExceptionOnNoFilesToCheck(): void
    {
        $this->expectException(NoUsefulCommandFoundException::class);
        $this->expectExceptionCode(1620831304);
        $this->expectExceptionMessage('It makes no sense to sniff no files.');

        $this->subject->addTargets([]);

        $this->subject->__toString();
    }
}
