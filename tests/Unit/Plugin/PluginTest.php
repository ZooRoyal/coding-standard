<?php
namespace Zooroyal\CodingStandard\Tests\Unit\Plugin;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Script\ScriptEvents;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\Plugin\Plugin;

class PluginTest extends TestCase
{
    /** @var Plugin */
    private $subject;

    protected function setUp()
    {
        $this->subject = new Plugin();
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testGetSubscribedEvents()
    {
        $result = Plugin::getSubscribedEvents();

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::hasKeyValuePair(
                    ScriptEvents::POST_INSTALL_CMD,
                    H::hasItem(
                        H::hasKeyValuePair('onInstallCommand', 0)
                    )
                ),
                H::hasKeyValuePair(
                    ScriptEvents::POST_UPDATE_CMD,
                    H::hasItem(
                        H::hasKeyValuePair('onUpdateCommand', 0)
                    )
                )
            )
        );
    }

    public function testActivateCalledWithoutErrors()
    {
        list($mockedComposer, $mockedIO) = $this->prepareMocksForActivate();

        $this->subject->activate($mockedComposer, $mockedIO);
    }

    public function npmInstallDataProvider()
    {
        return [
            'non-verbose'  => ['processOutput', 'npm', false, false, 0, 0],
            'verbose'      => ['processOutput', 'npm', true, false, 1, 0],
            'very verbose' => ['processOutput', 'npm', true, true, 1, 1],
        ];
    }

    /**
     * @dataProvider npmInstallDataProvider
     *
     * @param string $processOutput
     * @param string $processCommandLine
     * @param bool   $isVerbose
     * @param bool   $isVeryVerbose
     * @param int    $writeVerboseCount
     * @param int    $writeVeryVerboseCount
     */
    public function testNpmInstall(
        $processOutput,
        $processCommandLine,
        $isVerbose,
        $isVeryVerbose,
        $writeVerboseCount,
        $writeVeryVerboseCount
    ) {
        /** @var MockInterface|Process $mockedProcess */
        $mockedProcess = Mockery::mock(Process::class);

        /** @var MockInterface|Composer $mockedComposer */
        /** @var MockInterface|IOInterface $mockedIO */
        list($mockedComposer, $mockedIO) = $this->prepareMocksForActivate();

        $this->prepareMockedProcessForNpmInstall(
            $processOutput,
            $processCommandLine,
            $writeVeryVerboseCount,
            $mockedProcess
        );
        $this->prepareMockedIoForNpmInstall(
            $processCommandLine,
            $processOutput,
            $isVerbose,
            $isVeryVerbose,
            $writeVerboseCount,
            $writeVeryVerboseCount,
            $mockedIO
        );

        $this->subject->activate($mockedComposer, $mockedIO);
        $this->subject->overwriteProcess($mockedProcess);

        $this->subject->npmInstall();
    }

    /**
     * Prepares Mocks to call activate method
     *
     * @return array
     */
    private function prepareMocksForActivate()
    {
        /** @var MockInterface|Composer $mockedComposer */
        $mockedComposer = Mockery::mock(Composer::class);
        /** @var MockInterface|IOInterface $mockedIO */
        $mockedIO = Mockery::mock(IOInterface::class);
        /** @var MockInterface|Config $mockedConfiguration */
        $mockedConfiguration = Mockery::mock(Config::class);

        $mockedComposer->shouldReceive('getConfig')->once()
            ->withNoArgs()->andReturn($mockedConfiguration);
        $mockedConfiguration->shouldReceive('get')->once()
            ->with('vendor-dir')->andReturn('vendor-dir');

        return [$mockedComposer, $mockedIO];
    }

    /**
     * Prepares $mockedIO for testNpmInstall
     *
     * @param string                    $processCommandLine
     * @param string                    $processOutput
     * @param bool                      $isVerbose
     * @param bool                      $isVeryVerbose
     * @param int                       $writeVerboseCount
     * @param int                       $writeVeryVerboseCount
     * @param MockInterface|IOInterface $mockedIO
     */
    private function prepareMockedIoForNpmInstall(
        $processCommandLine,
        $processOutput,
        $isVerbose,
        $isVeryVerbose,
        $writeVerboseCount,
        $writeVeryVerboseCount,
        $mockedIO
    ) {
        $expectedVerboseWrite       = sprintf('<info>%s</info>', 'Installing NPM-Packages for Coding-Standard');
        $expectedVeryVerboseWrite   = sprintf('Executed Command: <info>%s</info>', $processCommandLine);
        $expectedVerboseOutputWrite = sprintf('<info>%s</info>', $processOutput);
        $expectedWrite              = '<info>NPM packages for zooroyal/coding-standard installed</info>';

        $mockedIO->shouldReceive('isVeryVerbose')
            ->andReturn($isVeryVerbose);
        $mockedIO->shouldReceive('write')->times($writeVerboseCount)
            ->with($expectedVerboseWrite);
        $mockedIO->shouldReceive('write')->times($writeVeryVerboseCount)
            ->with($expectedVeryVerboseWrite);
        $mockedIO->shouldReceive('write')->times($writeVerboseCount)
            ->with($expectedVerboseOutputWrite);
        $mockedIO->shouldReceive('write')->once()
            ->with($expectedWrite);
        $mockedIO->shouldReceive('isVerbose')
            ->andReturn($isVerbose);
    }

    /**
     * Prepares $mockedProcess for testNpmInstall
     *
     * @param string                $processOutput
     * @param string                $processCommandLine
     * @param bool                  $writeVeryVerboseCount
     * @param MockInterface|Process $mockedProcess
     */
    private function prepareMockedProcessForNpmInstall(
        $processOutput,
        $processCommandLine,
        $writeVeryVerboseCount,
        $mockedProcess
    ) {
        $mockedProcess->shouldReceive('mustRun')->once()
            ->withNoArgs()->andReturnSelf();
        $mockedProcess->shouldReceive('getOutput')->once()
            ->withNoArgs()->andReturn($processOutput);
        $mockedProcess->shouldReceive('getCommandLine')->times($writeVeryVerboseCount)
            ->withNoArgs()->andReturn($processCommandLine);
    }
}
