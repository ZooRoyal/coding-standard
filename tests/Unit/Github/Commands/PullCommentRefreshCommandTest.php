<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\Github\Commands;

use Github\Api\CurrentUser;
use Github\Api\PullRequest;
use Github\Client;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty as HP;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\Github\Commands\PullCommentRefreshCommand;
use Zooroyal\CodingStandard\Github\Library\CommentFilter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class PullCommentRefreshCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var array<MockInterface>|array<mixed> */
    private array $subjectParameters;

    private PullCommentRefreshCommand $subject;

    private MockInterface|InputInterface $mockedInputInterface;

    private MockInterface|OutputInterface $mockedOutputInterface;

    /** @var array<string,int|string> */
    private array $mockedArguments = [
        'token' => 'myToken',
        'user_name' => 'foobar',
        'organisation' => 'myOrganisation',
        'repository' => 'myRepository',
        'pullNumber' => 'myIssueId',
        'body' => 'myBody',
        'commitId' => 'myCommitId',
        'path' => 'myPath',
        'position' => 1,
    ];

    /** @var array<string,array<string,string>|int|string> */
    private array $mockedOwnCurrentComment;

    private string $mockedLogin = 'MyLogin';

    /** @var array<string,array<string,string>|int|string|null> */
    private array $mockedOwnStaleComment;

    protected function setUp(): void
    {
        $this->mockedOwnCurrentComment = [
            'id' => 25,
            'position' => $this->mockedArguments['position'],
            'original_position' => $this->mockedArguments['position'],
            'original_commit_id' => $this->mockedArguments['commitId'],
            'path' => $this->mockedArguments['path'],
            'user' => ['login' => $this->mockedLogin],
        ];
        $this->mockedOwnStaleComment = [
            'id' => 36,
            'position' => null,
            'original_position' => $this->mockedArguments['position'],
            'original_commit_id' => '123asd123',
            'path' => $this->mockedArguments['path'],
            'user' => ['login' => $this->mockedLogin],
        ];

        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(PullCommentRefreshCommand::class);
        $this->subject = $buildFragments['subject'];
        $this->subjectParameters = $buildFragments['parameters'];

        $this->mockedInputInterface = Mockery::mock(InputInterface::class);
        $this->mockedOutputInterface = Mockery::mock(OutputInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function configure(): void
    {
        /** @var MockInterface|FindFilesToCheckCommand $localSubject */
        $localSubject = Mockery::mock(PullCommentRefreshCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('pull:comment:refresh')->andReturnSelf();
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Updates a comment to a file in Github pull requests. Creates it if it does not exist.')->andReturnSelf();
        $localSubject->shouldReceive('setDefinition')->once()
            ->with(
                H::allOf(
                    H::anInstanceOf(InputDefinition::class),
                    HP::hasProperty(
                        'arguments',
                        H::allOf(
                            H::hasItems(
                                HP::hasProperty('name', 'token'),
                                HP::hasProperty('name', 'user_name'),
                                HP::hasProperty('name', 'organisation'),
                                HP::hasProperty('name', 'repository'),
                                HP::hasProperty('name', 'pullNumber'),
                                HP::hasProperty('name', 'commitId'),
                                HP::hasProperty('name', 'body'),
                                HP::hasProperty('name', 'path'),
                                HP::hasProperty('name', 'position')
                            )
                        )
                    )
                )
            )->andReturnSelf();

        $localSubject->configure();
    }

    /**
     * @test
     */
    public function executePassesDataToClientForUpdate(): void
    {
        foreach ($this->mockedArguments as $key => $value) {
            $this->mockedInputInterface->shouldReceive('getArgument')
                ->with($key)->andReturn($value);
        }

        $this->mockedInputInterface->shouldReceive('getArguments')
            ->withNoArgs()->andReturn($this->mockedArguments);

        $this->prepareMocksForFiltering(
            [
                $this->mockedOwnStaleComment,
            ],
            [
                'body' => $this->mockedArguments['body'],
            ],
            [
                'remove' => 1,
                'update' => 1,
            ]
        );
        $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);
    }

    /**
     * @test
     */
    public function executePassesDataToClientForCreate(): void
    {
        foreach ($this->mockedArguments as $key => $value) {
            $this->mockedInputInterface->shouldReceive('getArgument')
                ->with($key)->andReturn($value);
        }
        $this->mockedInputInterface->shouldReceive('getArguments')
            ->withNoArgs()->andReturn($this->mockedArguments);

        $this->prepareMocksForFiltering(
            [
                $this->mockedOwnStaleComment,
                $this->mockedOwnCurrentComment,
            ],
            [
                'body' => $this->mockedArguments['body'],
                'commit_id' => $this->mockedArguments['commitId'],
                'path' => $this->mockedArguments['path'],
                'position' => (int) $this->mockedArguments['position'],
            ],
            [
                'create' => 1,
                'remove' => 2,
            ]
        );
        $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);
    }

    /**
     * Prepares all relevant mocks for filtering
     *
     * @param array<int, array<string,string>> $staleCommentsToReturn
     * @param array<string, array<string, string>>|array<string,int|string> $expectedParameterValue
     * @param array<string, int> $mockExecutionAmounts
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function prepareMocksForFiltering(
        array $staleCommentsToReturn,
        array $expectedParameterValue,
        array $mockExecutionAmounts = [],
    ): void {
        $mockedAllComments = [$this->mockedOwnStaleComment, $this->mockedOwnCurrentComment];

        $this->subjectParameters[Client::class]->shouldReceive('authenticate')->once()
            ->with($this->mockedArguments['user_name'], $this->mockedArguments['token'], Client::AUTH_ACCESS_TOKEN);

        $currentUser = Mockery::mock(CurrentUser::class);
        $currentUser->expects()->show()->andReturn(['login' => $this->mockedLogin]);

        $this->subjectParameters[Client::class]->shouldReceive('currentUser')->once()
            ->withNoArgs()->andReturn($currentUser);

        $pullRequestMock = Mockery::mock(PullRequest::class);
        $pullRequestMock
            ->shouldReceive('comments->remove')
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                H::either(H::is($this->mockedOwnStaleComment['id']))->orElse(H::is($this->mockedOwnCurrentComment['id']))
            )->times($mockExecutionAmounts['remove'] ?? 0);

        $pullRequestMock
            ->shouldReceive('comments->create')
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                $this->mockedArguments['pullNumber'],
                $expectedParameterValue,
            )->times($mockExecutionAmounts['create'] ?? 0);

        $pullRequestMock
            ->shouldReceive('comments->update')
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                $this->mockedOwnCurrentComment['id'],
                $expectedParameterValue,
            )->times($mockExecutionAmounts['update'] ?? 0);

        $pullRequestMock
            ->shouldReceive('comments->all')
            ->once()
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                $this->mockedArguments['pullNumber'],
            )
            ->andReturn($mockedAllComments);

        $this->subjectParameters[Client::class]->shouldReceive('pullRequest')
            ->andReturn($pullRequestMock);

        $this->subjectParameters[CommentFilter::class]->shouldReceive('filterForOwnComments')
            ->with(
                $mockedAllComments,
                $this->mockedArguments['path'],
                $this->mockedLogin
            )
            ->andReturn($mockedAllComments);

        $this->subjectParameters[CommentFilter::class]->shouldReceive('filterForStaleComments')
            ->with(
                $mockedAllComments,
                $this->mockedArguments['position'],
                $this->mockedArguments['commitId']
            )
            ->andReturn($staleCommentsToReturn);
    }
}
