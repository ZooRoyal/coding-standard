<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic;

use Mockery;
use Mockery\MockInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

abstract class TargetableToolsCommandTest extends AbstractToolCommandTest
{
    /** @var MockInterface|TargetableInputFacet */
    protected TargetableInputFacet $mockedTargetableInputFacet;
    /** @var MockInterface|InputDefinition */
    protected InputDefinition $mockedTargetInputDefinition;
    /** @var MockInterface|InputOption */
    protected InputOption $mockedTargetingOption;
    /** @var array<MockInterface|InputArgument> */
    protected array $mockedTargetingArguments;
    /** @var array<MockInterface|InputOption> */
    protected array $mockedTargetingOptions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedTargetInputDefinition = Mockery::mock(InputDefinition::class);
        $this->mockedTargetableInputFacet = Mockery::mock(TargetableInputFacet::class);
        $this->mockedTargetingOption = Mockery::mock(InputOption::class);
        $this->mockedTargetingOptions = [$this->mockedTargetingOption];
        $this->mockedTargetingArguments = [];

        $this->mockedTargetableInputFacet->shouldReceive('getInputDefinition')
            ->andReturn($this->mockedTargetInputDefinition);
        $this->mockedTargetInputDefinition->shouldReceive('getOptions')->andReturn($this->mockedTargetingOptions);
        $this->mockedTargetInputDefinition->shouldReceive('getArguments')->andReturn($this->mockedTargetingArguments);
    }
}
