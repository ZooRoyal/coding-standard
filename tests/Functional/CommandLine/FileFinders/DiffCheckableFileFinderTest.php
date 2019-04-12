<?php

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\FileFinders;

use DI\Container;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Mockery;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\ProcessRunner;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\GitChangeSet;

class DiffCheckableFileFinderTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function findFiles()
    {
        $forgedRootDirectory = __DIR__ . '/fixtures';
        $forgedRawDiffUnfilteredString = 'topFolder/allowedChangesFile' . PHP_EOL
            . 'topFolder/folder/subFolder/allowedChangesFile' . PHP_EOL
            . 'topFolder/folder/subFolder/.doChangeFiles' . PHP_EOL
            . 'topFolder/folder/subFolder/finalFolder/disallowedChangesFile' . PHP_EOL
            . 'topFolder/folder/subFolder/finalFolder/.dontChangeFiles' . PHP_EOL
            . 'topFolder/folder/disallowedChangesFile' . PHP_EOL
            . 'topFolder/folder/.dontChangeFiles';

        $forgedFileSet = [
            'topFolder/allowedChangesFile',
            'topFolder/folder/subFolder/allowedChangesFile',
            'topFolder/folder/subFolder/.doChangeFiles',
        ];

        $targetBranch = 'myTarget';
        $container = $this->setUpMockedObjects($forgedRootDirectory, $targetBranch, $forgedRawDiffUnfilteredString);
        /** @var DiffCheckableFileFinder $subject */
        $subject = $container->get(DiffCheckableFileFinder::class);

        $result = $subject->findFiles(
            '',
            '.dontChangeFiles',
            '.doChangeFiles',
            $targetBranch
        );

        MatcherAssert::assertThat(
            $result,
            H::both(
                H::anInstanceOf(GitChangeSet::class)
            )->andAlso(
                HasProperty::hasProperty('files', H::arrayContainingInAnyOrder($forgedFileSet))
            )
        );
    }

    /**
     * Setup all mocked objects for test isolation.
     *
     * @param string $forgedRootDirectory
     * @param string $targetBranch
     * @param string $forgedRawDiffUnfilteredString
     *
     * @return Container
     */
    private function setUpMockedObjects(
        string $forgedRootDirectory,
        string $targetBranch,
        string $forgedRawDiffUnfilteredString
    ) : Container {
        $targetMergeBase = '123asdasdMergeBase123123asd';

        $mockedEnvironment = Mockery::mock(Environment::class);
        $mockedEnvironment->shouldReceive('getRootDirectory')
            ->withNoArgs()->andReturn($forgedRootDirectory);
        $mockedEnvironment->shouldReceive('getBlacklistedDirectories')
            ->withNoArgs()->andReturn(['.eslintrc.js', '.git', '.idea', '.vagrant', 'vendor']);

        $mockedProcessRunner = Mockery::mock(ProcessRunner::class);
        $mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git', 'merge-base', 'HEAD', $targetBranch)->andReturn($targetMergeBase);
        $mockedProcessRunner->shouldReceive('runAsProcess')->once()
            ->with('git', 'diff', '--name-only', '--diff-filter=d', $targetMergeBase)
            ->andReturn($forgedRawDiffUnfilteredString);

        $container = ContainerFactory::getUnboundContainerInstance();
        $container->set(Environment::class, $mockedEnvironment);
        $container->set(ProcessRunner::class, $mockedProcessRunner);
        return $container;
    }
}
