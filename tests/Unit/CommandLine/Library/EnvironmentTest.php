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

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsTrueIfCommitHashesAreEqual()
    {
        $mockedBranchName = 'my/mocked/branch';
        $mockedCommitHash = '123qwe0';

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git rev-list -n 1 "HEAD"')->andReturn($mockedCommitHash);
        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git rev-list -n 1 "' . $mockedBranchName . '"')->andReturn($mockedCommitHash);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToReturnsFalseIfCommitHashesAreUnequal()
    {
        $mockedBranchName      = 'my/mocked/branch';
        $mockedCommitHash      = '123qwe0';
        $mockedLocalCommitHash = '0ewq321';

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git rev-list -n 1 "HEAD"')->andReturn($mockedLocalCommitHash);
        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git rev-list -n 1 "' . $mockedBranchName . '"')->andReturn($mockedCommitHash);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isLocalBranchEqualToCachesLocalHeadHash()
    {
        $mockedBranchName      = 'my/mocked/branch';
        $mockedCommitHash      = '123qwe0';
        $mockedLocalCommitHash = '0ewq321';

        $this->mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git rev-list -n 1 "HEAD"')->andReturn($mockedLocalCommitHash);
        $this->mockedProcessRunner->shouldReceive('runAsProcess')->twice()
            ->with('git rev-list -n 1 "' . $mockedBranchName . '"')->andReturn($mockedCommitHash);

        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        $result = $this->subject->isLocalBranchEqualTo($mockedBranchName);
        self::assertFalse($result);
    }
}
