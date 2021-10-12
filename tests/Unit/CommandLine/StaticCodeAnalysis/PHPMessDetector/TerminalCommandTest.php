<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\PHPMessDetector;

use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\NoUsefulCommandFoundException;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPMessDetector\TerminalCommand;
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

    protected function setUp(): void
    {
        $this->mockedEnvironment = Mockery::mock(Environment::class);
        $this->mockedOutput = Mockery::mock(OutputInterface::class);

        $this->mockedEnvironment->shouldReceive('getPackageDirectory->getRealPath')
            ->andReturn(self::FORGED_PACKAGE_DIRECTORY);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRelativePathname')
            ->andReturn(self::FORGED_RELATIV_ROOT);
        $this->mockedEnvironment->shouldReceive('getRootDirectory->getRealPath')
            ->andReturn(self::FORGED_ABSOLUTE_ROOT);
        $this->mockedEnvironment->shouldReceive('getVendorPath->getRealPath')
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

        $this->subject->addAllowedFileExtensions($data->getExtensions());
        $this->subject->addExclusions($data->getExcluded());
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
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR . '/bin/phpmd c,d text '
                            . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpmd/phpmd.xml --suffixes qweasd,argh --exclude a,b',
                        'excluded' => [$mockedEnhancedFileInfoExcluded1, $mockedEnhancedFileInfoExcluded1],
                        'extensions' => ['qweasd', 'argh'],
                        'targets' => [$mockedEnhancedFileInfoTarget1, $mockedEnhancedFileInfoTarget1],
                    ]
                ),
            ],
            'empty optionals' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR . '/bin/phpmd '
                            . self::FORGED_ABSOLUTE_ROOT . ' text ' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpmd/phpmd.xml',
                    ]
                ),
            ],
            'excluding' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR . '/bin/phpmd '
                            . self::FORGED_ABSOLUTE_ROOT . ' text ' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpmd/phpmd.xml --exclude a,b',
                        'excluded' => [$mockedEnhancedFileInfoExcluded2, $mockedEnhancedFileInfoExcluded2],
                    ]
                ),
            ],
            'extensions' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR . '/bin/phpmd '
                            . self::FORGED_ABSOLUTE_ROOT . ' text ' . self::FORGED_PACKAGE_DIRECTORY
                            . '/config/phpmd/phpmd.xml --suffixes asdqwe,qweasd',
                        'extensions' => ['asdqwe', 'qweasd'],
                    ]
                ),
            ],
            'targeted' => [
                new TerminalCommandTestData(
                    [
                        'expectedCommand' => 'php ' . self::FORGED_ABSOLUTE_VENDOR . '/bin/phpmd c,d text ' .
                            self::FORGED_PACKAGE_DIRECTORY . '/config/phpmd/phpmd.xml',
                        'targets' => [$mockedEnhancedFileInfoTarget2, $mockedEnhancedFileInfoTarget2],
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
