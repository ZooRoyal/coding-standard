<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPCodeSniffer;

use Hamcrest\Matchers;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\NoUsefulCommandFoundException;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCodeSniffer\TerminalCommand;
use Zooroyal\CodingStandard\Tests\Tools\TerminalCommandTestData;

class TerminalCommandTest extends TestCase
{
    private const FORGED_PACKAGE_DIRECTORY = '/packageDirectory';
    private const FORGED_RELATIV_ROOT = '.';
    private const FORGED_ABSOLUTE_VENDOR = '/vendor';

    private TerminalCommand $subject;
    /** @var MockInterface|Environment */
    private Environment $mockedEnvironment;
    /** @var MockInterface|OutputInterface */
    private OutputInterface $mockedOutput;

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEnvironment->shouldReceive('getPackageDirectory->getRealPath')
            ->andReturn(self::FORGED_PACKAGE_DIRECTORY);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRelativePathname')
            ->andReturn(self::FORGED_RELATIV_ROOT);
        $this->mockedEnvironment->shouldReceive('getVendorDirectory->getRealPath')
            ->andReturn(self::FORGED_ABSOLUTE_VENDOR);

        $this->subject = new TerminalCommand($this->mockedEnvironment);
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
                    . PHP_EOL . $data->getExpectedCommand(),
                ),
                OutputInterface::VERBOSITY_VERY_VERBOSE,
            );

        $this->subject->addAllowedFileExtensions($data->getExtensions());
        $this->subject->addExclusions($data->getExcluded());
        $this->subject->setFixingMode($data->isFixing());
        $this->subject->addVerbosityLevel($data->getVerbosityLevel());
        $this->subject->setMaximalConcurrentProcesses($data->getProcesses());
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
        $mockedEnhancedFileInfo1 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfo1->shouldReceive('getRealPath')
            ->andReturnValues(['a', 'b']);
        $mockedEnhancedFileInfo2 = Mockery::mock(EnhancedFileInfo::class);
        $mockedEnhancedFileInfo2->shouldReceive('getRealPath')
            ->andReturnValues(['a', 'b']);

        return [
            'all' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcbf -q --extensions=qweasd,argh --parallel=7 -p --standard='
                            . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml --ignore=a,b c d',
                        'excluded' => [$mockedEnhancedFileInfo1, $mockedEnhancedFileInfo1],
                        'extensions' => ['qweasd', 'argh'],
                        'fixingMode' => true,
                        'targets' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/c', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/d', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                        'verbosityLevel' => OutputInterface::VERBOSITY_QUIET,
                        'processes' => 7,
                    ],
                ),
            ],
            'empty optionals' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml ' . self::FORGED_RELATIV_ROOT,
                    ],
                ),
            ],
            'excluding' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml --ignore=a,b ' . self::FORGED_RELATIV_ROOT,
                        'excluded' => [$mockedEnhancedFileInfo2, $mockedEnhancedFileInfo2],
                    ],
                ),
            ],
            'extensions' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s --extensions=asdqwe,qweasd --parallel=1 -p --standard='
                            . self::FORGED_PACKAGE_DIRECTORY . '/config/phpcs/ZooRoyal/ruleset.xml '
                            . self::FORGED_RELATIV_ROOT,
                        'extensions' => ['asdqwe', 'qweasd'],
                    ],
                ),
            ],
            'fixing' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcbf --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml ' . self::FORGED_RELATIV_ROOT,
                        'fixingMode' => true,
                    ],
                ),
            ],
            'targeted' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml c d',
                        'targets' => [
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/c', self::FORGED_ABSOLUTE_VENDOR),
                            new EnhancedFileInfo(self::FORGED_ABSOLUTE_VENDOR . '/d', self::FORGED_ABSOLUTE_VENDOR),
                        ],
                    ],
                ),
            ],
            'verbosity quiet' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s -q --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml ' . self::FORGED_RELATIV_ROOT,
                        'verbosityLevel' => OutputInterface::VERBOSITY_QUIET,
                    ],
                ),
            ],
            'verbosity verbose' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s -v --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml ' . self::FORGED_RELATIV_ROOT,
                        'verbosityLevel' => OutputInterface::VERBOSITY_VERBOSE,
                    ],
                ),
            ],
            'verbosity very verbose' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s -vv --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml ' . self::FORGED_RELATIV_ROOT,
                        'fixingMode' => false,
                        'verbosityLevel' => OutputInterface::VERBOSITY_VERY_VERBOSE,
                    ],
                ),
            ],
            'verbosity debug verbose' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s -vvv --parallel=1 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml ' . self::FORGED_RELATIV_ROOT,
                        'verbosityLevel' => OutputInterface::VERBOSITY_DEBUG,
                    ],
                ),
            ],
            'processes' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR
                            . '/bin/phpcs -s --parallel=28 -p --standard=' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpcs/ZooRoyal/ruleset.xml ' . self::FORGED_RELATIV_ROOT,
                        'processes' => 28,
                    ],
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
