<?php
namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;

class EnvironmentTest extends TestCase
{
    /** @var Environment */
    private $subject;
    /** @var MockInterface|ProcessRunner */
    private $mockedProcessRunner;
    /** @var string */
    private $rootDirectory;
    /** @var string */
    private $localBranch;
    /** @var string[] */
    private $blacklistedDirectories = [
        '.eslintrc.js',
        '.git',
        '.idea',
        '.vagrant',
        'node_modules',
        'vendor',
        'bower_components',
    ];

    protected function setUp()
    {
        $this->rootDirectory = '/my/root';
        $this->localBranch   = 'localBranch';

        $this->mockedProcessRunner = Mockery::mock(ProcessRunner::class);
        $this->subject             = new Environment($this->mockedProcessRunner);
    }

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getRootDirectory()
    {
        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git rev-parse --show-toplevel')->andReturn($this->rootDirectory);

        $this->subject->getRootDirectory();
        $result = $this->subject->getRootDirectory();

        self::assertSame($this->rootDirectory, $result);
    }

    /**
     * @test
     */
    public function getLocalBranch()
    {
        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git name-rev --exclude=tag\* --name-only HEAD')->andReturn($this->localBranch);

        $this->subject->getLocalBranch();
        $result = $this->subject->getLocalBranch();

        self::assertSame($this->localBranch, $result);
    }

    /**
     * @test
     */
    public function getPackageDirectory()
    {
        $result = $this->subject->getPackageDirectory();

        $expectedPath = realpath(
            __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
        );
        self::assertSame($expectedPath, $result);
    }

    /**
     * @test
     */
    public function getBlacklistedDirectories()
    {
        $result = $this->subject->getBlacklistedDirectories();

        self::assertSame($this->blacklistedDirectories, $result);
    }
}
