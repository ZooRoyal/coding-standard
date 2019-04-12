<?php

namespace Zooroyal\CodingStandard\Tests\Functional\Plugin;

use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Zooroyal\CodingStandard\Plugin\Plugin;

class PluginTest extends TestCase
{
    /** @var Plugin */
    private $subject;
    /** @var string[] */
    private $composerArtefactPaths = [
        __DIR__ . '/Fixtures/ComposerTest/vendor/',
        __DIR__ . '/Fixtures/ComposerTest/composer.lock',
    ];

    protected function setUp()
    {
        $this->subject = new Plugin();
    }

    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->composerArtefactPaths);
        Mockery::close();
    }

    /**
     * @test
     * @large
     */
    public function composerInstallRunsSmoothly()
    {
        $process = new Process('composer install', __DIR__ . '/Fixtures/ComposerTest/');
        $process->mustRun();
        $exitCode = $process->getExitCode();

        self::assertSame(0, $exitCode);
    }
}
