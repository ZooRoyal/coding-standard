<?php

namespace Zooroyal\CodingStandard\Tests\Unit\Github\Commands;

use Github\Client;
use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty as HP;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Commands\StaticCodeAnalysis\FindFilesToCheckCommand;
use Zooroyal\CodingStandard\Github\Commands\PullCommentRefreshCommand;
use Zooroyal\CodingStandard\Github\Library\CommentFilter;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class PullCommentRefreshCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var FindFilesToCheckCommand */
    private $subject;
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;
    /** @var array<string> */
    private $mockedArguments;
    /** @var array<string> */
    private $mockedOwnCurrentComment;
    /** @var string */
    private $mockedLogin = 'MyLogin';
    /** @var array<string|array<string>> */
    private $mockedOwnStaleComment;

    protected function setUp()
    {
        $this->mockedArguments = [
            'token' => 'myToken',
            'organisation' => 'myOrganisation',
            'repository' => 'myRepository',
            'pullNumber' => 'myIssueId',
            'body' => 'myBody',
            'commitId' => 'myCommitId',
            'path' => 'myPath',
            'position' => 1,
        ];

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

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function configure()
    {
        /** @var MockInterface|FindFilesToCheckCommand $localSubject */
        $localSubject = Mockery::mock(PullCommentRefreshCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('pull:comment:refresh');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Updates a comment to a file in Github pull requests. Creates it if it does not exist.');
        $localSubject->shouldReceive('setDefinition')->once()
            ->with(
                H::allOf(
                    H::anInstanceOf(InputDefinition::class),
                    HP::hasProperty(
                        'arguments',
                        H::allOf(
                            H::hasItems(
                                HP::hasProperty('name', 'token'),
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
            );

        $localSubject->configure();
    }

    /**
     * @test
     */
    public function executePassesDataToClientForUpdate()
    {
        $expectedParameterValue = ['body' => $this->mockedArguments['body']];

        foreach ($this->mockedArguments as $key => $value) {
            $this->mockedInputInterface->shouldReceive('getArgument')
                ->with($key)->andReturn($value);
        }

        $this->mockedInputInterface->shouldReceive('getArguments')
            ->withNoArgs()->andReturn($this->mockedArguments);

        $this->prepareMocksForFiltering([$this->mockedOwnStaleComment]);

        $this->subjectParameters[Client::class]->shouldReceive('pullRequest->comments->remove')->once()
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                $this->mockedOwnStaleComment['id']
            );

        $this->subjectParameters[Client::class]->shouldReceive('pullRequest->comments->update')->once()
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                $this->mockedOwnCurrentComment['id'],
                $expectedParameterValue
            );

        $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);
    }

    /**
     * @test
     */
    public function executePassesDataToClientForCreate()
    {
        $expectedParameterValue = [
            'body' => $this->mockedArguments['body'],
            'commit_id' => $this->mockedArguments['commitId'],
            'path' => $this->mockedArguments['path'],
            'position' => (int) $this->mockedArguments['position'],
        ];

        foreach ($this->mockedArguments as $key => $value) {
            $this->mockedInputInterface->shouldReceive('getArgument')
                ->with($key)->andReturn($value);
        }

        $this->mockedInputInterface->shouldReceive('getArguments')
            ->withNoArgs()->andReturn($this->mockedArguments);

        $this->prepareMocksForFiltering([$this->mockedOwnStaleComment, $this->mockedOwnCurrentComment]);

        $this->subjectParameters[Client::class]->shouldReceive('pullRequest->comments->remove')->twice()
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                H::either(H::is($this->mockedOwnStaleComment['id']))->orElse(H::is($this->mockedOwnCurrentComment['id']))
            );

        $this->subjectParameters[Client::class]->shouldReceive('pullRequest->comments->create')->once()
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                $this->mockedArguments['pullNumber'],
                H::identicalTo($expectedParameterValue)
            );

        $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);
    }

    private function prepareMocksForFiltering($staleCommentsToReturn)
    {
        $mockedAllComments = [$this->mockedOwnStaleComment, $this->mockedOwnCurrentComment];

        $this->subjectParameters[Client::class]->shouldReceive('authenticate')->once()
            ->with($this->mockedArguments['token'], '', Client::AUTH_URL_TOKEN);

        $this->subjectParameters[Client::class]->shouldReceive('currentUser->show')->once()
            ->withNoArgs()->andReturn(['login' => $this->mockedLogin]);

        $this->subjectParameters[Client::class]->shouldReceive('pullRequest->comments->all')->once()
            ->with(
                $this->mockedArguments['organisation'],
                $this->mockedArguments['repository'],
                $this->mockedArguments['pullNumber']
            )
            ->andReturn($mockedAllComments);

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
