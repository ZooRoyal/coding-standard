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
use Zooroyal\CodingStandard\Github\Commands\IssueCommentAddCommand;
use Zooroyal\CodingStandard\Tests\Tools\SubjectFactory;

class IssueCommentAddCommandTest extends TestCase
{
    /** @var MockInterface[]|mixed[] */
    private $subjectParameters;
    /** @var FindFilesToCheckCommand */
    private $subject;
    /** @var MockInterface|InputInterface */
    private $mockedInputInterface;
    /** @var MockInterface|OutputInterface */
    private $mockedOutputInterface;

    protected function setUp()
    {
        $subjectFactory = new SubjectFactory();
        $buildFragments = $subjectFactory->buildSubject(IssueCommentAddCommand::class);
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
        $localSubject = Mockery::mock(IssueCommentAddCommand::class, $this->subjectParameters)->makePartial();

        $localSubject->shouldReceive('setName')->once()->with('issue:comment:add');
        $localSubject->shouldReceive('setDescription')->once()
            ->with('Adds comment to Github Issue.');
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
                                HP::hasProperty('name', 'issue_id'),
                                HP::hasProperty('name', 'body')
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
    public function executePassesDataToClient()
    {
        $expectedTokenValue = 'myToken';
        $expectedOrganisationValue = 'myOrganisation';
        $expectedRepositoryValue = 'myRepository';
        $expectedIssueIdValue = 'myIssueId';
        $expectedBodyValue = 'myBody';
        $expectedParameterValue = ['body' => $expectedBodyValue];

        $this->mockedInputInterface->shouldReceive('getArgument')->once()
            ->with('token')->andReturn($expectedTokenValue);
        $this->mockedInputInterface->shouldReceive('getArgument')->once()
            ->with('organisation')->andReturn($expectedOrganisationValue);
        $this->mockedInputInterface->shouldReceive('getArgument')->once()
            ->with('repository')->andReturn($expectedRepositoryValue);
        $this->mockedInputInterface->shouldReceive('getArgument')->once()
            ->with('issue_id')->andReturn($expectedIssueIdValue);
        $this->mockedInputInterface->shouldReceive('getArgument')->once()
            ->with('body')->andReturn($expectedBodyValue);

        $this->subjectParameters[Client::class]->shouldReceive('authenticate')->once()
            ->with($expectedTokenValue, '', Client::AUTH_URL_TOKEN);
        $this->subjectParameters[Client::class]->shouldReceive('issue->comments->create')->once()
            ->with($expectedOrganisationValue, $expectedRepositoryValue, $expectedIssueIdValue, $expectedParameterValue);

        $this->subject->execute($this->mockedInputInterface, $this->mockedOutputInterface);
    }
}
